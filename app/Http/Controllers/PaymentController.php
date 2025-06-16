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
            'folder' => 'jersey_designs', // Optional: organize uploads in a folder on Cloudinary
            'resource_type' => 'image',
        ]);
        $imageLink = $uploadResult['secure_url'] ?? null;

        // Simpan data pembayaran ke database

        $payment = Payment::create([
            'file_name' => $imageLink,
            'status' => 'pending',
        ]);
        // Redirect ke halaman payment (form user)
        return redirect()->route('payment.show', $payment->id);

    }
    
    public function update(Request $request, $id)
{

    $payment = DB::transaction(function () use ($request, $id) {
        $payment = Payment::findOrFail($id);

        // Hitung amount sebagai price * amount
        $quantity = $request->quantity ?? 1; // default 1 jika tidak ada
        $price = $request->price ?? 50000;
        $amount = $price * $quantity;

        $payment->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
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
        return $payment;
    });

            $mail = [
                    'order_id' => $payment->order_id,
                    'name' => $payment->name,
                    'email' => $payment->email,
                    'phone' => $payment->phone,
                    'address' => $payment->address,
                    'amount' => $payment->amount,
                    'file_name' => $payment->file_name,
                ];

                Mail::to($payment->email)->send(new MailSend($mail));
                Mail::to('matsudagie@gmail.com')->send(new MailSend($mail));

    return response()->json([
        'snap_token' => $payment->snap_token,
        'payment_id' => $payment->id,
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

            // Verifikasi signature (opsional tapi sangat direkomendasikan untuk keamanan)
            // $local_signature_key = hash("sha512", $notification->order_id.$notification->status_code.$notification->gross_amount.config('services.midtrans.server_key'));
            // if ($notification->signature_key != $local_signature_key) {
            //     Log::error("Invalid signature for order_id: {$order_id}");
            //     return response()->json(['message' => 'Invalid signature'], 403);
            // }

            if ($status == 'capture') {
                if ($fraud == 'accept') {
                    $payment->setStatusSuccess();
                } else if ($fraud == 'challenge') {
                    $payment->status = 'challenge'; // Anda mungkin perlu menambahkan status 'challenge'
                }
            } else if ($status == 'settlement') {
                $payment->setStatusSuccess();
            } else if ($status == 'pending') {
                $payment->setStatusPending(); // Biasanya status sudah pending, tapi ini konfirmasi
            } else if ($status == 'deny') {
                $payment->setStatusFailed();
            } else if ($status == 'expire') {
                $payment->setStatusExpired();
            } else if ($status == 'cancel') {
                $payment->setStatusFailed(); // Atau status 'cancelled' jika Anda punya
            }

            // Simpan metode pembayaran aktual dari Midtrans
            if ($payment_method) {
                $payment->payment_method = $payment_method;
            }
            $payment->save();

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage() . ' --- Payload: ' . $notif);
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    // public function mail(Request $request)
    // {
    //     $mail = [
    //         'title' => 'Mail from Laravel',
    //         'body' => 'This is a test email sent from Laravel application.'
    //     ];

    //     Mail::to($request->email)->send(new MailSend($mail));

    //     return response()->json(['message' => 'Email sent successfully']);
    // }

}

