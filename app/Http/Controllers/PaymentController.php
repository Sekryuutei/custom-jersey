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

    public function store(Request $request)
    {
        $imageData = $request->input('designImage');
        $imageLink = null;
        $cloudinary = new Cloudinary(config('services.cloudinary'));
        $uploadResult = $cloudinary->uploadApi()->upload($imageData, [
            'folder' => 'jersey_designs', 
            'resource_type' => 'image',
        ]);
        $imageLink = $uploadResult['secure_url'] ?? null;

        $payment = Payment::create([
            'file_name' => $imageLink,
            'status' => 'pending',
        ]);
        return redirect()->route('payment.show', $payment->id);
    }
    
    public function update(Request $request, Payment $payment)
{

    DB::transaction(function () use ($request, $payment) {
        // Hitung amount sebagai price * amount
        $quantity = $request->quantity ?? 1; // default 1 jika tidak ada
        $price = $request->price ?? 50000;
        $amount = $price * $quantity;

        $payment->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'size' => $request->size,
            'quantity' => $quantity,
            'payment_method' => $request->payment_method,
            'amount' => $amount,
            'price' => $price,
            'status' => 'pending',
        ]);
        
        $orderId = 'SANDBOX-' . uniqid();
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $payment->name,
                'email' => $payment->email,
                'phone' => $payment->phone,
                'address' => $payment->address,
            ],
            'item_details' => [[
                'id' => $payment->id,
                'price' => $price,
                'quantity' => $quantity,
                'name' => "Custom Jersey Order #{$payment->id}",
            ]],
        ];
        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $payment->snap_token = $snapToken;
        $payment->order_id = $orderId;
        $payment->save();
    });

    // Render template pesan WhatsApp menjadi string
    $whatsappMessage = view('whatsapp.payment_success', compact('payment'))->render();

    return response()->json([
        'snap_token' => $payment->snap_token,
        'payment_id' => $payment->id,
        'amount' => $payment->amount,
        'whatsapp_message' => $whatsappMessage,
    ]);
    
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

    $whatsappMessage = view('whatsapp.payment_success', compact('payment'))->render();
    $userPhone = preg_replace('/^0/', '62', $payment->phone);
    $adminPhone = config('services.fonnte.admin_number');
    $targets = "{$userPhone},{$adminPhone}";

    $response = Http::withHeaders([
        'Authorization' => config('services.fonnte.token')
    ])->post('https://api.fonnte.com/send', [
        'target' => $targets,
        'message' => $whatsappMessage,
    ]);

    // Log the response from Fonnte for debugging
    if ($response->failed()) {
        Log::error("Failed to send WhatsApp notification for order {$payment->order_id}. Fonnte Response: " . $response->body());
    } else {
        Log::info("WhatsApp notification sent successfully for order {$payment->order_id}. Fonnte Response: " . $response->body());
    }
}

}
