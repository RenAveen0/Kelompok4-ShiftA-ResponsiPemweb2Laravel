<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'beranda')->name('beranda');
Route::view('/masuk', 'auth.masuk')->name('masuk');
Route::view('/daftar', 'auth.daftar')->name('daftar');
Route::view('/dasbor', 'dasbor')->name('dasbor');
