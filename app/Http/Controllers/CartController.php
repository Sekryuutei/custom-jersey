<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang.
     */
    public function index()
    {
        $cartItems = $this->getCartItems();
        return view('cart.index', compact('cartItems'));
    }

    /**
     * Menambahkan item ke keranjang.
     */
    public function add(Request $request)
    {
        $request->validate(['designImage' => 'required']);

        // Upload ke Cloudinary
        $uploadResult = Cloudinary::upload($request->input('designImage'), [
            'folder' => 'jersey_designs',
        ]);
        $imageLink = $uploadResult->getSecurePath();

        if (!$imageLink) {
            return back()->with('error', 'Gagal mengunggah desain. Silakan coba lagi.');
        }

        $cartData = [
            'file_name' => $imageLink,
            'price' => 50000, // Harga dasar
            'quantity' => 1,
            'size' => 'L',
        ];

        if (Auth::check()) {
            $cartData['user_id'] = Auth::id();
        } else {
            $cartData['session_id'] = session()->getId();
        }

        CartItem::create($cartData);

        return redirect()->route('cart.index')->with('success', 'Desain berhasil ditambahkan ke keranjang!');
    }

    /**
     * Memperbarui item di keranjang (quantity/size).
     */
    public function update(Request $request, CartItem $item)
    {
        // Pastikan user hanya bisa update item miliknya
        $this->authorizeCartItem($item);

        $item->update([
            'quantity' => $request->input('quantity', 1),
            'size' => $request->input('size', 'L'),
        ]);

        return redirect()->route('cart.index')->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function remove(CartItem $item)
    {
        $this->authorizeCartItem($item);
        $item->delete();
        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Helper untuk mengambil item keranjang.
     */
    private function getCartItems()
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->get();
        } else {
            return CartItem::where('session_id', session()->getId())->get();
        }
    }

    /**
     * Helper untuk otorisasi.
     */
    private function authorizeCartItem(CartItem $item)
    {
        if (Auth::check()) {
            if ($item->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if ($item->session_id !== session()->getId()) {
                abort(403, 'Unauthorized action.');
            }
        }
    }
}
