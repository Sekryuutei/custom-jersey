<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

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

        try {
            // Konfigurasi Cloudinary SDK secara langsung untuk menghindari masalah inisialisasi Facade.
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);

            // Unggah gambar base64 ke Cloudinary menggunakan SDK langsung
            $uploadResult = (new UploadApi())->upload($designImage, [
                'folder'        => 'jersey_designs',
                'resource_type' => 'image',
            ]);

            // Dapatkan URL aman dari gambar yang diunggah (hasilnya adalah array)
            $designImageUrl = $uploadResult['secure_url'];
        } catch (\Exception $e) {
            $errorMessage = 'Gagal mengunggah desain Anda. Silakan coba lagi.';
            // Jika dalam mode debug, tampilkan pesan error yang lebih detail untuk diagnosis
            if (config('app.debug')) {
                $errorMessage .= ' Detail Teknis: ' . $e->getMessage();
            }
            // Selalu log pesan error yang lengkap untuk server
            Log::error('Cloudinary Upload Failed: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with('error', $errorMessage)->withInput($request->except('designImage'));
        }

        // Buat ID unik untuk item di keranjang berdasarkan template dan hash desain
        $cartItemId = $template->id . '-' . md5($designImageUrl);

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
                "price" => 150000, // Ganti dengan $template->price jika ada
                "template_image" => $template->image_path,
                "design_image_path" => $designImageUrl, // Simpan URL Cloudinary
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
        $cart = session()->get('cart', []);

        if (isset($cart[$cartItemId])) {
            try {
                $designUrl = $cart[$cartItemId]['design_image_path'];

                // Hapus gambar dari Cloudinary jika URL-nya ada
                if ($designUrl && Str::contains($designUrl, 'cloudinary')) {
                    $publicId = $this->getPublicIdFromUrl($designUrl);
                    if ($publicId) {
                        // Menggunakan SDK Cloudinary secara langsung untuk konsistensi dan keandalan.
                        Configuration::instance([
                            'cloud' => [
                                'cloud_name' => config('cloudinary.cloud_name'),
                                'api_key'    => config('cloudinary.api_key'),
                                'api_secret' => config('cloudinary.api_secret'),
                            ],
                            'url' => ['secure' => true]
                        ]);

                        (new UploadApi())->destroy($publicId);

                        Log::info("Successfully deleted Cloudinary asset '{$publicId}' from cart using direct SDK call.");
                    }
                }

                // Hapus dari session HANYA jika Cloudinary berhasil (atau tidak ada gambar)
                unset($cart[$cartItemId]);
                session()->put('cart', $cart);
                return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');
            } catch (\Exception $e) {
                // Jika terjadi error saat menghapus dari Cloudinary, log dan beri tahu pengguna.
                // Item tidak akan dihapus dari keranjang.
                Log::error('Cart Item Removal Failed (Cloudinary Error): ' . $e->getMessage(), ['exception' => $e]);
                return redirect()->back()->with('error', 'Gagal menghapus item dari keranjang karena masalah server. Silakan coba lagi.');
            }
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
    }

    /**
     * Helper untuk mengekstrak public_id dari URL Cloudinary.
     */
    private function getPublicIdFromUrl(string $url): ?string
    {
        // Regex yang lebih andal: mencari bagian versi (/v12345/), mengambil semua setelahnya,
        // lalu menghapus ekstensi file. Ini bekerja bahkan jika ada transformasi URL.
        // Contoh: .../upload/w_200/v12345/folder/file.jpg -> folder/file
        if (preg_match('/\/v\d+\/(.*)$/', $url, $matches)) {
            $pathWithExtension = $matches[1];
            // Menghapus ekstensi file (e.g., .jpg, .png) dari akhir string.
            $publicId = preg_replace('/\.\w+$/', '', $pathWithExtension);
            return $publicId;
        }

        // Jika tidak ada versi di URL, kita tidak bisa mengekstrak ID dengan andal.
        return null;
    }
}
