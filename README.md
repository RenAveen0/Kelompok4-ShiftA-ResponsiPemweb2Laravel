# FoodRescue API
> Platform Penyelamatan Limbah Pangan & Penyaluran Donasi Makanan Realtime

---

## 📌 Informasi Kelompok
- **Nomor Kelompok:** Kelompok 04
- **Shift Praktikum:** Shift A

---

## 👥 Anggota Kelompok

| No | Nama Lengkap                      |    NIM    | Shift Awal | Shift Akhir | Jobdesk / Kontribusi                                                                                     | Link Video Penjelasan |
|:--:|:----------------------------------|:---------:|:----------:|:-----------:|:---------------------------------------------------------------------------------------------------------|:---------------------:|
| 1  | Hana Nur Fathiyyah                | H1H024017 |  Shift A   |   Shift A   | Modul Utama Donasi & Klaim (CRUD Donasi Makanan, CRUD Klaim Donasi, CRUD Penugasan Relawan)              | [YouTube/Drive](https://...) |
| 2  | Difa' Tamaya Maulidina Adz Dzikro | H1H024019 |  Shift A   |   Shift A   | Modul Master Data & Pengguna (CRUD User & Profil, CRUD Kategori Makanan, CRUD Titik Lokasi)             | [YouTube/Drive](https://...) |
| 3  | Nesa Dwi Cahyani                  | H1H024024 |  Shift A   |   Shift A   | Modul Tindak Lanjut & Feedback (CRUD Bukti Serah Terima, CRUD Ulasan & Rating, CRUD Laporan Kelayakan) | [YouTube/Drive](https://...) |
---

## 📖 Deskripsi Aplikasi
**FoodRescue** adalah aplikasi RESTful API berbasis Laravel 13 yang dirancang untuk menghubungkan penyedia makanan berlebih (donatur) dengan pihak yang membutuhkan (penerima) serta relawan pengantar. Aplikasi ini bertujuan untuk menekan penumpukan limbah pangan (*food waste*) dan mempercepat penyaluran bantuan makanan secara terstruktur, terverifikasi, dan realtime.

---

## ⚙️ Penjelasan Teknis

### 1. Teknologi (Tech Stack)
- **Backend:** Laravel 13 (PHP 8.4)
- **API Architecture:** RESTful API
- **Database:** MySQL / MariaDB
- **Authentication & Security:** Laravel Sanctum (Token-based Authentication) & Middleware Role Access Control
- **Realtime Notification:** Laravel Reverb / WebSockets

### 2. Fitur Utama & Modul
- **Autentikasi & Otorisasi:** Multi-role RBAC (*Admin, Donatur, Penerima, Relawan*) dengan token Sanctum.
- **Modul Master Data & Pengguna (Difa):**
  - **CRUD Pengguna & Profil:** Manajemen profil pengguna, koordinat alamat, serta pengelolaan data user oleh Admin.
  - **CRUD Kategori Makanan:** Pengelolaan jenis makanan, deskripsi, panduan penyimpanan, dan batas jam simpan.
  - **CRUD Titik Lokasi Penjemputan:** Pengelolaan daftar posko/zona penjemputan makanan beserta status aktifnya.
- **Modul Utama Donasi & Klaim (Hana):**
  - **CRUD Donasi Makanan:** Pengelolaan data postingan donasi makanan oleh donatur.
  - **CRUD Klaim Donasi:** Pengajuan dan pengelolaan pengklaiman makanan oleh penerima.
  - **CRUD Penugasan Relawan:** Penugasan relawan untuk penjemputan dan pengantaran donasi.
- **Modul Tindak Lanjut & Feedback (Nesa):**
  - **CRUD Bukti Serah Terima:** Unggah foto dan catatan verifikasi penyerahan donasi oleh relawan/penerima.
  - **CRUD Ulasan & Rating:** Pengisian nilai kepuasan (1-5) dan masukan atas donasi yang telah diterima.
  - **CRUD Laporan Kelayakan:** Pelaporan jika ditemukan kondisi makanan yang tidak layak atau bungkus rusak.

### 3. Skema Data Singkat
- `users` (1 : N) `donasi_makanan`
- `kategori_makanan` (1 : N) `donasi_makanan`
- `titik_lokasi` (1 : N) `donasi_makanan`
- `donasi_makanan` (1 : 1) `klaim_donasi`
- `klaim_donasi` (1 : 1) `penugasan_relawan`
- `penugasan_relawan` (1 : 1) `bukti_serah_terima`
- `klaim_donasi` (1 : 1) `ulasan_donasi`

---

## 🚀 Panduan Instalasi Lokal

```bash
# Clone repository
git clone <URL_REPOSITORY>
cd food-rescue

# Install dependensi PHP
composer install

# Konfigurasi Environment
cp .env.example .env
php artisan key:generate

# Atur koneksi database di .env (DB_DATABASE=foodrescue_db), lalu migrasi & seed
php artisan migrate --seed

# Jalankan development server
php artisan serve