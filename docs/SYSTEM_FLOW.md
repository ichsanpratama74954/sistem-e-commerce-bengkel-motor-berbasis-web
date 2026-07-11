# System Flow — E-Commerce Bengkel Motor

## 📊 Gambaran Arsitektur

```
                           ┌─────────────────────────────┐
                           │         ┌───────────┐        │
                           │         │   USER    │        │
                           │         ├───────────┤        │
                           │         │ role:     │        │
                           │         │ - admin   │        │
                           │         │ - mekanik │        │
                           │         │ - pelanggan       │
                           │         └─────┬─────┘        │
                           │               │              │
            ┌──────────────┼───────────────┼──────────────┼──────────────┐
            │              │               │              │              │
            ▼              │               ▼              │              ▼
     ┌───────────┐         │      ┌──────────────┐       │      ┌─────────────┐
     │MOTORCYCLES│         │      │  BOOKINGS    │       │      │   ORDERS    │
     ├───────────┤         │      ├──────────────┤       │      ├─────────────┤
     │- brand    │         │      │- user_id     │       │      │- user_id    │
     │- model    │◄────────┼──────┤- motorcycle  │       ├──────┤- total_amt  │
     │- plate    │         │      │- date        │       │      │- status     │
     └───────────┘         │      │- status      │       │      └──────┬──────┘
                           │      └──────┬───────┘       │             │
                           │             │                │             │
                           │      ┌──────▼───────┐       │      ┌──────▼──────┐
                           │      │BOOKING_DETAILS│       │      │ORDER_DETAILS│
                           │      ├──────────────┤       │      ├─────────────┤
                           │      │- service_id  │       │      │- sparepart  │
                           │      │- sparepart   │       │      │- qty        │
                           │      │- qty, price  │       │      │- price      │
                           │      └──────────────┘       │      └─────────────┘
                           │               │              │              │
                           ▼               ▼              ▼              ▼
                    ┌──────────────────────────────────────────────────────────┐
                    │                      PAYMENTS                           │
                    │              (polymorphic: paymentable)                  │
                    │  ┌─────────────────┬─────────────────┬────────────────┐  │
                    │  │  paymentable_id │ paymentable_type │ amount, status │  │
                    │  └─────────────────┴─────────────────┴────────────────┘  │
                    └──────────────────────────────────────────────────────────┘
```

---

## 🔄 Alur Utama

### A. Flow Servis (Booking)

```
Pelanggan ──→ Pilih Motor ──→ Pilih Jasa ──→ Pilih Sparepart (opsional)
     │                                              │
     ▼                                              ▼
  Create Booking ──→ Status: PENDING
     │
     ├── Admin/Mekanik Approve ──→ Status: APPROVED
     │                              │
     │                              ├── Lakukan Servis ──→ Selesai
     │                              │
     │                              └── Bayar ──→ Payment.create(paymentable: Booking)
     │                                                │
     │                                                ├── Success ──→ Booking selesai
     │                                                └── Failed  ──→ Booking ditunda
     │
     └── Admin/Mekanik Reject ──→ Status: REJECTED
```

**Detail:**
1. Pelanggan memilih motor miliknya
2. Pilih jasa servis yang dibutuhkan (bisa lebih dari 1)
3. Pilih sparepart yang diperlukan (opsional, bisa lebih dari 1)
4. Sistem auto-hitung subtotal per item berdasarkan harga saat itu
5. Booking tersimpan di `bookings` + `booking_details`
6. Admin/mekanik bisa approve atau reject booking
7. Setelah servis, pembayaran dibuat mengacu ke Booking ID

---

### B. Flow E-Commerce (Order)

```
Pelanggan ──→ Pilih Sparepart ──→ Tentukan Quantity ──→ Auto-hitung Total
     │
     ▼
  Create Order ──→ Status: PENDING
     │
     ├── Bayar ──→ Payment.create(paymentable: Order)
     │              │
     │              ├── Success ──→ Stok berkurang ──→ Order Selesai
     │              └── Failed  ──→ Order batal
     │
     ├── Approve ──→ Selesai (tanpa bayar di sistem)
     └── Batal  ──→ REJECTED
```

