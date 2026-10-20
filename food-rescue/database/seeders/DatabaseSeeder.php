<?php

namespace Database\Seeders;

use App\Models\KategoriMakanan;
use App\Models\TitikLokasi;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Aman dijalankan berulang (updateOrCreate / firstOrCreate).
     * Kata sandi di bawah hanya untuk pengembangan; ganti di lingkungan produksi.
     */
    public function run(): void
    {
        // Akun: [email, nama, kata sandi, peran, no. telepon]
        $akun = [
            ['admin@foodrescue.test',    'Admin FoodRescue', 'Admin12345',    'admin',    '081234567890'],
            ['donatur@foodrescue.test',  'Donatur Uji',      'Donatur12345',  'donatur',  '081234567891'],
            ['penerima@foodrescue.test', 'Penerima Uji',     'Penerima12345', 'penerima', '081234567892'],
            ['relawan@foodrescue.test',  'Relawan Uji',      'Relawan12345',  'relawan',  '081234567893'],
        ];

        foreach ($akun as [$email, $nama, $sandi, $peran, $telepon]) {
            // Kata sandi di-hash otomatis oleh cast 'hashed' pada model User
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => $sandi, 'role' => $peran, 'no_telepon' => $telepon],
            );
        }

        // Master data kategori makanan (donasi wajib punya kategori)
        $kategori = [
            ['Makanan Berat',  'Nasi dan lauk pauk',     'Simpan tertutup di suhu ruang', 6],
            ['Roti & Kue',     'Roti, kue, dan camilan', 'Simpan di wadah kering',        24],
            ['Buah & Sayur',   'Buah dan sayur segar',   'Simpan di tempat sejuk',        48],
            ['Minuman',        'Minuman kemasan/segar',  'Simpan di lemari pendingin',    12],
        ];

        foreach ($kategori as [$nama, $deskripsi, $panduan, $jam]) {
            KategoriMakanan::firstOrCreate(
                ['nama' => $nama],
                ['deskripsi' => $deskripsi, 'panduan_simpan' => $panduan, 'batas_simpan_jam' => $jam],
            );
        }

        // Master data titik lokasi penjemputan
        TitikLokasi::firstOrCreate(
            ['nama' => 'Posko Alun-alun Purwokerto'],
            ['alamat' => 'Alun-alun Purwokerto, Banyumas', 'latitude' => -7.4242, 'longitude' => 109.2303, 'is_aktif' => true],
        );
    }
}
