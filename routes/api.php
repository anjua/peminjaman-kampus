<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::post('login', [Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [Api\AuthController::class, 'logout']);
    Route::get('ruang', [Api\DataController::class, 'ruang']);
    Route::get('alat', [Api\DataController::class, 'alat']);
    Route::get('peminjaman', [Api\PeminjamanController::class, 'index']);
    Route::post('peminjaman', [Api\PeminjamanController::class, 'store'])->middleware('hak:ajukan-peminjaman');
    Route::get('peminjaman/{peminjaman}', [Api\PeminjamanController::class, 'show']);
});