**Detail:**
1. Pelanggan memilih sparepart langsung (tanpa booking servis)
2. Quantity & subtotal otomatis dihitung
3. `total_amount` di tabel orders terisi otomatis dari jumlah items
4. Setiap OrderDetail menyimpan `unit_price` (snapshot harga) agar tidak berubah jika harga sparepart naik nanti
5. Pembayaran opsional — bisa bayar dulu atau langsung approved oleh admin
6. Jika payment success, stok sparepart berkurang

---

### C. Flow Pembayaran (Payment)

```
                    ┌─────────── PAYMENT ───────────┐
                    │                                │
         ┌──────────▼──────────┐       ┌─────────────▼──────────┐
         │  paymentable_type = │       │  paymentable_type =    │
         │  App\Models\Booking │       │  App\Models\Order      │
         └──────────┬──────────┘       └─────────────┬──────────┘
                    │                                │
                    ▼                                ▼
          Payment untuk servis              Payment untuk belanja
          (jasa + sparepart)                sparepart (e-commerce)
```

**Detail:**
- Satu Payment hanya mengacu ke SATU sumber (Booking ATAU Order) — tidak bisa keduanya
- Payment method: `Cash`, `Transfer`, `QRIS`, `DANA`
- Payment status: `Pending` → `Success` / `Failed`
- Data historis tetap aman karena unit_price di snapshot di detail tables

---

## 📋 Status Workflow

### Booking Status
```
PENDING ──→ APPROVED ──→ (selesai otomatis setelah payment success)
    │                       │
    └──→ REJECTED           └──→ REJECTED (jika batal)
```

### Order Status
```
PENDING ──→ APPROVED (Selesai)
    │           │
    └──→ REJECTED (Batal)
```

### Payment Status
```
Pending ──→ Success
    │
    └──→ Failed
```

---

## 🛠 Relasi CRUD

| Entitas | Create | Read | Update | Delete | Catatan |
|---------|--------|------|--------|--------|---------|
| User | Register | Admin | Admin/User | Admin | Role: admin/mekanik/pelanggan |
| Motorcycle | Pelanggan | Pemilik | Pemilik | Pemilik | Terkait user_id |
| Service | Admin | Semua | Admin | Admin | Harga tetap per item |
| Sparepart | Admin | Semua | Admin | Admin | Memengaruhi stok |
| Booking | Pelanggan | Semua | Admin | Admin | Menyimpan snapshot harga |
| BookingDetail | Auto | Auto | Auto | Auto | Terbuat otomatis saat booking |
| Order | Pelanggan | Semua | Admin | Admin | total_amount auto dari items |
| OrderDetail | Auto | Auto | Auto | Auto | Terbuat otomatis saat order |
| Payment | Admin | Semua | Admin | Admin | Polymorphic ke Booking/Order |
| Category | Admin | Semua | Admin | Admin | Kategori sparepart |

---

## 📌 Aturan Bisnis

1. **Snapshot Harga** — Harga jasa/sparepart di-copy ke `unit_price` di `booking_details` / `order_details` saat transaksi. Jika harga berubah nanti, transaksi lama tidak terpengaruh.

2. **Unique Booking** — Satu motor tidak bisa di-booking 2 kali pada tanggal yang sama (`unique(user_id, motorcycle_id, booking_date)`).

3. **Stok Sparepart** — Saat order sparepart dengan payment success, stok harus berkurang (perlu implementasi event/listener).

4. **Polymorphic Payment** — Satu payment hanya untuk satu sumber (Booking ATAU Order), tidak bisa keduanya.

5. **Hapus Berantai** — Jika Booking/Order dihapus, detail items ikut terhapus (cascade). Tapi Payment tetap ada (tidak cascade ke payment).
