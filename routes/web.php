<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomController;
use App\Http\Controllers\TemplateController;



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [CustomController::class, 'index']);
Route::post('/save-design', [CustomController::class, 'store']);
Route::get('/download/{id}', [CustomController::class, 'download']);
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/design/{template}', [TemplateController::class, 'design']);