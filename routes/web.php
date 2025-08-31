<?php

use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Main application routes
Route::get('/', [TemplateController::class, 'home'])->name('home');
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/design/{template}', [TemplateController::class, 'showDesign'])->name('design.show');
Route::get('/guide', [TemplateController::class, 'guide'])->name('guide');

// Payment flow routes
Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{payment}', [PaymentController::class, 'update'])->name('payment.update');
Route::get('/order/{payment}', [PaymentController::class, 'order'])->name('order.show');


// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{cartItemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItemId}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

// Route untuk simulasi API ongkos kirim
Route::get('/shipping-options', [CheckoutController::class, 'getShippingOptions'])->name('shipping.options');

// Order status routes
Route::get('/order/{payment}', [PaymentController::class, 'order'])->name('order.show');
Route::get('/payment/{payment}/download', [PaymentController::class, 'download'])->name('payment.download'); // This route is now active

// Review routes
Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');

// Webhook routes
Route::post('/midtrans/notif', [PaymentController::class, 'notif'])->name('midtrans.notif');

// Admin Routes (Consider protecting these with middleware in the future)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [PaymentController::class, 'admin'])->name('index');
    Route::resource('templates', AdminTemplateController::class)->except(['show']);
    Route::get('orders/{payment}', [PaymentController::class, 'showOrder'])->name('orders.show');
    Route::patch('orders/{payment}/update-shipping', [PaymentController::class, 'updateShipping'])->name('orders.updateShipping');
    Route::resource('users', AdminUserController::class)->except(['show']);
});

// Laravel Breeze Authentication Routes
Route::get('/dashboard', function () {
    // Redirect pengguna berdasarkan role mereka setelah login
    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.index');
    }
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{payment}/confirm-delivery', [PaymentController::class, 'confirmDelivery'])->name('orders.confirm_delivery');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
