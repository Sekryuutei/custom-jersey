<?php



namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Cloudinary\Cloudinary;

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

        $payment = Payment::create([
            'file_name' => $imageLink,
            'status' => 'pending',
        ]);
        // Redirect ke halaman payment (form user)
        return redirect()->route('payment.show', $payment->id);

    }
    
// Imgur API
//    public function store(Request $request)
// {

//     $imageData = $request->input('designImage');
//     $image = str_replace('data:image/png;base64,', '', $imageData);
//     $imageLink = null;

    
//         $client = new Client();
//         $imgurClientId = config('services.imgur.client_id');
//         $response = $client->post('https://api.imgur.com/3/image', [
//             'headers' => [
//                 'Authorization' => "Client-ID {$imgurClientId}",
//             ],
//             'form_params' => [
//                 'image' => $image,
//                 'type' => 'base64',
//             ],
//             'timeout' => 30,
//         ]);
//         $responseBody = json_decode($response->getBody()->getContents(), true);
//         $imageLink = $responseBody['data']['link'] ?? null;
        
//     // Simpan data pembayaran ke database
//     $payment = Payment::create([
//         'file_name' => $imageLink,
//         'status' => 'pending',
//     ]);

    // Redirect ke halaman payment (form user)
//     return redirect()->route('payment.show', $payment->id);
    
// }

    public function update(Request $request, $id)
{

    $payment = DB::transaction(function () use ($request, $id) {
        $payment = Payment::findOrFail($id);

        // Hitung amount sebagai price * amount
        $amount = (int)($request->amount ?? 1); // default 1 jika tidak ada

        $price = (int)($request->price ?? 50000);
        $totalAmount = $price * $amount;

        $payment->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'amount' => $amount,
            'price' => $price,
            // 'updated_at' => now(),
            'status' => 'pending',
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => 'SANDBOX-' . uniqid(),
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $payment->name,
                'email' => $payment->email,
                'phone' => $payment->phone,
                'billing_address' => [
                    'first_name' => $payment->name,
                    'address' => $payment->address,
                ],
            ],
            'item_details' => [[
                'id' => $payment->id,
                'price' => $price,
                'amount' => $amount,
                'name' => "Custom Jersey Order #{$payment->id}",
            ]],
        ];
        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $payment->snap_token = $snapToken;
        $payment->save();
        return $payment;
    });

    return response()->json([
        'snap_token' => $payment->snap_token,
        'payment_id' => $payment->id,
    ]);
    
}

    public function show(Payment $payment)
    {
        return view('payment', compact('payment'));
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

}

