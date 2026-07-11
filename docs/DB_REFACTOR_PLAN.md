# Database Refactor Plan

## 📋 Ringkasan

Refactor database untuk sistem e-commerce bengkel motor. Memisahkan Booking (servis) dan Order (e-commerce) dengan jelas, menambahkan tabel pivot, dan memperbaiki desain Payments.

---

## 🔄 Perubahan

### 1. Tabel Baru: `booking_details`

```php
Schema::create('booking_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
    $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('sparepart_id')->nullable()->constrained('spareparts')->nullOnDelete();
    $table->integer('quantity')->default(1);
    $table->decimal('unit_price', 12, 2);
    $table->decimal('subtotal', 12, 2);
    $table->timestamps();
});
```

### 2. Tabel Baru: `order_details`

```php
Schema::create('order_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sparepart_id')->constrained('spareparts')->cascadeOnDelete();
    $table->integer('quantity')->default(1);
    $table->decimal('unit_price', 12, 2);
    $table->decimal('subtotal', 12, 2);
    $table->timestamps();
});
```

### 3. Migrasi Payments → Polymorphic

Ubah dari:
```php
$table->foreignId('booking_id')->nullable()->constrained('bookings');
$table->foreignId('order_id')->nullable()->constrained('orders');
```

Menjadi:
```php
$table->morphs('paymentable'); // paymentable_id + paymentable_type
$table->dropForeign(['booking_id']);
$table->dropForeign(['order_id']);
$table->dropColumn(['booking_id', 'order_id']);
```

### 4. Tambah `slug` ke Categories

```php
$table->string('slug')->unique()->after('name');
```

### 5. Unique Constraint di Bookings

```php
$table->unique(['user_id', 'motorcycle_id', 'booking_date']);
```

### 6. Ubah Tipe `total_amount` di Orders

```php
$table->decimal('total_amount', 12, 2)->default(0)->change();
```

### 7. Model Baru & Relasi

| Model | Method | Relasi |
|-------|--------|--------|
| `BookingDetail` | `booking()` | `belongsTo(Booking::class)` |
| `BookingDetail` | `service()` | `belongsTo(Service::class)` |
| `BookingDetail` | `sparepart()` | `belongsTo(Sparepart::class)` |
| `OrderDetail` | `order()` | `belongsTo(Order::class)` |
| `OrderDetail` | `sparepart()` | `belongsTo(Sparepart::class)` |
| `Booking` | `bookingDetails()` | `hasMany(BookingDetail::class)` |
| `Order` | `orderDetails()` | `hasMany(OrderDetail::class)` |
| `Service` | `bookingDetails()` | `hasMany(BookingDetail::class)` |
| `Sparepart` | `bookingDetails()` | `hasMany(BookingDetail::class)` |
| `Sparepart` | `orderDetails()` | `hasMany(OrderDetail::class)` |
| `Payment` | `paymentable()` | `morphTo()` |

---

## ✅ Status Implementasi

### Sudah Selesai (Batch 1 — Database Layer)

- [x] 1. Migration `create_booking_details_table`
- [x] 2. Migration `create_order_details_table`
- [x] 3. Migration `add_slug_to_categories_table`
- [x] 4. Migration `add_unique_constraint_to_bookings_table`
- [x] 5. Migration `change_total_amount_type_in_orders_table`
- [x] 6. Migration `convert_payments_to_polymorphic`
- [x] 7. Model `BookingDetail` (baru)
- [x] 8. Model `OrderDetail` (baru)
- [x] 9. Update model `Booking` — tambah `bookingDetails()`
- [x] 10. Update model `Order` — tambah `orderDetails()`
- [x] 11. Update model `Service` — tambah `bookingDetails()`
- [x] 12. Update model `Sparepart` — tambah `bookingDetails()` & `orderDetails()`
- [x] 13. Update model `Payment` — morphTo
- [x] 14. Update model `Category` — tambah `slug` fillable
- [x] 15. `php artisan migrate` ✅ sukses

### Batch 2 — View Layer ✅ Selesai

- [x] 16. Update Booking create blade — form untuk pilih jasa & sparepart
- [x] 17. Update Booking edit blade — form untuk pilih jasa & sparepart
- [x] 18. Update Booking index — tampilkan total & items dari booking_details
- [x] 19. Update OrdersForm + index — sparepart multi-select + auto-calculate total
- [x] 20. Update Order index — tampilkan items dari order_details
- [x] 21. Update PaymentForm — polymorphic (paymentable_id + paymentable_type)
- [x] 22. Update Payment create/edit — dropdown source type + source ID
- [x] 23. Update Payment index — tampilkan polymorphic source

---

## 📌 Yang Harus Dilakukan Setelah Update

### 1. Backup Database
```bash
php artisan db:dump  # atau backup manual via phpMyAdmin/mysqldump
```

### 2. Jalankan Migration
```bash
php artisan migrate
```

### 3. Seed Data (jika ada)
```bash
php artisan db:seed --class=BookingDetailSeeder  # jika ada
```

### 4. Verifikasi Struktur
```bash
php artisan db:show  # atau cek via tools database
```

### 5. Test Manual
- Buat booking baru dengan jasa & sparepart
- Buat order baru (e-commerce murni)
- Bayar booking → cek payment ter-record
- Bayar order → cek payment ter-record
- Cek unique constraint booking_date duplicate

### 6. Update View & Controller
- Booking create/edit → tambah form untuk pilih jasa & sparepart
- Booking index → tampilkan total biaya dari booking_details
- Order create/edit → tambah form untuk pilih sparepart
- Order index → tampilkan items dari order_details
- Payment → sesuaikan dengan polymorphic

### 7. Hapus Migration Lama (jika sudah stabilize)
Setelah semua berjalan lancar, hapus migration `create_payments_table.php` lama
dan buat ulang dengan struktur polymorphic yang clean.

---

## 🧪 Rollback Plan

Jika terjadi error saat migrate:

```bash
# Rollback semua migration baru
php artisan migrate:rollback --step=6

# Atau rollback spesifik
php artisan migrate:rollback --path=database/migrations/2026_07_xx_xxxxxx_create_booking_details_table.php
```
