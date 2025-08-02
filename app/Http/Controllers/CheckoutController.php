<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Payment;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Menampilkan halaman checkout.
     */
    public function index()
    {
        // Mengambil data dari session cart, bukan database
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong. Silakan belanja terlebih dahulu.');
        }

        // Menghitung total harga dari session cart
        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Mengirim data session cart ke view
        return view('checkout.index', compact('cart', 'totalPrice'));
    }

    /**
     * Memproses checkout dan mendapatkan Snap Token.
     */
    public function process(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // Mengambil data dari session cart
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Keranjang Anda kosong.'], 400);
        }

        // Menghitung total dan menyiapkan item details untuk Midtrans
        $totalAmount = 0;
        $item_details = [];
        foreach ($cart as $id => $item) {
            $totalAmount += $item['price'] * $item['quantity'];

            // Siapkan nama item dan pastikan tidak melebihi 50 karakter (batas Midtrans)
            $itemName = 'Custom Jersey ' . $item['name'] . ' (' . $item['size'] . ')';

            $item_details[] = [
                'id' => $id,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'name' => \Illuminate\Support\Str::limit($itemName, 50, '') // Memotong nama jika > 50 karakter
            ];
        }

        $payment = null;
        try {
            DB::transaction(function () use ($request, $cart, $totalAmount, $item_details, &$payment) {
                // Validasi setiap item di keranjang sebelum memproses
                foreach ($cart as $id => $item) {
                    // Pastikan design_image_path adalah URL yang valid dari Cloudinary
                    if (!filter_var($item['design_image_path'], FILTER_VALIDATE_URL) || !\Illuminate\Support\Str::contains($item['design_image_path'], 'cloudinary')) {
                        // Jika tidak valid, lempar exception untuk menghentikan transaksi
                        throw new \Exception('Keranjang Anda berisi data desain yang tidak valid. Harap hapus item tersebut dan tambahkan kembali.');
                    }
                }

                // 1. Buat record Payment utama
                $payment = Payment::create([
                    'user_id' => Auth::id(), // Akan null jika pengguna tidak login
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                // 2. Pindahkan item dari session cart ke tabel order_items
                foreach ($cart as $item) {
                    OrderItem::create([
                        'payment_id' => $payment->id,
                        'file_name' => $item['design_image_path'], // Menyimpan URL Cloudinary
                        'size' => $item['size'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                // 3. Kosongkan session cart
                session()->forget('cart');

                // 4. Dapatkan Snap Token
                $orderId = 'SANDBOX-' . $payment->id . '-' . time();
                $payment->order_id = $orderId;

                $payload = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => $totalAmount
                    ],
                    'customer_details' => [
                        'first_name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone
                    ],
                    'item_details' => $item_details, // Menggunakan item_details yang sudah disiapkan
                ];

                $payment->snap_token = \Midtrans\Snap::getSnapToken($payload);
                $payment->save();
            });
        } catch (\Exception $e) {
            // Kirim pesan error yang spesifik ke frontend
            return response()->json(['message' => $e->getMessage()], 500);
        }
        // Pastikan $payment tidak null sebelum mengakses propertinya
        if (is_null($payment)) {
            return response()->json(['message' => 'Gagal membuat catatan pembayaran.'], 500);
        }

        return response()->json(['snap_token' => $payment->snap_token, 'payment_id' => $payment->id]);
    }

}
