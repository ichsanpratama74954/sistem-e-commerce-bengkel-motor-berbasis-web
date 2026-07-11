# Sistem E-Commerce Bengkel Motor

Sistem manajemen bengkel motor berbasis web dengan fitur booking servis, e-commerce sparepart, dan manajemen pembayaran. Dibangun dengan Laravel + Livewire + Flux UI.

---

## Daftar Isi

- [Arsitektur Sistem](#arsitektur-sistem)
- [Struktur Database](#struktur-database)
- [Flow Bisnis](#flow-bisnis)
- [Role & Hak Akses](#role--hak-akses)
- [Panduan Penggunaan](#panduan-penggunaan)
- [API Endpoints](#api-endpoints)
- [Pengembangan](#pengembangan)

---

## Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────┐
│                    Laravel Framework                         │
│  ┌────────────┐  ┌──────────────┐  ┌───────────────────┐   │
│  │  Livewire  │  │   Eloquent   │  │   Flux UI         │   │
│  │ Components │──│    Models    │──│   Components      │   │
│  └────────────┘  └──────────────┘  └───────────────────┘   │
│                        │                                     │
│                        ▼                                     │
│              ┌─────────────────┐                             │
│              │    MySQL DB     │                             │
│              └─────────────────┘                             │
└──────────────────────────────────────────────────────────────┘
```

### Stack Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 11 / PHP 8.2+ |
| Frontend | Livewire v3, Flux UI, Tailwind CSS |
| Database | MySQL 8+ (MariaDB 11+) |
| Auth | Laravel Breeze + Two Factor |
| Auth (lanjutan) | Passkeys (FIDO2/WebAuthn) |

---

## Struktur Database

### Entity Relationship Diagram

```
USERS ──┬── MOTORCYCLES      SERVICES
  │     │                      │
  │     ├── BOOKINGS ──────┬── BOOKING_DETAILS
  │     │                  │     ├── service_id (nullable)
  │     │                  │     └── sparepart_id (nullable)
  │     │                  │
  │     ├── ORDERS ────────┬── ORDER_DETAILS
  │     │                        └── sparepart_id
  │     │
  │     └── PAYMENTS (polymorphic)
  │              ├── paymentable_type: Booking|Order
  │              └── paymentable_id
  │
CATEGORIES ──── SPAREPARTS ──────┬── ORDER_DETAILS
                                  └── BOOKING_DETAILS (nullable)
```

### Tabel & Deskripsi

| Tabel | Fungsi | Key Columns |
|-------|--------|-------------|
| `users` | Pengguna sistem | `id`, `name`, `email`, `role` (enum: admin/mekanik/pelanggan) |
| `motorcycles` | Kendaraan pelanggan | `id`, `user_id`, `brand`, `model`, `plate_number` |
| `bookings` | Janji servis | `id`, `user_id`, `motorcycle_id`, `booking_date`, `status` |
| `booking_details` | Item servis (jasa/sparepart) | `booking_id`, `service_id` (nullable), `sparepart_id` (nullable), `quantity`, `unit_price`, `subtotal` |
| `orders` | Pesanan sparepart (e-commerce) | `id`, `user_id`, `total_amount`, `status` |
| `order_details` | Item pesanan | `order_id`, `sparepart_id`, `quantity`, `unit_price`, `subtotal` |
| `services` | Daftar jasa servis | `id`, `service_name`, `description`, `service_price` |
| `spareparts` | Daftar sparepart | `id`, `category_id`, `part_name`, `price`, `stock` |
| `categories` | Kategori sparepart | `id`, `name`, `slug`, `description` |
| `payments` | Pembayaran (polymorphic) | `id`, `paymentable_id`, `paymentable_type`, `amount`, `payment_method`, `payment_status` |

### Aturan Database

1. **Unique Booking** — Satu user+motorcycle hanya bisa booking 1x di tanggal yang sama (`unique(user_id, motorcycle_id, booking_date)`)
2. **Snapshot Harga** — `unit_price` di booking_details/order_details menyimpan harga saat transaksi, tidak terpengaruh perubahan harga di master
3. **Polymorphic Payment** — Payment bisa mengacu ke Booking ATAU Order (tidak keduanya)
4. **Slug Auto-generate** — Kategori slug dibuat otomatis dari nama via `Str::slug()`

---

## Flow Bisnis

### 1. Flow Servis (Booking)

```
Pelanggan ──→ Pilih Motor ──→ Pilih Jasa ──→ Pilih Sparepart ──→ Create Booking
                                                                        │
                                                                   PENDING
                                                                     │
                                         ┌───────────────────────────┼───────────────────────────┐
                                         ▼                           ▼                           ▼
                                    APPROVED                     REJECTED                  (diubah manual)
                                         │
                                    Servis selesai
                                         │
                                    Klik "Bayar"
                                         │
                                         ▼
                                    Modal Payment (auto-fill amount dari booking_details)
                                         │
                                    Pilih metode → Create
                                         │
                                         ▼
                                    Payment Success / Failed
```

**Cara pakai:**
1. Buka menu **Bookings** → **Add Booking**
2. Pilih User, Motorcycle, Booking Date
3. Tambah Services (pilih jasa + quantity → Add)
4. Tambah Spareparts (opsional, pilih sparepart + quantity → Add)
5. Set Status → Create
6. Setelah servis selesai, klik **⋮ → Bayar** untuk mencatat pembayaran

### 2. Flow E-Commerce (Order)

```
Pelanggan ──→ Pilih Sparepart ──→ Quantity ──→ Auto-hitung Total ──→ Create Order
                                                                          │
                                                                     PENDING
                                                                       │
                                              ┌────────────────────────┼────────────────────┐
                                              ▼                        ▼                    ▼
                                         APPROVED                  REJECTED           Klik "Bayar"
                                          (Selesai)               (Batal)                 │
                                                                                          ▼
                                                                                    Modal Payment
                                                                                    (auto-fill amount)
```

**Cara pakai:**
1. Buka menu **Orders** → **Add Order**
2. Pilih User
3. Tambah Spareparts (pilih sparepart + quantity → Add, total otomatis)
4. Set Status → Create
5. Klik **⋮ → Bayar** untuk pembayaran

### 3. Flow Pembayaran (Payment)

```
Payment (polymorphic)
    │
    ├── paymentable_type = App\Models\Booking  → Pembayaran servis
    └── paymentable_type = App\Models\Order    → Pembayaran belanja sparepart
```

**Status Payment:**
```
Pending ──→ Success
    │
    └── Failed
```

**Metode Pembayaran:**
- Cash / Tunai
- Bank Transfer
- QRIS / E-Wallet
- DANA

**Cara pakai (manual):**
1. Buka **Financial Payments** → **Add New Payment**
2. Pilih Source Type (Booking/Order)
3. Pilih Source ID dari dropdown
4. Masukkan Amount (atau auto-fill dari tombol Bayar di Booking/Order)
5. Pilih Method & Status → Create

---

## Role & Hak Akses

| Role | Deskripsi |
|------|-----------|
| `admin` | Akses penuh ke semua fitur |
| `mekanik` | Melihat/mengelola booking, jasa, sparepart |
| `pelanggan` | Melihat data sendiri, membuat booking |

### Matriks Akses

| Fitur | Admin | Mekanik | Pelanggan |
|-------|-------|---------|-----------|
| Kelola User | ✅ | ❌ | ❌ |
| Booking (CRUD) | ✅ | ✅ | ✅ (milik sendiri) |
| Order (CRUD) | ✅ | ❌ | ❌ |
| Service (CRUD) | ✅ | ✅ | ❌ |
| Sparepart (CRUD) | ✅ | ✅ | ❌ |
| Payment (CRUD) | ✅ | ❌ | ❌ |
| Category (CRUD) | ✅ | ❌ | ❌ |

---

## Panduan Penggunaan

### Halaman Utama

| URL | Halaman | Deskripsi |
|-----|---------|-----------|
| `/dashboard` | Dashboard | Overview statistik |
| `/bookings` | Bookings | Manajemen booking servis |
| `/orders` | Orders | Manajemen pesanan sparepart |
| `/services` | Services | Daftar jasa servis |
| `/spareparts` | Spareparts | Daftar sparepart & stok |
| `/categories` | Categories | Kategori sparepart |
| `/payments` | Payments | Riwayat pembayaran |
| `/motorcycles` | Motorcycles | Data kendaraan |

### Komponen Livewire

| Komponen | Lokasi |
|----------|--------|
| Booking Create | `resources/views/components/booking/⚡create.blade.php` |
| Booking Edit | `resources/views/components/booking/⚡edit.blade.php` |
| Order Create | `resources/views/components/orders/⚡create.blade.php` |
| Order Edit | `resources/views/components/orders/⚡edit.blade.php` |
| Payment Create | `resources/views/components/payment/⚡create.blade.php` |
| Payment Edit | `resources/views/components/payment/⚡edit.blade.php` |

### Form Objects (Livewire)

| Form | File |
|------|------|
| `BookingForm` | `app/Livewire/Forms/BookingForm.php` |
| `OrdersForm` | `app/Livewire/Forms/OrdersForm.php` |
| `PaymentForm` | `app/Livewire/Forms/PaymentForm.php` |
| `CategoryForm` | `app/Livewire/Forms/CategoryForm.php` |

---

## Route List

| Method | URI | Name | Component |
|--------|-----|------|-----------|
| GET | `/bookings` | `booking.index` | `pages::booking.index` |
| GET | `/orders` | `order.index` | `pages::orders.index` |
| GET | `/services` | `service.index` | `pages::service.index` |
| GET | `/spareparts` | `sparepart.index` | `pages::sparepart.index` |
| GET | `/categories` | `category.index` | `pages::category.index` |
| GET | `/payments` | `payment.index` | `pages::payment.index` |
| GET | `/motorcycles` | `motorcycle.index` | `pages::motorcycle.index` |

Semua route dilindungi middleware `auth` dan `verified`.

---

## Pengembangan

### Command Berguna

```bash
# Migrasi database
php artisan migrate

# Rollback migrasi terakhir
php artisan migrate:rollback --step=1

# Refresh migrasi (hapus + migrate ulang)
php artisan migrate:fresh --seed

# Clear cache
php artisan optimize:clear

# Lihat route
php artisan route:list

# Lihat status migrasi
php artisan migrate:status

# Cek database
php artisan db:show
php artisan db:table bookings
```

### Struktur Direktori

```
app/
├── Livewire/
│   └── Forms/
│       ├── BookingForm.php
│       ├── OrdersForm.php
│       ├── PaymentForm.php
│       └── CategoryForm.php
└── Models/
    ├── Booking.php
    ├── BookingDetail.php
    ├── Order.php
    ├── OrderDetail.php
    ├── Payment.php
    ├── Service.php
    ├── Sparepart.php
    ├── Category.php
    ├── Motorcycle.php
    └── User.php

database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 2026_06_21_115003_create_categories_table.php
├── 2026_06_21_223253_create_services_table.php
├── 2026_06_25_121304_create_motorcycles_table.php
├── 2026_06_27_033448_create_orders_table.php
├── 2026_07_02_130408_create_bookings_table.php
├── 2026_07_03_105325_create_spareparts_table.php
├── 2026_07_03_203106_create_payments_table.php
├── 2026_07_11_035849_create_booking_details_table.php
├── 2026_07_11_035856_create_order_details_table.php
├── 2026_07_11_035903_add_slug_to_categories_table.php
├── 2026_07_11_035905_add_unique_constraint_to_bookings_table.php
├── 2026_07_11_035906_change_total_amount_type_in_orders_table.php
└── 2026_07_11_035906_convert_payments_to_polymorphic_table.php

resources/views/
├── components/
│   ├── booking/       # Booking CRUD modals
│   ├── orders/        # Orders CRUD modals
│   ├── payment/       # Payment CRUD modals
│   ├── category/      # Category CRUD modals
│   ├── service/       # Service CRUD modals
│   ├── sparepart/     # Sparepart CRUD modals
│   ├── motorcycle/    # Motorcycle CRUD modals
│   └── payment/       # Payment CRUD modals
└── pages/
    ├── booking/       # Booking index (full page)
    ├── orders/        # Orders index (full page)
    ├── payment/       # Payment index (full page)
    ├── category/      # Category index (full page)
    ├── service/       # Service index (full page)
    ├── sparepart/     # Sparepart index (full page)
    └── motorcycle/    # Motorcycle index (full page)
```

---

## Lisensi

Hak Cipta © 2026. Project mahasiswa.
