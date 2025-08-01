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
        // Admin tidak seharusnya mengakses halaman riwayat pesanan pelanggan
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.index')->with('error', 'Halaman ini hanya untuk pelanggan.');
        }

        $orders = Auth::user()
            ->payments()
            ->with('orderItems') // Eager load
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}
