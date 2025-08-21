<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BilliardController;

Route::get('/', function () {
    return view('layout.main');
});

Route::get('/meja-billiard', [BilliardController::class, 'meja'])->name('billiard.meja');
Route::get('/list/{nomor_meja}', [BilliardController::class, 'list'])->name('billiard.list');
Route::get('/belanja', [BilliardController::class, 'belanja'])->name('billiard.belanja');
Route::post('/order', [BilliardController::class, 'belanjastore'])->name('belanja.store');
