<?php

return [
    // Konfigurasi ini akan digunakan oleh aplikasi dan mereferensikan
    // variabel .env dengan benar.
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
    'api_key'    => env('CLOUDINARY_API_KEY', ''),
    'api_secret' => env('CLOUDINARY_API_SECRET', ''),

    // Kita secara eksplisit mengatur 'url' dan 'cloud_url' menjadi null.
    // Ini memaksa paket untuk menggunakan api_key dan api_secret di atas,
    // dan mencegahnya menggunakan URL yang salah dari konfigurasi defaultnya.
    'url'        => null,
    'cloud_url'  => null,

    // Diperlukan untuk widget upload di frontend.
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', ''),

    // URL notifikasi default.
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
];
