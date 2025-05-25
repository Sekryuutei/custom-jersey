<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\PaymentController;
use app\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [CustomController::class, 'index']);
Route::post('/save-design', [CustomController::class, 'store']);
Route::get('/download/{id}', [CustomController::class, 'download']);
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/design/{template}', [TemplateController::class, 'design']);

Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{payment}', [PaymentController::class, 'update'])->name('payment.update');

Route::get('/admin', [PaymentController::class, 'admin'])->name('admin');
Route::get('/admin/payment/{payment}/download', [PaymentController::class, 'download'])->name('payment.download');

Route::post('/payment/notif', [PaymentController::class, 'notif'])->name('payment.notif');