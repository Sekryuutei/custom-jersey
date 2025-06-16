<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\PaymentController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [TemplateController::class, 'home']);
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/design/{template}', [TemplateController::class, 'design']);
Route::get('/tutor', [TemplateController::class, 'tutor']);

Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{payment}', [PaymentController::class, 'update'])->name('payment.update');

Route::get('/admin', [PaymentController::class, 'admin'])->name('admin');
Route::get('/admin/payment/{payment}/download', [PaymentController::class, 'download'])->name('payment.download');
Route::get('/order/{payment}', [PaymentController::class, 'order'])->name('order.show');

Route::post('/midtrans/notif', [PaymentController::class, 'notif'])->name('midtrans.notif');

// Route::post('/mail/{payment}', [PaymentController::class, 'mail'])->name('mail.send');