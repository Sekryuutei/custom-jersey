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
        // Anda perlu membuat view ini: resources/views/admin/templates/create.blade.php
        return view('admin.templates.create');
    }

    /**
     * Menyimpan template baru ke database setelah diunggah ke Cloudinary.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:svg,png,jpg,jpeg|max:2048',
        ]);

        try {
            // Mengunggah file ke Cloudinary dan mendapatkan URL yang aman
            $uploadedFileUrl = $request->file('image')->storeOnCloudinary('templates')->getSecurePath();

            // Menyimpan record baru ke database dengan URL dari Cloudinary
            Template::create([
                'name' => $request->name,
                'image_path' => $uploadedFileUrl, // Simpan URL, bukan nama file lokal
            ]);

            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diunggah dan disimpan.');
        } catch (\Exception $e) {
            Log::error('Cloudinary Upload Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengunggah template. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit template.
     */
    public function edit(Template $template)
    {
        // Memanggil view baru: resources/views/admin/templates/edit.blade.php
        return view('admin.templates.edit', compact('template'));
    }

    /**
     * Memperbarui data template di database.
     */
    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:svg,png,jpg,jpeg|max:2048', // Dibuat nullable
        ]);

        try {
            if ($request->hasFile('image')) {
                // Hapus gambar lama dari Cloudinary jika ada
                if ($template->image_path) {
                    $publicId = $this->getPublicIdFromUrl($template->image_path);
                    if ($publicId) {
                        \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::destroy($publicId);
                    }
                }

                // Unggah gambar baru dan dapatkan URL
                $uploadedFileUrl = $request->file('image')->storeOnCloudinary('templates')->getSecurePath();
                $data['image_path'] = $uploadedFileUrl;
            }

            $template->update($data);

            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Template Update Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui template. Silakan coba lagi.');
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
            }
            $template->delete();
            return redirect()->route('admin.templates.index')->with('success', 'Template berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Template Deletion Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus template. Silakan coba lagi.');
        }
    }
}