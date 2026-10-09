# FoodRescue API
> Platform Penyelamatan Limbah Pangan & Penyaluran Donasi Makanan Realtime

---

## 📌 Informasi Kelompok
- **Nomor Kelompok:** Kelompok 04
- **Shift Praktikum:** Shift A

---

## 👥 Anggota Kelompok

| No | Nama Lengkap | NIM | Shift Awal | Shift Akhir | Jobdesk / Kontribusi | Link Video Penjelasan |
|---|---|---|---|---|---|---|
| 1 | Hana Nur Fathiyyah | H1H024017 | Shift A | Shift A | [Contoh: CRUD Fitur Reservasi & Autentikasi] | [YouTube/Drive](https://...) |
| 2 | Difa' Tamaya Maulidina Adz Dzikro | H1H024019 | Shift B | Shift A |  Modul Master Data & Pengguna (CRUD User & Profil, CRUD Kategori Makanan, CRUD Titik Lokasi) | [YouTube/Drive](https://...) |
| 3 | Nesa Dwi Cahyani | H1H024024 | [Shift Awal] | [Shift Akhir] | [Jobdesk Fitur] | [YouTube/Drive](https://...) |

---

## 📖 Deskripsi Aplikasi
**FoodRescue** adalah aplikasi RESTful API berbasis Laravel 13 yang dirancang untuk menghubungkan penyedia makanan berlebih (donatur) dengan pihak yang membutuhkan (penerima) serta relawan pengantar. Aplikasi ini bertujuan untuk menekan penumpukan limbah pangan (*food waste*) dan mempercepat penyaluran bantuan makanan secara terstruktur, terverifikasi, dan realtime.

---

## ⚙️ Penjelasan Teknis

### 1. Teknologi (Tech Stack)
- **Backend:** Laravel 13 (PHP 8.4)[cite: 6]
- **API Architecture:** RESTful API[cite: 1, 6]
- **Database:** MySQL / MariaDB[cite: 5, 6]
- **Authentication & Security:** Laravel Sanctum (Token-based Authentication) & Middleware Role Access Control
- **Realtime Notification:** Laravel Reverb / WebSockets

### 2. Fitur Utama & Modul
- **Autentikasi & Otorisasi:** Multi-role RBAC (*Admin, Donatur, Penerima, Relawan*) dengan token Sanctum.
- **Modul Master Data & Pengguna (Difa):**
  - **CRUD Pengguna & Profil:** Manajemen profil pengguna, koordinat alamat, serta pengelolaan data user oleh Admin.
  - **CRUD Kategori Makanan:** Pengelolaan jenis makanan, deskripsi, panduan penyimpanan, dan batas jam simpan.
  - **CRUD Titik Lokasi Penjemputan:** Pengelolaan daftar posko/zona penjemputan makanan beserta status aktifnya.
- **Modul Utama Donasi & Klaim (Hana):** [Modul Utama Donasi dan Klaim]
- **Modul Tindak Lanjut & Feedback (Nesa):** [Modul Tindak Lanjut dan Feedback]

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

