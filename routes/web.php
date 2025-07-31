<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FonnteController;


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

Route::get('/order/{payment}', [PaymentController::class, 'order'])->name('order.show');

Route::post('/midtrans/notif', [PaymentController::class, 'notif'])->name('midtrans.notif');

// Fonnte Webhook Route
Route::any('/fonnte/webhook', [FonnteController::class, 'handleWebhook'])->name('fonnte.webhook');
