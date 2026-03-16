<div align="center">

# 🛒 Waroong POS

**Aplikasi kasir modern untuk warung kelontong Indonesia**

Dibangun di atas Laravel + FilamentPHP. Multi-tenant, mobile-friendly, dan siap pakai.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/FilamentPHP-3.x-FDC950?style=flat-square&logo=laravel&logoColor=black)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](LICENSE)

</div>

---

## Fitur Utama

| Fitur | Keterangan |
|---|---|
| 🧾 **Kasir** | Keranjang belanja, auto-kalkulasi subtotal & total |
| 💳 **Multi Pembayaran** | Tunai, QRIS (upload bukti), Kasbon |
| 📒 **Manajemen Kasbon** | Tracking utang & pelunasan per pelanggan |
| 📊 **Dashboard** | Omset, keuntungan, kasbon aktif, produk terlaris |
| 🖨️ **Cetak Struk** | Format thermal 58mm, auto-print |
| 📷 **Barcode Scanner** | Scanner fisik & kamera HP |
| 🏪 **Multi-Tenant** | Tiap user punya warung & katalog sendiri |
| 🔐 **Login Google** | Daftar & masuk cukup dengan akun Google |

---

## Tech Stack

- **[Laravel 10](https://laravel.com)** — Backend framework
- **[FilamentPHP v3](https://filamentphp.com)** — Admin panel (TALL Stack)
- **[Laravel Socialite](https://laravel.com/docs/socialite)** — Google OAuth
- **MySQL** — Database

---

## Instalasi

### 1. Clone & Install

```bash
git clone https://github.com/akbarr13/waroong.git
cd waroong
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Konfigurasi Database

Edit `.env`:

```env
DB_DATABASE=waroong
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Setup Google OAuth

1. Buka [Google Cloud Console](https://console.cloud.google.com)
2. Buat project → **APIs & Services** → **Credentials** → **Create OAuth 2.0 Client ID**
3. Pilih tipe **Web application**
4. Tambahkan Authorized redirect URI:
   ```
   http://localhost:8000/auth/google/callback
   ```
5. Salin Client ID & Secret ke `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 4. Migrasi & Jalankan

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Buka **[http://localhost:8000/admin](http://localhost:8000/admin)** dan login dengan Google.

---

## Lisensi

[MIT](LICENSE) — bebas digunakan dan dimodifikasi.
