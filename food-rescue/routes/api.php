<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\DonasiMakananController;
use App\Http\Controllers\Api\KlaimDonasiController;
use Illuminate\Support\Facades\Route;

// 1. Rute Autentikasi Publik
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// 2. Rute Terlindungi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Profil & Logout
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Akses Publik Terautentikasi (Semua Peran)
    Route::get('donasi', [DonasiMakananController::class, 'index']);

    // Akses khusus Donatur & Admin
    Route::middleware(['role:donatur,admin'])->group(function () {
        Route::post('donasi', [DonasiMakananController::class, 'store'])->middleware('ability:donasi:create,*');
    });

    // Akses khusus Penerima & Admin
    Route::middleware(['role:penerima,admin'])->group(function () {
        Route::post('klaim', [KlaimDonasiController::class, 'store'])->middleware('ability:klaim:create,*');
    });

    // Akses khusus Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dasbor', DasborController::class);
    });
});