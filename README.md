# Waroong POS

Aplikasi Point of Sale (POS) untuk warung kelontong (UMKM) Indonesia. Multi-tenant — setiap user punya warung dan katalog sendiri.

## Fitur

- **Kasir** — form transaksi dengan keranjang belanja, auto-kalkulasi harga & stok
- **Metode Pembayaran** — Tunai, QRIS (upload bukti), Kasbon
- **Manajemen Kasbon** — tracking utang pelanggan, pelunasan langsung dari tabel
- **Dashboard** — omset harian, keuntungan, kasbon aktif, produk terlaris, stok menipis
- **Cetak Struk** — format thermal 58mm, auto-print
- **Barcode Scanner** — scanner fisik (keyboard) & kamera HP
- **Multi-tenant** — tiap user punya katalog produk, kategori, dan pelanggan sendiri
- **Login via Google** — tidak perlu daftar manual

## Tech Stack

- **Laravel 10** + **FilamentPHP v3** (TALL Stack)
- **MySQL**
- **Laravel Socialite** (Google OAuth)

## Instalasi

```bash
git clone https://github.com/akbarr13/waroong.git
cd waroong
composer install
cp .env.example .env
php artisan key:generate
```

Atur database di `.env`, lalu:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

## Setup Google OAuth

1. Buat project di [Google Cloud Console](https://console.cloud.google.com)
2. Buat **OAuth 2.0 Client ID** (Web application)
3. Tambahkan Authorized redirect URI: `http://localhost:8000/auth/google/callback`
4. Isi di `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Akses di `http://localhost:8000/admin` — login dengan Google.

## Lisensi

MIT
