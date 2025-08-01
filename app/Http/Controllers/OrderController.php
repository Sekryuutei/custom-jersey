<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Menampilkan riwayat pesanan milik pengguna yang sedang login.
     */
    public function index()
    {
        $orders = Auth::user()
            ->payments()
            ->with('orderItems') // Eager load
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}
