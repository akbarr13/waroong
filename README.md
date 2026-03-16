# Waroong POS (JuraPOS)

Aplikasi Point of Sale (POS) dan Manajemen Inventaris yang dirancang khusus untuk pemilik warung kelontong (UMKM) di Indonesia. Aplikasi ini fokus pada kecepatan transaksi, kemudahan penggunaan (UX/UI), dan manajemen utang/kasbon pelanggan.

## 🚀 Tech Stack
*   **Framework:** Laravel 10.x
*   **Admin Panel / UI:** FilamentPHP v3.x (TALL Stack: TailwindCSS, Alpine.js, Laravel, Livewire)
*   **Database:** MySQL
*   **Auth:** Laravel Socialite (Google OAuth)
*   **Styling:** Tailwind CSS & Heroicons

---

## 🤖 AI Developer Context & Documentation
*This section is specifically written to provide context for AI assistants working on this codebase.*

### 1. Database Schema & Relationships
*   **`User`**: Pemilik warung. Login via Google OAuth. `hasMany(Transaction::class)`. Field `google_id` (nullable) untuk Socialite. Field `password` nullable (Google-only users tidak punya password).
*   **`Category`**: Kategori produk per user (multi-tenant). `belongsTo(User::class)`, `hasMany(Product::class)`. Global scope otomatis filter by `user_id`.
*   **`Product`**: `belongsTo(User::class)`, `belongsTo(Category::class)`, `hasMany(TransactionItem::class)`. Multi-tenant — hanya tampil milik user yang login. Tracks `stock`, `purchase_price` (modal), dan `selling_price` (jual).
*   **`Customer`**: Multi-tenant — per user. `belongsTo(User::class)`, `hasMany(Transaction::class)`. Has `totalDebt()` method returning sum of unpaid transactions.
*   **`Transaction`**: The POS Invoice. `belongsTo(User::class)`, `belongsTo(Customer::class)`. `payment_method` enum: `cash`, `qris`, `debt`. `status` enum: `paid`, `unpaid`. Has `payment_proof` (nullable string) for QRIS upload.
*   **`TransactionItem`**: `belongsTo(Transaction::class)`, `belongsTo(Product::class)`. Captures `price` at the exact time of transaction to prevent historical data changes if product prices change later.

