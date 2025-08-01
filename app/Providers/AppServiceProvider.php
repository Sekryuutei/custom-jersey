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
        // Bagikan data jumlah item keranjang ke semua view yang menggunakan 'master' layout
        View::composer('master', CartComposer::class);
    }
}
