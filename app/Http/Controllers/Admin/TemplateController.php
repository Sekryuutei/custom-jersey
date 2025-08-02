<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        // Mengekstrak "folder/file" dari URL seperti:
        // https://res.cloudinary.com/cloud/image/upload/v12345/folder/file.jpg
        if (preg_match('/\/v\d+\/(.+?)(?:\.\w+)?$/', $url, $matches)) {
            return $matches[1];
        }
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
            'cloudinaryCloudName' => config('services.cloudinary.cloud_name'),
            'cloudinaryUploadPreset' => config('services.cloudinary.upload_preset')
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
            'cloudinaryCloudName' => config('services.cloudinary.cloud_name'),
            'cloudinaryUploadPreset' => config('services.cloudinary.upload_preset')
        ]);
    }

    /**
     * Memperbarui data template di database.
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_path' => 'nullable|string|url', // Dibuat nullable dan harus URL
        ]);

        try {
            $oldImagePath = $template->image_path;
            $dataToUpdate = ['name' => $validated['name']];

            // Jika URL gambar baru dikirimkan
            if (isset($validated['image_path']) && $validated['image_path']) {
                $dataToUpdate['image_path'] = $validated['image_path'];

                // Hapus gambar lama dari Cloudinary jika ada
                if ($oldImagePath) {
                    $publicId = $this->getPublicIdFromUrl($oldImagePath);
                    if ($publicId) \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicId);
                }
            }

            $template->update($dataToUpdate);

            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Template Update Failed: ' . $e->getMessage(), ['exception' => $e]);
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
            // Hapus gambar dari Cloudinary jika ada
            if ($template->image_path) {
                $publicId = $this->getPublicIdFromUrl($template->image_path);
                if ($publicId) {
                    \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicId);
                }
            }
            $template->delete();
            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Template Deletion Failed: ' . $e->getMessage(), ['exception' => $e]);

            $errorMessage = 'Gagal menghapus template. Terjadi kesalahan tak terduga.';
            $lowerCaseMessage = strtolower($e->getMessage());

            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorMessage = 'Gagal menghapus dari database. Mungkin ada data lain yang terkait.';
            } elseif (str_contains($lowerCaseMessage, 'api key') || str_contains($lowerCaseMessage, 'api secret') || str_contains($lowerCaseMessage, 'cloud name')) {
                $errorMessage = 'Gagal menghapus: Konfigurasi Cloudinary tidak valid di server.';
            } elseif (str_contains($lowerCaseMessage, 'not found')) {
                $errorMessage = 'Gagal menghapus: Gambar tidak ditemukan di Cloudinary.';
            } elseif (str_contains($lowerCaseMessage, 'timed out') || str_contains($lowerCaseMessage, 'network')) {
                $errorMessage = 'Gagal menghapus: Terjadi masalah koneksi ke server Cloudinary.';
            }

            return back()->with('error', $errorMessage);
        }
    }
}