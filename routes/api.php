<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriBukuController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::apiResource('buku', BukuController::class)->middleware('auth:api');
Route::apiResource('kategori-buku', KategoriBukuController::class)->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('peminjaman/dipinjam', [PeminjamanController::class, 'bukuDipinjam']);
    Route::post('peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan']);
    Route::apiResource('peminjaman', PeminjamanController::class);
});