### 2. Filament Implementation Details
*   **Auth — Google OAuth Only**:
    *   Login page hanya menampilkan tombol "Masuk dengan Google" (no email/password form).
    *   Custom Filament login page: `app/Filament/Pages/Auth/Login.php` → view `resources/views/filament/pages/auth/login.blade.php`.
    *   OAuth routes: `GET /auth/google` (redirect) + `GET /auth/google/callback` (handler via `GoogleController`).
    *   Callback: find-or-create user by `google_id` or `email`. User baru langsung dapat warung kosong.
    *   Requires `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
*   **Multi-tenancy**:
    *   Setiap user punya katalog sendiri (categories, products, customers).
    *   Global scope di `Category`, `Product`, `Customer` — otomatis filter `WHERE user_id = auth()->id()`.
    *   `creating` event otomatis set `user_id` ke user yang sedang login.
    *   Seeder menggunakan `withoutGlobalScopes()` dan assign ke user pertama.
*   **AdminPanelProvider (`app/Providers/Filament/AdminPanelProvider.php`)**:
    *   Theme color: `Color::Emerald`. Font: `Poppins`.
    *   Brand Name: "Waroong".
    *   Sidebar is set to `sidebarFullyCollapsibleOnDesktop()`.
    *   Custom `Dashboard` page at `app/Filament/Pages/Dashboard.php` with "Transaksi Baru" header action shortcut.
*   **Navigation & Sorting**:
    *   Navigation groups: `Toko` (Transactions) and `Master Data` (Categories, Products, Customers).
    *   `TransactionResource` forced to top with `navigationSort = -1`.
*   **CategoryResource**:
    *   Modal-only CRUD — no separate create/edit pages.
*   **ProductResource**:
    *   Harga Modal & Harga Jual formatted with `money('IDR')`.
    *   Stok column is a badge: merah (≤5), kuning (≤10), hijau (>10).
    *   `createOptionForm` on `category_id` to create categories on the fly.
*   **CustomerResource**:
    *   Kolom "Total Utang" via `Customer::totalDebt()`.
    *   Badge status "Lunas" / "Ada Utang".
    *   Filter "Punya Kasbon".
    *   Nomor HP adalah link `wa.me/...` (strip non-digit characters).
*   **TransactionResource (The POS Interface)**:
    *   Form section order: Keranjang → Total & Pembayaran → Informasi Transaksi (collapsed).
    *   Payment method uses `ToggleButtons`: Tunai / QRIS / Kasbon.
    *   QRIS shows `FileUpload` for payment proof (stored in `storage/payment-proofs/`).
    *   Kasbon shows `customer_id` field. Keduanya pakai `->visible()` (server-side) dengan `wire:loading` loader di section untuk feedback saat menunggu.
    *   `status` field only visible on edit (auto-set on create via `mutateFormDataBeforeCreate`).
    *   Quantity field is `->live(debounce: 500)` with server-side subtotal recalculation.
    *   All price/subtotal/total recalculation happens in `afterCreate` hook (fetches price fresh from DB).
    *   Repeater items are `->disabled()` on edit — cart is locked after transaction is saved.
    *   Table has "Lunasi" action per row (visible only for unpaid), filter kasbon aktif.
    *   Customer name in table links to customer edit page.
*   **EditTransaction**:
    *   Header action "Tandai Lunas" — visible only when status is unpaid.
    *   Header action "Cetak Struk" — opens `GET /struk/{transaction}` in new tab.
    *   Calls `refreshFormData(['status'])` after update.
*   **Barcode Scanner**:
    *   Field `barcode_scan` di form transaksi — scan langsung tambah produk ke keranjang, atau increment qty jika sudah ada.
    *   Field `sku` di form produk — scan untuk auto-isi SKU.
    *   Tombol 📷 **Kamera** di kedua field — pakai `html5-qrcode` CDN, buka kamera belakang HP.
    *   JS global `openCameraScanner(targetField)` di `AdminPanelProvider` via `renderHook('panels::body.end')`.
    *   Butuh **HTTPS** untuk akses kamera di production.
*   **Struk (`resources/views/struk.blade.php`)**:
    *   Route: `GET /struk/{transaction}`, dilindungi middleware `auth`, nama route `struk`.
    *   Format thermal printer 58mm, auto `window.print()` saat halaman load.
    *   Menampilkan: header toko, info transaksi, daftar item, total, metode pembayaran, status kasbon.
    *   Tombol "Cetak Struk" & "Tutup" tidak ikut tercetak (`no-print` class).
    *   Akses dari: tabel transaksi (kolom actions) dan header halaman edit transaksi.
*   **Widgets (`app/Filament/Widgets/`)**:
    *   `StatsOverviewWidget` — Omset hari ini, Estimasi Keuntungan, Kasbon Aktif, Jumlah Transaksi.
    *   `TopProductsWidget` — 5 produk terlaris (by qty), uses raw query + `->with('product.category')`.
    *   `LowStockWidget` — produk dengan stok ≤ 10.

### 3. Critical Business Logic
*   **Stock decrement**: happens in `afterCreate` on `CreateTransaction`, after price/subtotal are recalculated.
*   **Price integrity**: `afterCreate` always fetches `selling_price` fresh from DB — form state is not trusted for price.
*   **Status auto-set**: `mutateFormDataBeforeCreate` sets `status` from `payment_method` (debt → unpaid, else → paid).
*   **Kasbon pelunasan**: via "Lunasi" table action or "Tandai Lunas" header action in EditTransaction.

---

## ✅ To-Do List (Roadmap)

### Fase 1: Setup & Master Data ✅
- [x] Inisialisasi Project Laravel & Filament v3.
- [x] Setup Database Schema (Categories, Products, Customers, Transactions).
- [x] UX Tweak: Sidebar, Navigation Groups, Icons, and Colors.
- [x] UX Tweak: Form Kategori via Modal Pop-up.
- [x] UX Tweak: Form Produk dengan layout Grid & Quick Create Category.

### Fase 2: Core Kasir & Transaksi ✅
- [x] Form Kasir dengan auto-kalkulasi harga & subtotal (Reactive Repeater).
- [x] Tabel Riwayat Transaksi dengan Badge Status & formatting IDR.
- [x] Metode pembayaran: Tunai, QRIS (dengan upload bukti), Kasbon.
- [x] UX kasir: keranjang di atas, payment di bawah, invoice section collapsed.
- [x] Logika pengurangan stok otomatis via `afterCreate`.
- [x] Validasi stok via `beforeCreate`.
- [x] Items terkunci (disabled) di halaman edit transaksi.

### Fase 3: Kasbon & Manajemen Utang ✅
- [x] CustomerResource: kolom total utang, badge status, filter punya kasbon, link WA.
- [x] TransactionResource: filter kasbon aktif, action "Lunasi" per baris.
- [x] EditTransaction: tombol "Tandai Lunas" di header.

### Fase 4: Dashboard & Reporting ✅
- [x] Widget Omset Hari Ini & Estimasi Keuntungan.
- [x] Widget Kasbon Aktif & Jumlah Transaksi Hari Ini.
- [x] Tabel Barang Terlaris (Top 5).
- [x] Tabel Stok Menipis (stok ≤ 10).
- [x] Shortcut "Transaksi Baru" di header dashboard.

### Fase 5: Ekstra / Polish ✅
- [x] Cetak Struk — Blade view format thermal 58mm, auto print, route `GET /struk/{transaction}`.
- [x] Dukungan scan Barcode — field scan di form transaksi & produk, support scanner fisik (keyboard) dan kamera HP (via `html5-qrcode` CDN).
- [x] Mobile Optimization — form responsive grid, tabel sembunyikan kolom sekunder di layar kecil.

### Fase 6: Auth & Multi-Tenancy ✅
- [x] Login via Google OAuth (Laravel Socialite) — halaman login hanya tampilkan tombol Google.
- [x] User baru yang daftar via Google langsung dapat warung kosong.
- [x] Multi-tenancy: setiap user punya katalog sendiri (kategori, produk, pelanggan scoped by `user_id`).
- [x] Global scope otomatis di model `Category`, `Product`, `Customer`.
- [x] Database seeder untuk data awal (user admin, kategori, produk, pelanggan).

---

## 🛠️ Cara Menjalankan Project Lokal

1. Clone repositori ini.
2. Jalankan instalasi dependensi:
   ```bash
   composer install
   npm install
   ```
3. Copy file `.env.example` menjadi `.env` dan atur konfigurasi database MySQL kamu.
4. Generate App Key:
   ```bash
   php artisan key:generate
   ```
5. Buat Google OAuth credentials di [Google Cloud Console](https://console.cloud.google.com):
   - Buat OAuth 2.0 Client ID (Web application).
   - Tambahkan Authorized redirect URI: `http://localhost:8000/auth/google/callback`.
   - Isi di `.env`:
     ```
     GOOGLE_CLIENT_ID=your-client-id
     GOOGLE_CLIENT_SECRET=your-client-secret
     GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
     ```
6. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
7. Buat storage symlink:
   ```bash
   php artisan storage:link
   ```
8. Jalankan local server:
   ```bash
   php artisan serve
   ```
9. Akses dashboard di `http://localhost:8000/admin` — login via Google.
