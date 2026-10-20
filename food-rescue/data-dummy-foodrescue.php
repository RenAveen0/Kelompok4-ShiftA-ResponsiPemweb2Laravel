<?php

/**
 * Data dummy FoodRescue untuk uji coba.
 *
 * Jalankan dari folder food-rescue (taruh file ini di folder yang sama):
 *   php artisan tinker data-dummy-foodrescue.php
 *
 * Aman dijalankan berulang: setiap data dicari dulu dan baru dibuat bila belum ada.
 * Semua pembuatan data dibungkus satu transaksi, jadi kalau ada yang gagal tidak ada data setengah jadi.
 */

use App\Models\BuktiSerahTerima;
use App\Models\DonasiMakanan;
use App\Models\KategoriMakanan;
use App\Models\KlaimDonasi;
use App\Models\LaporanKelayakan;
use App\Models\PenugasanRelawan;
use App\Models\TitikLokasi;
use App\Models\UlasanDonasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Migrasi ulasan_donasi (kolom id) sudah diperbaiki; jalankan `php artisan migrate:fresh --seed` dulu.
$denganFeedback = true;

$ringkasan = DB::transaction(function () use ($denganFeedback) {

    // ---------- 1. Akun ----------
    // kunci => [email, nama, kata sandi, peran, no. telepon]
    $daftarAkun = [
        'admin'    => ['admin@foodrescue.test',    'Admin FoodRescue', 'Admin12345',    'admin',    '081234567890'],
        'donatur'  => ['donatur@foodrescue.test',  'Donatur Uji',      'Donatur12345',  'donatur',  '081234567891'],
        'penerima' => ['penerima@foodrescue.test', 'Penerima Uji',     'Penerima12345', 'penerima', '081234567892'],
        'relawan1' => ['relawan@foodrescue.test',  'Relawan Uji',      'Relawan12345',  'relawan',  '081234567893'],
        'relawan2' => ['relawan2@foodrescue.test', 'Relawan Dua',      'Relawan12345',  'relawan',  '081234567894'],
    ];

    $akun = [];
    foreach ($daftarAkun as $kunci => [$email, $nama, $sandi, $peran, $telepon]) {
        // Kata sandi di-hash otomatis oleh cast 'hashed' pada model User.
        $akun[$kunci] = User::updateOrCreate(
            ['email' => $email],
            ['name' => $nama, 'password' => $sandi, 'role' => $peran, 'no_telepon' => $telepon],
        );
    }

    // ---------- 2. Master data ----------
    $kategori = [
        'berat' => KategoriMakanan::firstOrCreate(
            ['nama' => 'Makanan Berat'],
            ['deskripsi' => 'Nasi dan lauk pauk', 'panduan_simpan' => 'Simpan tertutup di suhu ruang', 'batas_simpan_jam' => 6],
        ),
        'roti' => KategoriMakanan::firstOrCreate(
            ['nama' => 'Roti & Kue'],
            ['deskripsi' => 'Roti, kue, dan camilan', 'panduan_simpan' => 'Simpan di wadah kering', 'batas_simpan_jam' => 24],
        ),
    ];

    $titik = TitikLokasi::firstOrCreate(
        ['nama' => 'Posko Pusat'],
        ['alamat' => 'Jl. Merdeka No. 1', 'latitude' => -7.4300, 'longitude' => 109.2400, 'is_aktif' => true],
    );

    // ---------- 3. Donasi, klaim, dan penugasan ----------
    // 'klaim' bersifat opsional. 'relawan' dan 'tugas' hanya diisi bila klaim sudah punya relawan.
    $donasiUji = [
        ['judul' => 'Nasi Kotak Syukuran',      'kategori' => 'berat', 'porsi' => 20, 'lokasi' => 'Jl. Merdeka No. 10'],
        ['judul' => 'Roti Sisa Toko Hari Ini',  'kategori' => 'roti',  'porsi' => 15, 'lokasi' => 'Toko Roti Manis, Jl. Pasar',
            'klaim' => ['porsi' => 3, 'status' => 'diajukan']],
        ['judul' => 'Nasi Ayam Bakar Catering', 'kategori' => 'berat', 'porsi' => 10, 'lokasi' => 'Dapur Catering, Jl. Veteran',
            'klaim' => ['porsi' => 4, 'status' => 'disetujui']],
        ['judul' => 'Nasi Bungkus Warteg',      'kategori' => 'berat', 'porsi' => 8,  'lokasi' => 'Warteg Bahari, Jl. Pasar',
            'klaim' => ['porsi' => 2, 'status' => 'disetujui', 'relawan' => 'relawan1', 'tugas' => 'menuju_lokasi']],
        ['judul' => 'Katering Hajatan',         'kategori' => 'berat', 'porsi' => 30, 'lokasi' => 'Gedung Serbaguna',
            'klaim' => ['porsi' => 10, 'status' => 'selesai', 'relawan' => 'relawan2', 'tugas' => 'selesai']],
        ['judul' => 'Kue Basah Arisan',         'kategori' => 'roti',  'porsi' => 12, 'lokasi' => 'Jl. Kenanga No. 20',
            'klaim' => ['porsi' => 2, 'status' => 'ditolak']],
    ];

    $selesai = null; // klaim dan donasi yang sudah selesai, dipakai untuk data feedback

    foreach ($donasiUji as $data) {
        $klaimData = $data['klaim'] ?? null;

        // Klaim yang ditolak/dibatalkan sudah mengembalikan porsinya, sisanya mengurangi stok.
        $memegangPorsi = $klaimData && ! in_array($klaimData['status'], ['ditolak', 'dibatalkan'], true);
        $sisa = $data['porsi'] - ($memegangPorsi ? $klaimData['porsi'] : 0);

        $donasi = DonasiMakanan::firstOrCreate(
            ['judul' => $data['judul']],
            [
                'user_id'         => $akun['donatur']->id,
                'kategori_id'     => $kategori[$data['kategori']]->id,
                'titik_lokasi_id' => $titik->id,
                'deskripsi'       => 'Data dummy uji coba.',
                'porsi'           => $data['porsi'],
                'porsi_tersisa'   => $sisa,
                'lokasi'          => $data['lokasi'],
                'batas_waktu'     => now()->addHours(72),
                'status'          => $sisa === 0 ? 'habis' : 'tersedia',
            ],
        );

        if (! $klaimData) {
            continue;
        }

        $relawan = isset($klaimData['relawan']) ? $akun[$klaimData['relawan']] : null;

        $klaim = KlaimDonasi::firstOrCreate(
            ['donasi_id' => $donasi->id, 'penerima_id' => $akun['penerima']->id],
            ['jumlah_porsi' => $klaimData['porsi'], 'status' => $klaimData['status'], 'relawan_id' => $relawan?->id],
        );

        if ($relawan) {
            $penugasan = PenugasanRelawan::firstOrCreate(
                ['klaim_id' => $klaim->id],
                ['relawan_id' => $relawan->id, 'status' => $klaimData['tugas'], 'catatan' => 'Data dummy.'],
            );

            // Kolom waktu tidak ada di $fillable, jadi diisi dengan forceFill.
            if ($klaimData['tugas'] === 'selesai' && ! $penugasan->waktu_selesai) {
                $penugasan->forceFill(['waktu_ambil' => now()->subHours(2), 'waktu_selesai' => now()->subHour()])->save();
            }
        }

        if ($klaimData['status'] === 'selesai') {
            $selesai = ['donasi' => $donasi, 'klaim' => $klaim, 'relawan' => $relawan];
        }
    }

    // ---------- 4. Feedback (opsional) ----------
    if ($denganFeedback && $selesai) {
        BuktiSerahTerima::firstOrCreate(
            ['klaim_id' => $selesai['klaim']->id],
            [
                'relawan_id'         => $selesai['relawan']->id,
                'foto_bukti'         => 'https://placehold.co/600x400?text=Bukti+Serah+Terima',
                'catatan'            => 'Makanan diterima dalam kondisi baik.',
                'waktu_serah_terima' => now()->subHour(),
            ],
        );

        UlasanDonasi::firstOrCreate(
            ['klaim_id' => $selesai['klaim']->id],
            ['user_id' => $akun['penerima']->id, 'rating' => 5, 'komentar' => 'Makanan masih hangat dan porsinya pas.'],
        );

        LaporanKelayakan::firstOrCreate(
            ['donasi_id' => $selesai['donasi']->id, 'pelapor_id' => $akun['penerima']->id],
            ['alasan' => 'Sebagian kemasan sedikit rusak saat diterima.', 'status' => 'menunggu_tinjauan'],
        );
    }

    return [
        'users'     => User::count(),
        'kategori'  => KategoriMakanan::count(),
        'titik'     => TitikLokasi::count(),
        'donasi'    => DonasiMakanan::count(),
        'klaim'     => KlaimDonasi::count(),
        'penugasan' => PenugasanRelawan::count(),
        'bukti'     => $denganFeedback ? BuktiSerahTerima::count() : '(dilewati)',
        'ulasan'    => $denganFeedback ? UlasanDonasi::count() : '(dilewati)',
        'laporan'   => $denganFeedback ? LaporanKelayakan::count() : '(dilewati)',
    ];
});

echo json_encode($ringkasan, JSON_PRETTY_PRINT) . PHP_EOL;