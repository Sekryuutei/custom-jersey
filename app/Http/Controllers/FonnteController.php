<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FonnteController extends Controller
{
    /**
     * Menangani webhook yang masuk dari Fonnte.
     */
    public function handleWebhook(Request $request)
    {
        // Ambil data dari payload Fonnte
        $sender = $request->input('sender');
        $message = $request->input('message');
        $fileUrl = $request->input('url'); // URL gambar yang dilampirkan
        $fileName = $request->input('filename', 'image.jpg'); // Nama file asli

        // Log untuk debugging
        Log::info('Fonnte Webhook Received:', $request->all());

        // Nomor admin yang diizinkan (bisa juga disimpan di .env)
        $adminPhone = config('services.fonnte.admin_number');

        // 1. Validasi Pengirim
        if (!$adminPhone || $sender !== $adminPhone) {
            Log::warning('Unauthorized attempt from: ' . $sender);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // 2. Validasi Perintah
        $command = 'tambah template:';
        if (!Str::startsWith(strtolower($message), $command)) {
            $this->sendFonnteReply($sender, "Perintah tidak dikenali. Gunakan format:\n*tambah template: Nama Template Anda*");
            return response()->json(['status' => 'error', 'message' => 'Invalid command']);
        }

        // 3. Validasi Lampiran Gambar
        if (!$fileUrl) {
            $this->sendFonnteReply($sender, "Gagal. Anda harus melampirkan satu gambar untuk membuat template baru.");
            return response()->json(['status' => 'error', 'message' => 'No image attached']);
        }

        try {
            // Ekstrak nama template dari pesan
            $templateName = trim(substr($message, strlen($command)));

            // Unduh gambar dari URL
            $imageContent = Http::get($fileUrl)->body();

            // Buat nama file yang unik dan simpan
            $newFileName = 'templates/' . Str::slug($templateName) . '_' . time() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            Storage::disk('asset')->put($newFileName, $imageContent);

            // Simpan ke database
            Template::create([
                'name' => $templateName,
                'image_path' => $newFileName,
            ]);

            // Kirim notifikasi sukses ke admin
            $this->sendFonnteReply($sender, "✅ Template *'{$templateName}'* berhasil ditambahkan!");
            Log::info("Template '{$templateName}' created successfully by admin.");

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to create template via Fonnte: ' . $e->getMessage());
            $this->sendFonnteReply($sender, "❌ Terjadi kesalahan internal saat membuat template. Silakan coba lagi.");
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Helper untuk mengirim balasan via Fonnte API.
     */
    private function sendFonnteReply($target, $message)
    {
        Http::withHeaders([
            'Authorization' => config('services.fonnte.token')
        ])->post('https://api.fonnte.com/send', [
            'target' => $target,
            'message' => $message,
        ]);
    }
}
