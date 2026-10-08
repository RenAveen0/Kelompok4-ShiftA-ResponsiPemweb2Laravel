<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\DonasiMakananController;
use App\Http\Controllers\Api\KategoriMakananController;
use App\Http\Controllers\Api\KlaimDonasiController;
use App\Http\Controllers\Api\TitikLokasiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BuktiSerahTerimaController;
use App\Http\Controllers\Api\UlasanDonasiController;
use App\Http\Controllers\Api\LaporanKelayakanController;
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

    // --- MODUL NESA: Bukti Serah Terima, Ulasan, & Laporan Kelayakan ---
    Route::get('bukti-serah-terima', [BuktiSerahTerimaController::class, 'index']);
    Route::post('bukti-serah-terima', [BuktiSerahTerimaController::class, 'store']);
    Route::get('bukti-serah-terima/{id}', [BuktiSerahTerimaController::class, 'show']);
    Route::delete('bukti-serah-terima/{id}', [BuktiSerahTerimaController::class, 'destroy']);

    Route::get('ulasan', [UlasanDonasiController::class, 'index']);
    Route::post('ulasan', [UlasanDonasiController::class, 'store']);
    Route::get('ulasan/{id}', [UlasanDonasiController::class, 'show']);
    Route::put('ulasan/{id}', [UlasanDonasiController::class, 'update']);
    Route::delete('ulasan/{id}', [UlasanDonasiController::class, 'destroy']);

    Route::get('laporan-kelayakan', [LaporanKelayakanController::class, 'index']);
    Route::post('laporan-kelayakan', [LaporanKelayakanController::class, 'store']);
    Route::get('laporan-kelayakan/{id}', [LaporanKelayakanController::class, 'show']);
    Route::put('laporan-kelayakan/{id}', [LaporanKelayakanController::class, 'update']);
    Route::delete('laporan-kelayakan/{id}', [LaporanKelayakanController::class, 'destroy']);

    // --- MODUL HANA & NESA ---
    Route::get('donasi', [DonasiMakananController::class, 'index']);
    Route::middleware(['role:donatur,admin'])->group(function () {
        Route::post('donasi', [DonasiMakananController::class, 'store'])->middleware('ability:donasi:create,*');
    });
    Route::middleware(['role:penerima,admin'])->group(function () {
        Route::post('klaim', [KlaimDonasiController::class, 'store'])->middleware('ability:klaim:create,*');
    });
});