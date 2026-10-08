<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\DonasiMakananController;
use App\Http\Controllers\Api\KategoriMakananController;
use App\Http\Controllers\Api\KlaimDonasiController;
use App\Http\Controllers\Api\TitikLokasiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Auth Publik
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login'])->name('login');

// Rute Terlindungi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // --- MODUL DIFA: Profil & Data Master ---
    Route::get('user/profil', [UserController::class, 'profil']);
    Route::put('user/profil', [UserController::class, 'updateProfil']);
    
    Route::get('kategori-makanan', [KategoriMakananController::class, 'index']);
    Route::get('kategori-makanan/{id}', [KategoriMakananController::class, 'show']);
    
    Route::get('titik-lokasi', [TitikLokasiController::class, 'index']);
    Route::get('titik-lokasi/{id}', [TitikLokasiController::class, 'show']);

    // Khusus Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dasbor', DasborController::class);
        
        Route::get('users', [UserController::class, 'index']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);

        Route::post('kategori-makanan', [KategoriMakananController::class, 'store']);
        Route::put('kategori-makanan/{id}', [KategoriMakananController::class, 'update']);
        Route::delete('kategori-makanan/{id}', [KategoriMakananController::class, 'destroy']);

        Route::post('titik-lokasi', [TitikLokasiController::class, 'store']);
        Route::put('titik-lokasi/{id}', [TitikLokasiController::class, 'update']);
        Route::delete('titik-lokasi/{id}', [TitikLokasiController::class, 'destroy']);
    });

    // --- MODUL HANA & NESA ---
    Route::get('donasi', [DonasiMakananController::class, 'index']);
    Route::middleware(['role:donatur,admin'])->group(function () {
        Route::post('donasi', [DonasiMakananController::class, 'store'])->middleware('ability:donasi:create,*');
    });
    Route::middleware(['role:penerima,admin'])->group(function () {
        Route::post('klaim', [KlaimDonasiController::class, 'store'])->middleware('ability:klaim:create,*');
    });
});