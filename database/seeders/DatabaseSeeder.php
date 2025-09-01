<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // 1. Buat Akun Admin
        // // Pastikan model User Anda memiliki kolom 'role'
        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'), // Ganti dengan password yang aman
        //     'role' => 'admin',
        // ]);

        // // 2. Buat Akun Pelanggan
        // User::create([
        //     'name' => 'Pelanggan',
        //     'email' => 'pelanggan@example.com',
        //     'password' => Hash::make('password'), // Ganti dengan password yang aman
        //     'role' => 'user', // 'user' atau role default lainnya
        // ]);

        // Panggil semua seeder aplikasi dalam urutan yang benar
        $this->call([
            TemplateSeeder::class,
            UserSeeder::class,
        ]);
    }
}
