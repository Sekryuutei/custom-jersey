<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'payment_id'  => 'required|exists:payments,id',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'nullable|string|max:1000',
        ]);

        // Verifikasi bahwa pengguna yang login benar-benar memiliki pesanan ini
        $payment = Payment::where('id', $request->payment_id)
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$payment) {
            return back()->with('error', 'Anda tidak dapat memberikan ulasan untuk pesanan ini.');
        }

        // Verifikasi bahwa template yang diulas ada dalam pesanan tersebut
        $orderItemExists = $payment->orderItems()->where('template_id', $request->template_id)->exists();

        if (!$orderItemExists) {
             return back()->with('error', 'Produk tidak ditemukan dalam pesanan ini.');
        }

        // Simpan atau perbarui ulasan. Ini memungkinkan pengguna mengubah ulasannya.
        Review::updateOrCreate(
            [
                'user_id'     => Auth::id(),
                'template_id' => $request->template_id,
                'payment_id'  => $request->payment_id,
            ],
            $request->only(['rating', 'comment'])
        );

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
