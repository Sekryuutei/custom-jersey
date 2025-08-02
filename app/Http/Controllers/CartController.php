<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        return view('cart.index', compact('cart', 'totalPrice'));
    }

    /**
     * Menambahkan item ke keranjang.
     */
    public function add(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'designImage' => 'required|string',
        ]);

        $template = Template::findOrFail($request->template_id);
        $designImage = $request->designImage;

        // Simpan gambar desain ke file untuk referensi (praktik terbaik)
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $designImage));
        $designImageName = 'designs/' . Str::random(40) . '.png';
        Storage::disk('public')->put($designImageName, $imageData);

        // Buat ID unik untuk item di keranjang berdasarkan template dan hash desain
        $cartItemId = $template->id . '-' . md5($designImageName);

        // Ambil keranjang dari session, atau buat array kosong jika belum ada.
        // INILAH KUNCI UNTUK MENGHINDARI ERROR "Trying to access array offset on null"
        $cart = session()->get('cart', []);

        // Jika item yang sama persis sudah ada, tambahkan jumlahnya.
        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity']++;
        } else {
            // Jika tidak, tambahkan sebagai item baru.
            // Anda bisa menambahkan kolom 'price' di tabel templates Anda. Untuk saat ini, kita gunakan harga statis.
            $cart[$cartItemId] = [
                "name" => $template->name,
                "quantity" => 1,
                "size" => 'L', // Ukuran default saat item ditambahkan
                "price" => 50000, // Ganti dengan $template->price jika ada
                "template_image" => $template->image_path,
                "design_image_path" => $designImageName, // Simpan path, bukan data base64
                "template_id" => $template->id,
            ];
        }

        // Simpan kembali keranjang yang sudah diperbarui ke dalam session.
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Desain berhasil ditambahkan ke keranjang!');
    }

    /**
     * Memperbarui jumlah item di keranjang.
     */
    public function update(Request $request, $cartItemId)
    {
        $cart = session()->get('cart');

        if (isset($cart[$cartItemId])) {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'size' => 'required|string|in:S,M,L,XL,XXL',
            ]);

            $cart[$cartItemId]['quantity'] = $request->input('quantity');
            $cart[$cartItemId]['size'] = $request->input('size');
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Jumlah item berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function remove($cartItemId)
    {
        $cart = session()->get('cart');

        if (isset($cart[$cartItemId])) {
            // Hapus juga file gambar desain dari storage untuk menjaga kebersihan.
            Storage::disk('public')->delete($cart[$cartItemId]['design_image_path']);
            
            unset($cart[$cartItemId]);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
    }
}
