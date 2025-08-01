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
        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang Anda kosong.');
        }
        return view('checkout.index', compact('cartItems'));
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

        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang Anda kosong.'], 400);
        }

        $totalAmount = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $payment = null;
        DB::transaction(function () use ($request, $cartItems, $totalAmount, &$payment) {
            // 1. Buat record Payment utama
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'amount' => $totalAmount,
                'status' => 'pending',
            ]);

            $item_details = [];
            // 2. Pindahkan item dari keranjang ke order_items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'payment_id' => $payment->id,
                    'file_name' => $cartItem->file_name,
                    'size' => $cartItem->size,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);
                $item_details[] = [
                    'id' => $cartItem->id, 'price' => $cartItem->price, 'quantity' => $cartItem->quantity, 'name' => 'Custom Jersey ' . $cartItem->size
                ];
            }

            // 3. Hapus item dari keranjang
            $cartItems->each->delete();

            // 4. Dapatkan Snap Token
            $orderId = 'SANDBOX-' . $payment->id . '-' . time();
            $payment->order_id = $orderId;

            $payload = [
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $totalAmount],
                'customer_details' => ['first_name' => $request->name, 'email' => $request->email, 'phone' => $request->phone],
                'item_details' => $item_details,
            ];

            $payment->snap_token = \Midtrans\Snap::getSnapToken($payload);
            $payment->save();
        });
        // Pastikan $payment tidak null sebelum mengakses propertinya
        if (is_null($payment)) {
            return response()->json(['message' => 'Failed to create payment record.'], 500);
        }

        return response()->json(['snap_token' => $payment->snap_token, 'payment_id' => $payment->id]);
    }

    private function getCartItems()
    {
        return Auth::check() ? CartItem::where('user_id', Auth::id())->get() : CartItem::where('session_id', session()->getId())->get();
    }
}
