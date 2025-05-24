<?php



namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

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
            $request->validate([
            'designImage' => 'required|string',
            'template_id' => 'required|integer', // Sebaiknya tambahkan validasi exists:templates,id
            'price' => 'required|numeric|min:0', // Ini adalah harga dasar per unit
        ]);

    $imageData = $request->input('designImage');
    $image = str_replace('data:image/png;base64,', '', $imageData);
    $imgurClientId = config('services.imgur.client_id'); // Ganti dengan Client-ID Anda
    
    try{
    $client = new Client();
    $response = $client->post('https://api.imgur.com/3/image', [
        'headers' => [
            'Authorization' => "Client-ID {$imgurClientId}", // Ganti dengan Client-ID Anda
        ],

        'form_params' => [
            'image' => $image,
            'type' => 'base64',
        ],
        'timeout' => 30,
    ]);
    
    $responseBody = json_decode($response->getBody()->getContents(), true);
    $imgurLink = $responseBody['data']['link'];

    return redirect()->route('payment.show', [
        'template_id' => $request->template_id,
        'designImage' => $imgurLink,
        'price' => $request->price,
    ]);
} catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to upload image to Imgur: ' . $e->getMessage());
    }
}

    public function update(Request $request, $id)
{
    $validate = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'payment_method' => 'required|string|max:50',
        'imgur_link' => 'required|url',
        'template_id' => 'required|integer', // Sebaiknya tambahkan validasi exists:templates,id
        'price' => 'required|numeric|min:0', // Ini adalah harga dasar per unit

    ]);

    $quantity = (int)$validate['amount'];
    $unitPrice = (int)$validate['price'];
    $totalPrice = $quantity * $unitPrice;

    try {
    $payment = DB::transaction(function () use ($validate, $quantity, $unitPrice, $totalPrice, $id) {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'phone' => $validate['phone'],
            'address' => $validate['address'],
            'amount' => $quantity,
            'file_name' => $validate['imgur_link'],
            'template_id' => $validate['template_id'],
            'price' => $unitPrice,
            'status' => 'pending',
        ]);

        $payload = [
        'transaction_details' => [
        'order_id' => 'SANDBOX-' . uniqid(),
        'gross_amount' => $payment->price,
        ],

        'customer_details' => [
        'first_name' => $payment->name,
        'email' => $payment->email,
        'phone' => $payment->phone,
        'address' => $payment->address,
        ],
        'item_details' => [[
        'id' => $payment->template_id,
        'price' => $unitPrice,
        'quantity' => $payment->amount,
        'name' => "Payment for {$payment->file_name}",
        ]],
];
        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $payment->snap_token = $snapToken;
        $payment->save();
        return $payment;
    });
    if($payment) {
        return response()->json([
            'snap_token' => $payment->snap_token,
            'payment_id' => $payment->id,
        ]);
    }
    return response()->json([
        'error' => 'Failed to create payment',
    ]);
} catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to create payment: ' . $e->getMessage(),
        ]);
    }    
}

    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        $imgurLink = $payment->file_name;
        $template_id = $payment->template_id;
        $price = $payment->amount;

        if (!$imgurLink || !$template_id || !$price) {
            return redirect()->back()->with('error', 'File not found or invalid template.');
        }

        return view('payment', compact('imgurLink', 'template_id', 'price'));
    }   

    public function admin()
    {
        $payments = Payment::all();
        return view('admin', compact('payments'));
    }

    public function download(Payment $payment)
{
    if ($payment->file_name) {
        // Redirect langsung ke URL Imgur
        return redirect()->away($payment->file_name);
    }
    return redirect()->back()->with('error', 'File not found.');
}

public function notif(Request $request)
    {
        // Gunakan library Midtrans untuk menangani notifikasi jika memungkinkan
        // Ini adalah contoh dasar, pastikan untuk memverifikasi signature key dengan benar
        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: Failed to instantiate Notification object. ' . $e->getMessage());
            return response('Invalid notification object', 400);
        }

        $orderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null; // fraud_status mungkin tidak selalu ada

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::error("Midtrans notification: Pembayaran tidak ditemukan untuk order_id: {$orderId}");
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        }

        // Hindari pemrosesan ulang notifikasi yang statusnya sudah final
        if (in_array($payment->status, ['success', 'failed', 'cancelled', 'expired'])) {
            Log::info("Midtrans notification: Pembayaran untuk order_id: {$orderId} sudah memiliki status final: {$payment->status}. Diabaikan.");
            return response()->json(['message' => 'Notifikasi sudah diproses'], 200);
        }

        DB::transaction(function () use ($payment, $transactionStatus, $fraudStatus, $notif) {
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus == 'accept' || $fraudStatus == null) { // Anggap null sebagai accept jika tidak ada fraud check
                    $payment->status = 'success';
                } else if ($fraudStatus == 'challenge') {
                    $payment->status = 'challenge'; // Atau 'pending_fraud_review'
                } else { // deny
                    $payment->status = 'failed'; // Atau 'fraud_denied'
                }
            } else if ($transactionStatus == 'pending') {
                $payment->status = 'pending';
            } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $payment->status = ($transactionStatus == 'cancel') ? 'cancelled' : (($transactionStatus == 'expire') ? 'expired' : 'failed');
            }
            
            $payment->payment_method = $notif->payment_type ?? $payment->payment_method; // Simpan tipe pembayaran
            $payment->save();
            Log::info("Midtrans notification: Status pembayaran diperbarui untuk order_id: {$payment->order_id} menjadi {$payment->status}");
        });

        return response()->json(['message' => 'Notifikasi berhasil diproses'], 200);
    }
}



