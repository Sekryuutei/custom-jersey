<?php



namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
    if (strpos($imageData, 'data:image/png;base64,') !== 0) {
        return redirect()->back()->with('error', 'Invalid image data.');
    }

    $image = str_replace('data:image/png;base64,', '', $imageData);

    try {
        $client = new \GuzzleHttp\Client();
        $imgurClientId = config('services.imgur.client_id');
        $response = $client->post('https://api.imgur.com/3/image', [
            'headers' => [
                'Authorization' => "Client-ID {$imgurClientId}",
            ],
            'form_params' => [
                'image' => $image,
                'type' => 'base64',
            ],
            'timeout' => 30,
        ]);
        $responseBody = json_decode($response->getBody()->getContents(), true);
        if (empty($responseBody['data']['link'])) {
            return redirect()->back()->with('error', 'Imgur upload failed.');
        }
        $uploadedFileUrl = $responseBody['data']['link'];
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Imgur upload failed: ' . $e->getMessage());
    }

    // Simpan data pembayaran ke database
    $payment = Payment::create([
        'file_name' => $uploadedFileUrl,
        'price' => $request->price,
        'status' => 'pending',
    ]);

    // Redirect ke halaman payment (form user)
    return redirect()->route('payment.show', $payment->id);
}

    public function update(Request $request, $id)
{

    $payment = DB::transaction(function () use ($request, $id) {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'updated_at' => now(),
            'status' => 'pending',
        ]);

        $payload = [
        'transaction_details' => [
        'order_id' => 'SANDBOX-' . uniqid(),
        'gross_amount' => $payment->amount,
        ],

        'customer_details' => [
        'first_name' => $payment->name,
        'email' => $payment->email,
        'phone' => $payment->phone,
        'address' => $payment->address,
        ],
        'item_details' => [[
        'id' => $payment->payment_method,
        'price' => $payment->amount,
        'quantity' => 1,
        'name' => "Payment for {$payment->file_name}",
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

    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return view('payment', compact('payment'));
    }   

public function finish(Request $request)
{
    // Validasi pembayaran sukses dari Midtrans jika perlu
    $payment = Payment::create([
        'file_name' => $request->file_name,
        'price' => $request->price,
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'amount' => $request->amount,
        'status' => 'success',
        'payment_result' => $request->payment_result,
    ]);
    return response()->json(['success' => true]);
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

