<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // Metode lain seperti store, show, update, dll. tetap ada di sini...
    // ...

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

            // Validasi signature key (penting untuk keamanan)
            $signatureKey = hash('sha512', $orderId . $notif->status_code . $notif->gross_amount . config('services.midtrans.server_key'));
            if ($notif->signature_key != $signatureKey) {
                Log::error("Midtrans Notification: Invalid signature key for Order ID {$orderId}.");
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Update status pembayaran di database
            DB::transaction(function () use ($transactionStatus, $fraudStatus, $payment) {
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if ($fraudStatus == 'accept') {
                        // Pembayaran berhasil dan aman
                        $payment->status = 'success';
                    }
                } else if ($transactionStatus == 'pending') {
                    // Pembayaran masih menunggu
                    $payment->status = 'pending';
                } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                    // Pembayaran gagal
                    $payment->status = 'failed';
                }

                $payment->save();
                Log::info("Midtrans Notification: Status for Order ID {$payment->order_id} updated to {$payment->status}.");

                // Di sini Anda bisa menambahkan logika lain, seperti:
                // - Mengirim email konfirmasi ke pelanggan
                // - Mengirim notifikasi ke admin
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
}
