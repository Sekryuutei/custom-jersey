<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Menampilkan halaman status pesanan untuk pelanggan.
     * Ini adalah metode yang hilang yang menyebabkan error.
     */
    public function order(Payment $payment)
    {
        // Eager load order items untuk ditampilkan di view
        $payment->load('orderItems');

        // Anda perlu membuat view ini: resources/views/orders/show.blade.php
        return view('orders.show', compact('payment'));
    }

    /**
     * Menampilkan dashboard admin dengan daftar semua pembayaran.
     */
    public function admin()
    {
        $payments = Payment::with('user')->latest()->paginate(15);

        // Anda perlu membuat view ini: resources/views/admin/index.blade.php
        return view('admin.index', compact('payments'));
    }

    /**
     * Menampilkan detail pesanan di halaman admin.
     */
    public function showOrder(Payment $payment)
    {
        $payment->load('orderItems'); // Eager load order items

        // Anda perlu membuat view ini: resources/views/admin/orders/show.blade.php
        return view('admin.orders.show', compact('payment'));
    }
    
    /**
     * Menangani notifikasi webhook dari Midtrans.
     */
    public function notif(Request $request)
    {
        // Log mentah notifikasi untuk debugging
        Log::info('Midtrans Notif Received: ' . $request->getContent());

        try {
            // Buat instance dari notifikasi Midtrans
            $notif = new \Midtrans\Notification();

            // Lakukan validasi signature key untuk keamanan
            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;
            $orderId = $notif->order_id;

            // Cari pembayaran berdasarkan order_id
            $payment = Payment::where('order_id', $orderId)->first();

            if (!$payment) {
                Log::warning("Midtrans Notification: Payment with Order ID {$orderId} not found.");
                // Tetap kembalikan 200 OK agar Midtrans berhenti mengirim notifikasi
                return response()->json(['status' => 'ok', 'message' => 'Order not found']);
            }

            // Jangan proses notifikasi untuk status yang sama berulang kali
            if ($payment->status === 'success' || $payment->status === 'settlement') {
                Log::info("Midtrans Notification: Payment with Order ID {$orderId} already processed.");
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            // --- Validasi Signature Key yang Lebih Andal ---
            // Gunakan data dari database Anda untuk membuat signature, bukan dari payload notifikasi.
            // Ini mencegah masalah jika format 'gross_amount' dari Midtrans tidak konsisten.
            $ourAmount = $payment->amount;
            $formattedAmount = number_format($ourAmount, 2, '.', '');

            $localSignatureKey = hash('sha512', $orderId . $notif->status_code . $formattedAmount . config('services.midtrans.server_key'));

            if ($notif->signature_key !== $localSignatureKey) {
                // Log yang detail untuk debugging jika signature masih gagal
                Log::error("Midtrans Notification: Invalid signature key for Order ID {$orderId}.", [
                    'order_id' => $orderId,
                    'received_signature' => $notif->signature_key,
                    'generated_signature' => $localSignatureKey,
                    'string_to_hash' => $orderId . $notif->status_code . $formattedAmount . config('services.midtrans.server_key'),
                    'components' => [
                        'order_id' => $orderId,
                        'status_code' => $notif->status_code,
                        'gross_amount_from_db' => $formattedAmount, // Menggunakan amount dari DB
                        'server_key' => config('services.midtrans.server_key'),
                    ]
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Update status pembayaran di database
            DB::transaction(function () use ($transactionStatus, $fraudStatus, $payment) {
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if ($fraudStatus == 'accept') {
                        // Pembayaran berhasil dan aman
                        $payment->status = 'success';
                        $payment->save();
                        Log::info("Midtrans Notification: Status for Order ID {$payment->order_id} updated to success.");

                        // Kirim notifikasi ke admin HANYA JIKA pembayaran sukses
                        $this->sendAdminSuccessNotification($payment);
                    }
                } else if ($transactionStatus == 'pending') {
                    // Pembayaran masih menunggu
                    $payment->status = 'pending';
                    $payment->save();
                    Log::info("Midtrans Notification: Status for Order ID {$payment->order_id} updated to pending.");
                } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                    // Pembayaran gagal
                    $payment->status = 'failed';
                    $payment->save();
                    Log::info("Midtrans Notification: Status for Order ID {$payment->order_id} updated to failed.");
                }
            });

            // Beri tahu Midtrans bahwa notifikasi sudah diterima dengan sukses
            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            // Log error yang terjadi di dalam blok try
            Log::error('Midtrans Notification Error: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all()
            ]);

            // Tetap kembalikan status 200 OK agar Midtrans tidak mengirim ulang.
            // Masalahnya ada di sisi kita, bukan Midtrans.
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 200);
        }
    }

    /**
     * Mengirim notifikasi WhatsApp ke admin ketika pembayaran berhasil.
     *
     * @param Payment $payment
     */
    private function sendAdminSuccessNotification(Payment $payment)
    {
        try {
            // --- LANGKAH DEBUGGING: Hardcode kredensial untuk sementara ---
            // Ganti nilai di bawah ini dengan token dan nomor WA admin Anda yang sebenarnya.
            // Ini untuk memastikan tidak ada masalah dengan file .env atau cache konfigurasi.
            $token = 'SBrvFwH4H88gm9scBFsu';
            $adminPhone = '6285156383076'; // Contoh: 6281234567890

            if (!$token || !$adminPhone) {
                Log::warning('Fonnte token or admin phone number is not configured. Skipping notification.');
                return;
            }

            $customerName = $payment->name;
            $orderId = $payment->order_id;
            $amount = number_format($payment->amount, 0, ',', '.');

            $message = "✅ *Pembayaran Berhasil Diterima!*\n\n" .
                       "Halo Admin,\nAda pesanan baru yang sudah lunas dan siap diproses:\n\n" .
                       "*- Order ID:* {$orderId}\n" .
                       "*- Nama Pelanggan:* {$customerName}\n" .
                       "*- Total Pembayaran:* Rp {$amount}\n\n" .
                       "Silakan segera periksa dashboard admin untuk detail pesanan dan memprosesnya. Terima kasih!";

            // FIX: Fonnte API expects data as 'application/x-www-form-urlencoded', not JSON.
            // Menggunakan asForm() untuk mengirim data dengan Content-Type yang benar.
            $response = Http::asForm()->withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $adminPhone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("Successfully sent Fonnte notification to admin for Order ID {$orderId}.");
            } else {
                // Log error spesifik dari Fonnte jika pengiriman gagal
                Log::error("Fonnte API returned an error for Order ID {$orderId}.", [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send Fonnte notification for Order ID {$payment->order_id}: " . $e->getMessage());
        }
    }

    /**
     * Menangani permintaan download (misal: invoice).
     * Logika ini perlu diimplementasikan di masa depan.
     */
    public function download(Payment $payment)
    {
        // Logika untuk generate dan download invoice/PDF bisa ditambahkan di sini.
        // Contoh: return PDF::loadView('invoices.show', compact('payment'))->download('invoice-'.$payment->order_id.'.pdf');
        return back()->with('info', 'Fitur download belum diimplementasikan.');
    }
}
