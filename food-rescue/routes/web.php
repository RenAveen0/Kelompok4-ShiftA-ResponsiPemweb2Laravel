<?php

use Illuminate\Support\Facades\Route;

// Halaman antarmuka (frontend). Data diambil lewat API /api/* menggunakan token Sanctum.

// Tamu (guest)
Route::view('/', 'beranda')->name('beranda');
Route::view('/masuk', 'auth.masuk')->name('masuk');
Route::view('/daftar', 'auth.daftar')->name('daftar');

// Pengguna (donatur / penerima / relawan)
Route::view('/dasbor', 'dasbor')->name('dasbor');

// Admin: pengelolaan CRUD (akses dibatasi di sisi API oleh middleware role:admin)
Route::view('/admin', 'admin')->name('admin');
