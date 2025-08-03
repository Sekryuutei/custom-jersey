<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{

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
    /**
     * Menampilkan daftar semua template.
     */
    public function index()
    {
        $templates = Template::latest()->get();
        // Anda perlu membuat view ini: resources/views/admin/templates/index.blade.php
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Menampilkan form untuk membuat template baru.
     */
    public function create()
    {
        return view('admin.templates.create', [
            'cloudinaryCloudName' => config('cloudinary.cloud_name'),
            'cloudinaryUploadPreset' => config('cloudinary.upload_preset')
        ]);
    }

    /**
     * Menyimpan template baru ke database setelah diunggah ke Cloudinary.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image_path' => 'required|string|url', // Mengharapkan URL, bukan file
        ]);

        try {
            // Menyimpan record baru ke database dengan URL dari Cloudinary
            Template::create([
                'name' => $request->name,
                'image_path' => $request->image_path,
            ]);

            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diunggah dan disimpan.');
        } catch (\Exception $e) {
            Log::error('Template Store Failed: ' . $e->getMessage(), ['exception' => $e]);

            $errorMessage = 'Gagal mengunggah template. Terjadi kesalahan tak terduga.';
            $lowerCaseMessage = strtolower($e->getMessage());

            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorMessage = 'Gagal menyimpan ke database. Pastikan struktur tabel sudah benar.';
            } else {
                $errorMessage = 'Gagal menyimpan template. Terjadi kesalahan tak terduga.';
            }

            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit template.
     */
    public function edit(Template $template)
    {
        return view('admin.templates.edit', [
            'template' => $template,
            'cloudinaryCloudName' => config('cloudinary.cloud_name'),
            'cloudinaryUploadPreset' => config('cloudinary.upload_preset')
        ]);
    }

    /**
     * Memperbarui data template di database.
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'image_path' => 'nullable|string|url', // Dibuat nullable dan harus URL
        ]);

        try {
            $oldImagePath = $template->image_path;
            $newImagePath = $validated['image_path'] ?? null;
            $dataToUpdate = ['name' => $validated['name']];

            // Hanya proses gambar jika URL baru diberikan dan berbeda dari yang lama
            if ($newImagePath && $newImagePath !== $oldImagePath) {
                $dataToUpdate['image_path'] = $newImagePath;

                // Hapus gambar lama dari Cloudinary jika ada dan valid
                if ($oldImagePath) {
                    $publicId = $this->getPublicIdFromUrl($oldImagePath);
                    if ($publicId) {
                        // Gunakan SDK langsung untuk keandalan
                        try {
                            Configuration::instance([
                                'cloud' => [
                                    'cloud_name' => config('cloudinary.cloud_name'),
                                    'api_key'    => config('cloudinary.api_key'),
                                    'api_secret' => config('cloudinary.api_secret'),
                                ],
                                'url' => ['secure' => true]
                            ]);
                            (new UploadApi())->destroy($publicId);
                            Log::info("Successfully deleted old Cloudinary asset '{$publicId}' during template update.");
                        } catch (\Exception $cloudinaryException) {
                            // Log sebagai peringatan, tapi jangan hentikan proses update
                            Log::warning('Could not delete old Cloudinary asset ' . $publicId . ' during update: ' . $cloudinaryException->getMessage());
                        }
                    }
                }
            }

            $template->update($dataToUpdate);

            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Template Update Failed for ID ' . $template->id . ': ' . $e->getMessage(), ['exception' => $e]);
            $errorMessage = 'Gagal memperbarui template. Terjadi kesalahan tak terduga.';

            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorMessage = 'Gagal memperbarui database. Pastikan struktur tabel sudah benar.';
            }

            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Menghapus template dari database dan file dari Cloudinary.
     */
    public function destroy(Template $template)
    {
        try {
            // Langkah 1: Coba hapus gambar dari Cloudinary.
            if ($template->image_path) {
                $publicId = $this->getPublicIdFromUrl($template->image_path);
                if ($publicId) {
                    // Menggunakan SDK Cloudinary secara langsung untuk menghindari masalah inisialisasi pada Facade.
                    try {
                        Configuration::instance([
                            'cloud' => [
                                'cloud_name' => config('cloudinary.cloud_name'),
                                'api_key'    => config('cloudinary.api_key'),
                                'api_secret' => config('cloudinary.api_secret'),
                            ],
                            'url' => ['secure' => true]
                        ]);

                        (new UploadApi())->destroy($publicId);

                        Log::info("Successfully deleted Cloudinary asset '{$publicId}' using direct SDK call.");
                    } catch (\Exception $cloudinaryException) {
                        // Jika SDK langsung juga gagal, ini adalah error yang sebenarnya.
                        Log::error('Direct Cloudinary SDK call failed for Public ID ' . $publicId . ': ' . $cloudinaryException->getMessage(), ['exception' => $cloudinaryException]);
                        // Lemparkan kembali error agar transaksi utama gagal dan pesan error ditampilkan.
                        throw $cloudinaryException;
                    }
                }
            }

            // Langkah 2: Hapus record dari database. Ini hanya akan terjadi jika langkah 1 berhasil.
            $template->delete();
            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil dihapus.');
        } catch (\Exception $e) {
            // Jika terjadi error (baik dari Cloudinary atau DB), log dan kembalikan pesan error.
            Log::error('Template Deletion Failed for ID ' . $template->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal menghapus template. Silakan periksa log untuk detail.');
        }
    }
}