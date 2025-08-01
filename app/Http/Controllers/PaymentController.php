<?php



namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Cloudinary\Cloudinary;
use App\Mail\MailSend;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function __construct(){
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function show(Payment $payment)
    {
        return view('payment', compact('payment'));
    }

    public function order(Payment $payment)
    {
        return view('order', compact('payment'));
    }

    public function admin()
    {
        $payments = Payment::all();
        return view('admin', compact('payments'));
    }

    /**
     * Menampilkan detail pesanan untuk admin.
     */
    public function showOrder(Payment $payment)
    {
        // Eager load order items untuk efisiensi query
        $payment->load('orderItems');
        return view('admin.orders.show', compact('payment'));
    }

    public function download(Payment $payment)
    {
        if ($payment->file_name) {
            // Redirect langsung ke URL file hasil desain (misal Cloudinary)
            return redirect()->away($payment->file_name);
        }
        return redirect()->back()->with('error', 'File not found.');
    }

    public function notif(Request $request){
        $notif_body = $request->getContent();
        Log::info('Midtrans Notif Received: ' . $notif_body);
        try {
            // Pustaka Midtrans akan melakukan validasi signature secara otomatis di sini.
            $notif = new \Midtrans\Notification();

            $order_id = $notif->order_id;
            $status = $notif->transaction_status;
            $fraud = $notif->fraud_status;
            $payment_method = $notif->payment_type;

            $payment = Payment::where('order_id', $order_id)->first();

               if (!$payment) {
                Log::error("Payment not found for Midtrans order_id: {$order_id}. Notification: " . json_encode($notif));
                return response()->json(['message' => 'Payment not found for this order_id'], 404);
            }

            if ($status == 'capture') {
                if ($fraud == 'accept') {
                    $payment->setStatusSuccess();
                } else if ($fraud == 'challenge') {
                    $payment->status = 'challenge'; 
                }
            } else if ($status == 'settlement') {
                $this->sendSuccessNotification($payment);
                $payment->setStatusSuccess();
            } else if ($status == 'pending') {
                $payment->setStatusPending(); 
            } else if ($status == 'deny') {
                $payment->setStatusFailed();
            } else if ($status == 'expire') {
                $payment->setStatusExpired();
            } else if ($status == 'cancel') {
                $payment->setStatusFailed(); 
            }

            if ($payment_method) {
                $payment->payment_method = $payment_method;
            }
            $payment->save();

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage() . ' --- Raw Payload: ' . $notif_body);
            return response()->json(['message' => 'Error processing notification: ' . $e->getMessage()], 500);
        }
    }
    private function sendSuccessNotification(Payment $payment)
{
    // Jangan kirim notifikasi jika status sudah 'success' untuk menghindari duplikasi
    if ($payment->status === 'success') {
        Log::info("Notification for order {$payment->order_id} already sent.");
        return;
    }

    $fonnteToken = config('services.fonnte.token');

    // 1. Siapkan nomor target
    $customerPhone = preg_replace('/^0/', '62', $payment->phone);
    $targets = [$customerPhone]; // Mulai dengan nomor pelanggan

    $adminPhone = config('services.fonnte.admin_number');
    // Tambahkan nomor admin jika ada dan berbeda dari nomor pelanggan
    if ($adminPhone && $adminPhone !== $customerPhone) {
        $targets[] = $adminPhone;
    }

    // Gabungkan semua target menjadi satu string yang dipisahkan koma
    $targetString = implode(',', $targets);

    // 2. Render pesan gabungan
    $combinedMessage = view('whatsapp.combined_notification', compact('payment'))->render();

    // 3. Kirim satu notifikasi ke semua target
    $response = Http::withHeaders(['Authorization' => $fonnteToken])
        ->post('https://api.fonnte.com/send', [
            'target' => $targetString,
            'message' => $combinedMessage,
        ]);

    if ($response->failed()) {
        Log::error("Failed to send combined WhatsApp notification for order {$payment->order_id}. Targets: {$targetString}. Fonnte Response: " . $response->body());
    } else {
        Log::info("Combined WhatsApp notification sent successfully for order {$payment->order_id} to targets: {$targetString}.");
    }
}

}
