<?php

namespace App\Providers;

use App\Http\View\Composers\CartComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Memaksa semua URL yang dihasilkan (asset, route, dll.) untuk menggunakan HTTPS
        // jika aplikasi berjalan di lingkungan produksi.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        // Bagikan data jumlah item keranjang ke semua view yang menggunakan 'master' layout
        View::composer('master', CartComposer::class);
    }
}
