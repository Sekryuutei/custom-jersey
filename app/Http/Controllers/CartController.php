<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
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

        // --- VALIDASI UKURAN FILE ---
        // Hapus header data URI untuk mendapatkan data base64 murni
        $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $designImage);
        // Hitung perkiraan ukuran file dalam bytes. (panjang_string * 3/4)
        $fileSizeBytes = (int)(strlen($base64Data) * 0.75);
        $maxSizeBytes = 5 * 1024 * 1024; // 5 MB

        if ($fileSizeBytes > $maxSizeBytes) {
            return redirect()->back()
                ->with('error', 'Ukuran file desain terlalu besar. Maksimal 5 MB.')
                ->withInput($request->except('designImage'));
        }
        // --- AKHIR VALIDASI UKURAN FILE ---

        // Pastikan string base64 memiliki data URI scheme yang benar.
        // Cloudinary mengharapkan format seperti "data:image/png;base64,iVBORw0KGgo...".
        // Jika tidak ada, kita tambahkan prefix default untuk PNG.
        if (Str::startsWith($designImage, 'data:image')) {
            $designImage = 'data:image/png;base64,' . $designImage;
        }

        // --- VALIDASI KONFIGURASI CLOUDINARY ---
        // Cek ini penting untuk memastikan upload dari sisi server (signed upload) bisa berjalan.
        if (!config('cloudinary.cloud_name') || !config('cloudinary.api_key') || !config('cloudinary.api_secret')) {
            $missingKeys = [];
            if (!config('cloudinary.cloud_name')) $missingKeys[] = 'CLOUDINARY_CLOUD_NAME';
            if (!config('cloudinary.api_key')) $missingKeys[] = 'CLOUDINARY_API_KEY';
            if (!config('cloudinary.api_secret')) $missingKeys[] = 'CLOUDINARY_API_SECRET';

            $errorMessage = 'Konfigurasi server untuk unggah gambar tidak lengkap. Silakan hubungi administrator.';
            // Log error yang lebih spesifik untuk developer
            Log::error('Cloudinary configuration is incomplete. Missing or null values for: ' . implode(', ', $missingKeys));
            return redirect()->back()->with('error', $errorMessage)->withInput($request->except('designImage'));
        }

        try {
            // Unggah gambar base64 ke Cloudinary
            $uploadResult = Cloudinary::upload($designImage, [
                'folder' => 'jersey_designs',
                'resource_type' => 'image'
            ]);
            
            // Dapatkan URL aman dari gambar yang diunggah
            $designImageUrl = $uploadResult->getSecurePath();

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
        $cart = session()->get('cart', []); // Default ke array kosong untuk mencegah error pada sesi null

        if (isset($cart[$cartItemId])) {
            $designUrl = $cart[$cartItemId]['design_image_path'];
            
            // Hapus gambar dari Cloudinary jika URL-nya ada
            if ($designUrl && Str::contains($designUrl, 'cloudinary')) {
                try {
                    $publicId = $this->getPublicIdFromUrl($designUrl);
                    if ($publicId) {
                        Cloudinary::destroy($publicId);
                    }
                } catch (\Exception $e) {
                    // Log error tapi jangan hentikan proses penghapusan dari keranjang
                    Log::warning('Cloudinary Deletion Failed on Cart Remove: ' . $e->getMessage());
                }
            }
            
            unset($cart[$cartItemId]);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
    }

    /**
     * Helper untuk mengekstrak public_id dari URL Cloudinary.
     */
    private function getPublicIdFromUrl(string $url): ?string
    {
        // Mengekstrak "folder/file" dari URL seperti:
        // https://res.cloudinary.com/cloud/image/upload/v12345/folder/file.jpg
        if (preg_match('/\/v\d+\/(.+?)(?:\.\w+)?$/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
