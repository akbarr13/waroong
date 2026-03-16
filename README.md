# Waroong POS (JuraPOS)

Aplikasi Point of Sale (POS) dan Manajemen Inventaris yang dirancang khusus untuk pemilik warung kelontong (UMKM) di Indonesia. Aplikasi ini fokus pada kecepatan transaksi, kemudahan penggunaan (UX/UI), dan manajemen utang/kasbon pelanggan.

## 🚀 Tech Stack
*   **Framework:** Laravel 10.x
*   **Admin Panel / UI:** FilamentPHP v3.x (TALL Stack: TailwindCSS, Alpine.js, Laravel, Livewire)
*   **Database:** MySQL
*   **Styling:** Tailwind CSS & Heroicons

---

## 🤖 AI Developer Context & Documentation
*This section is specifically written to provide context for AI assistants working on this codebase.*

### 1. Database Schema & Relationships
*   **`User`**: Cashier/Admin. `hasMany(Transaction::class)`
*   **`Category`**: Product categories. `hasMany(Product::class)`
*   **`Product`**: `belongsTo(Category::class)`, `hasMany(TransactionItem::class)`. Tracks `stock`, `purchase_price` (modal), and `selling_price` (jual).
*   **`Customer`**: Target for the "Kasbon/Debt" feature. `hasMany(Transaction::class)`.
*   **`Transaction`**: The POS Invoice. `belongsTo(User::class)`, `belongsTo(Customer::class)`. Has enums/strings for `payment_method` (cash/debt) and `status` (paid/unpaid).
*   **`TransactionItem`**: `belongsTo(Transaction::class)`, `belongsTo(Product::class)`. Captures `price` at the exact time of transaction to prevent historical data changes if product prices change later.

### 2. Filament Implementation Details
*   **AdminPanelProvider (`app/Providers/Filament/AdminPanelProvider.php`)**:
    *   Theme color: `Color::Emerald`. Font: `Poppins`.
    *   Brand Name: "Waroong POS".
    *   Sidebar is set to `sidebarFullyCollapsibleOnDesktop()`.
*   **Navigation & Sorting**:
    *   Navigation groups are used: `Toko` (Transactions) and `Master Data` (Categories, Products, Customers).
    *   We use integer manipulation for sorting (`protected static ?int $navigationSort`). `TransactionResource` is forced to the top using `-1`.
*   **CategoryResource**:
    *   Converted to a **Modal-only** CRUD (Single Page Application feel).
    *   Deleted `CreateCategory` and `EditCategory` pages. Modals are triggered directly from `ListCategories` and the Table actions.
*   **ProductResource**:
    *   Uses `Section` and `Grid` (columns) for better UX.
    *   Includes a `createOptionForm` on the `category_id` Select field, allowing users to create new categories on the fly without leaving the product creation page.
*   **TransactionResource (The POS Interface)**:
    *   Uses a Livewire `Repeater` for the shopping cart (`items` relationship).
    *   Heavily utilizes Filament's **Reactive** features: `->reactive()` and `->afterStateUpdated()`.
    *   When a product is selected, it automatically fetches the `selling_price` and calculates the `subtotal`.
    *   The `total_amount` is automatically summed up in real-time from the repeater items.

---

## ✅ To-Do List (Roadmap)

### Fase 1: Setup & Master Data (Selesai)
- [x] Inisialisasi Project Laravel & Filament v3.
- [x] Setup Database Schema (Categories, Products, Customers, Transactions).
- [x] UX Tweak: Sidebar, Navigation Groups, Icons, and Colors.
- [x] UX Tweak: Form Kategori via Modal Pop-up.
- [x] UX Tweak: Form Produk dengan layout Grid & Quick Create Category.

### Fase 2: Core Kasir & Transaksi (Berjalan)
- [x] Buat Form Kasir dengan auto-kalkulasi harga & subtotal (Reactive Repeater).
- [x] Buat Tabel Riwayat Transaksi dengan Badge Status.
- [ ] **TUGAS SELANJUTNYA:** Logika pengurangan stok (`stock`) di `Product` otomatis saat Transaksi berhasil disimpan (Bisa menggunakan Eloquent Observer pada model `Transaction` atau Filament `afterCreate` hook).
- [ ] Validasi stok (Tidak bisa checkout jika stok barang < jumlah beli).

### Fase 3: Kasbon & Manajemen Utang
- [ ] Buat Widget/Halaman khusus untuk melihat daftar pelanggan yang memiliki `status = 'unpaid'` atau `payment_method = 'debt'`.
- [ ] Fitur Cicilan/Pelunasan Utang (Mengubah status transaksi dari unpaid ke paid, dan mencatat riwayat cicilan).

### Fase 4: Dashboard & Reporting
- [ ] Tampilkan Widget Total Pendapatan Hari Ini (Omset).
- [ ] Tampilkan Widget Estimasi Keuntungan (Profit) berdasarkan selisih `selling_price` dan `purchase_price`.
- [ ] Tabel "Barang Terlaris" atau "Stok Menipis".

### Fase 5: Ekstra / Polish
- [ ] Cetak Struk (PDF / Thermal Printer Web API).
- [ ] Dukungan scan Barcode (otomatis input ke keranjang jika SKU terdeteksi).

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
5. Jalankan migrasi:
   ```bash
   php artisan migrate
   ```
6. (Opsional) Buat user admin baru jika belum ada:
   ```bash
   php artisan make:filament-user
   ```
7. Jalankan local server:
   ```bash
   php artisan serve
   ```
8. Akses dashboard di `http://localhost:8000/admin`.