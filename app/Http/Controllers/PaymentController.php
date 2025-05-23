<?php



namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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
    $imageData = $request->input('designImage');
    $image = str_replace('data:image/png;base64,', '', $imageData);

    $imgurClientId = config('services.imgur.client_id'); // Ganti dengan Client-ID Anda
    $client = new Client();
    $response = $client->post('https://api.imgur.com/3/image', [
        'headers' => [
            'Authorization' => 'Client-ID'. $imgurClientId, // Ganti dengan Client-ID Anda
        ], 
//Access Token d59122c17f2d5ec3a6f3b2a6a7a86faa52c7a527
//Access Token e5dd62f30d4e3982d9b8107a469d4cffe0404d7a
//Refresh Token f5d95458c6e30d3000176076bdf7a76f6b28d14b
//Client ID adccd29b0f847a5
//Client Secret 7756be0f6ccfc486840e610c2ea9ca0390a4fd4c
        'form_params' => [
            'image' => $image,
            'type' => 'base64',
        ],
    ]);
    $responseBody = json_decode($response->getBody(), true);
    $image = $responseBody['data']['file'];
    $image = str_replace('data:image/png;base64,', '', $image);

    $payment = Payment::create([
        'file_name' => "payments/{$image}",
        'status' => 'pending',
    ]);

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

    public function admin()
    {
        $payments = Payment::all();
        return view('admin', compact('payments'));
    }

    public function download(Payment $payment)
    {
        // Ensure the file_name is not null and the file exists on the public disk
        if ($payment->file_name && Storage::disk('public')->exists($payment->file_name)) {
            $filePath = Storage::disk('public')->path($payment->file_name);
            return response()->download($filePath);
        }
        // Optionally, handle the case where the file doesn't exist
        return redirect()->back()->with('error', 'File not found.');
    }

}

