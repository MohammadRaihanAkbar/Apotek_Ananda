# PRODUCT REQUIREMENT DOCUMENT (PRD) — REVISI v3.0

## Sistem Informasi Manajemen Stok Obat — Apotek Ananda Jadimulya

**Versi:** 3.0 (Revisi Besar)
**Tanggal:** 3 Mei 2026
**Disusun oleh:** Tim Pengembang

---

## Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0–2.9 | s.d. 18 April 2026 | Lihat PRD_Apotek_Ananda_Jadimulya_Revisi.md |
| 3.0 | 3 Mei 2026 | **Revisi Besar:** (1) PBF jadi menu sidebar sendiri, Super Admin only. (2) Manajemen Stok berbasis Faktur (invoice-based) dengan batch & exp per item. (3) Piutang otomatis dari faktur + pelunasan upload bukti. (4) Laporan Expired otomatis saja (hapus manual). (5) Log Aktivitas mencatat SEMUA aksi. (6) Dashboard ringkasan + tombol Selengkapnya. |

---

## 1. Tujuan Aplikasi

Sistem Informasi Manajemen Stok Obat ini dikembangkan untuk membantu **Apotek Ananda Jadimulya** dalam mengelola persediaan obat secara efektif dan efisien, menggantikan proses pencatatan manual menjadi sistem terkomputerisasi.

**Tujuan utama:**
1. Mempermudah pencatatan stok obat masuk dari PBF per **faktur (invoice)**.
2. Mempermudah pengelolaan data PBF sebagai mitra pemasok obat.
3. Mempermudah pemantauan obat mendekati kedaluwarsa secara **otomatis** (≤ 6 bulan).
4. Mempermudah pencatatan & pelunasan piutang ke PBF berbasis faktur.
5. Meningkatkan efisiensi & keamanan melalui pencatatan log aktivitas lengkap.

---

## 2. Target User

### 2.1 Super Admin (Apoteker) — Akses Penuh

| No | Hak Akses | Keterangan |
|----|-----------|------------|
| 1 | Dashboard | Ringkasan data apotek + tombol selengkapnya |
| 2 | Manajemen PBF | Kelola data PBF (menu sidebar sendiri) |
| 3 | Manajemen Stok | Kelola faktur & obat (invoice-based) |
| 4 | Piutang | Lihat & lunasi faktur, upload bukti bayar |
| 5 | Laporan Expired | Lihat laporan otomatis obat mendekati expired |
| 6 | Kelola Akun Admin | CRUD akun admin |
| 7 | Log Aktivitas | Lihat seluruh riwayat aktivitas |

### 2.2 Admin (Asisten Apoteker) — Akses Terbatas

| No | Hak Akses | Keterangan |
|----|-----------|------------|
| 1 | Dashboard | Ringkasan data (terbatas) + tombol selengkapnya |
| 2 | Manajemen Stok | Kelola faktur & obat (tanpa hapus) |
| 3 | Log Aktivitas | Lihat riwayat aktivitas |

> **CATATAN:** Admin **TIDAK** memiliki akses ke PBF, Piutang, Laporan Expired, dan Kelola Akun.

---

## 3. Alur Kerja Sistem

### 3.1 Manajemen PBF (Super Admin Only)

Menu sidebar terpisah untuk mengelola data PBF (Pedagang Besar Farmasi) sebagai mitra pemasok obat.

```
Start → Login Super Admin → Buka Menu "PBF"
  → Sistem tampilkan tabel daftar PBF
  → Aksi: Tambah PBF Baru / Edit PBF / Hapus PBF / Lihat Detail PBF
  → Data tersimpan
  → End
```

**Informasi PBF:**
- Nama PBF
- Alamat
- No. Telepon
- Kontak Person
- Keterangan/Catatan

---

### 3.2 Input Stok Obat — Berbasis Faktur (Invoice-Based)

Perubahan besar: obat sekarang **dibungkus dalam 1 faktur**. Alur: masuk ke Manajemen Stok → klik "Tambah Faktur" → diarahkan ke **screen baru** (bukan popup).

```
Start
  │
  ▼
Login Admin/Super Admin
  │
  ▼
Buka Menu "Manajemen Stok"
  │
  ▼
Sistem menampilkan tabel daftar Faktur:
┌─────────────────────────────────────────────────────────┐
│ No │ No Faktur │ PBF │ Tgl Faktur │ Tgl Masuk │       │
│    │           │     │            │ Barang    │       │
│    │ Jml Obat │ Total Qty │ Total Harga │ Jatuh Tempo │
│    │ [Detail] │           │             │             │
└─────────────────────────────────────────────────────────┘
  │
  ▼
Klik "+ Tambah Faktur"
  │
  ▼
Diarahkan ke SCREEN BARU (satu halaman form):
  │
  ▼
═══ BAGIAN 1: Data Faktur ═══
Isi kolom:
- No Faktur (text)
- PBF (dropdown dari tabel PBF)
- Tanggal Faktur (date)
- Tanggal Masuk Barang (date)
- Tanggal Jatuh Tempo (date)
  │
  ▼
═══ BAGIAN 2: Data Obat ═══
Tambah obat satu per satu ke dalam faktur ini:
- Nama Obat
- Jenis Obat
- Satuan
- Harga Beli
- Discount
- Jumlah (qty)
  │
  ▼
Saat input qty (misal: Paracetamol qty=3),
muncul POPUP untuk input per-item:
┌──────────────────────────────┐
│ Paracetamol - Item 1 dari 3 │
│ No Batch: [________]        │
│ Exp Date: [________]        │
│                    [Next →] │
├──────────────────────────────┤
│ Paracetamol - Item 2 dari 3 │
│ No Batch: [________]        │
│ Exp Date: [________]        │
│                    [Next →] │
├──────────────────────────────┤
│ Paracetamol - Item 3 dari 3 │
│ No Batch: [________]        │
│ Exp Date: [________]        │
│                    [Selesai]│
└──────────────────────────────┘
  │
  ▼
Sistem hitung otomatis:
- Total Qty Obat (semua obat dalam faktur)
- Total Harga Faktur
  │
  ▼
Klik "Simpan Faktur"
  │
  ▼
Data faktur + obat tersimpan → kembali ke tabel faktur
  │
  ▼
End
```

**Saat klik "Detail" pada faktur:**
Menampilkan info faktur + tabel obat-obat di dalam faktur tersebut lengkap dengan batch & exp date.

---

### 3.3 Piutang — Otomatis dari Faktur

Tabel piutang **otomatis terisi dari data faktur** di Manajemen Stok. Tidak perlu input manual. Fungsi menu piutang: **melunasi faktur**.

```
Start
  │
  ▼
Login Super Admin
  │
  ▼
Buka Menu "Piutang"
  │
  ▼
Sistem menampilkan tabel faktur (otomatis dari Manajemen Stok):
┌──────────────────────────────────────────────────────────┐
│ No │ No Faktur │ PBF │ Tgl Faktur │ Total Harga │      │
│    │ Jatuh Tempo │ Status │ Aksi                        │
│    │             │ [Belum Lunas / Lunas]                 │
└──────────────────────────────────────────────────────────┘
  │
  ▼
Untuk melunasi: klik tombol "Lunasi"
  │
  ▼
Upload foto bukti pembayaran → Simpan
  │
  ▼
Status otomatis berubah → "Lunas"
Tanggal lunas otomatis tercatat
  │
  ▼
Bisa lihat foto bukti (ada tombol "Back" untuk kembali)
  │
  ▼
End
```

---

### 3.4 Laporan Expired — Otomatis

Sepenuhnya **otomatis**. Tidak ada input manual. Sistem menyortir obat yang expired dalam **6 bulan ke depan** dari data stok masuk (berdasarkan exp_date per batch).

```
Start → Login Super Admin → Buka "Laporan Expired"
  → Sistem otomatis query obat dengan exp_date ≤ 6 bulan dari hari ini
  → Tampil tabel + Filter (PBF, rentang tanggal, nama obat)
  → End
```

---

## 4. Feature MVP

### 4.1 Login System
| Komponen | Deskripsi |
|----------|-----------|
| Login username | Input username & password |
| Autentikasi | Validasi kredensial |
| Pembagian role | Super Admin vs Admin |
| Session management | Kelola sesi login |

### 4.2 Dashboard (Ringkasan + Selengkapnya)

**Dashboard Super Admin:**

| No | Widget | Keterangan |
|----|--------|------------|
| 1 | Jumlah Stok | Total unit obat + [Selengkapnya] → ke Manajemen Stok |
| 2 | Jumlah Faktur | Total faktur aktif + [Selengkapnya] → ke Manajemen Stok |
| 3 | Piutang Belum Lunas | Jumlah & total nominal + [Selengkapnya] → ke Piutang |
| 4 | Obat Mendekati Expired | Jumlah obat ≤ 6 bulan + [Selengkapnya] → ke Laporan Expired |
| 5 | Log Aktivitas Terbaru | 5 aktivitas terakhir + [Selengkapnya] → ke Log Aktivitas |

**Dashboard Admin:**

| No | Widget | Keterangan |
|----|--------|------------|
| 1 | Jumlah Stok | Total unit obat + [Selengkapnya] → ke Manajemen Stok |
| 2 | Log Aktivitas Terbaru | 5 aktivitas terakhir + [Selengkapnya] → ke Log Aktivitas |

### 4.3 Manajemen PBF (Menu Sidebar — Super Admin Only)

| No | Fitur | Akses |
|----|-------|-------|
| 1 | Lihat daftar PBF | Super Admin |
| 2 | Tambah PBF baru | Super Admin |
| 3 | Edit data PBF | Super Admin |
| 4 | Hapus PBF | Super Admin |
| 5 | Lihat detail PBF | Super Admin |

**Form Input PBF:**

| No | Kolom | Tipe | Keterangan |
|----|-------|------|------------|
| 1 | Nama PBF | Text | Nama Pedagang Besar Farmasi |
| 2 | Alamat | Text | Alamat PBF |
| 3 | No. Telepon | Text | Nomor telepon PBF |
| 4 | Kontak Person | Text | Nama kontak PBF |
| 5 | Keterangan | Textarea | Catatan tambahan |

### 4.4 Manajemen Stok (Invoice-Based)

**Tabel utama — Daftar Faktur:**

| No | Kolom | Tipe | Keterangan |
|----|-------|------|------------|
| 1 | Nomor | Auto | Nomor urut |
| 2 | No Faktur | Text | Nomor faktur/invoice |
| 3 | PBF | Text | Nama PBF (dari dropdown) |
| 4 | Tgl Faktur | Date | Tanggal faktur diterbitkan |
| 5 | Tgl Masuk Barang | Date | Tanggal barang diterima |
| 6 | Jumlah Obat | Number | Jumlah jenis obat dalam faktur |
| 7 | Total Qty | Number | Total semua qty obat |
| 8 | Total Harga Faktur | Number | Total harga seluruh obat |
| 9 | Tgl Jatuh Tempo | Date | Tenggat waktu pembayaran |
| 10 | Aksi | Button | Detail, Edit, Hapus |

**Form Tambah Faktur (screen terpisah, bukan popup):**

*Bagian Header Faktur:*

| No | Kolom | Tipe |
|----|-------|------|
| 1 | No Faktur | Text |
| 2 | PBF | Select/Dropdown (dari tabel PBF) |
| 3 | Tanggal Faktur | Date |
| 4 | Tanggal Masuk Barang | Date |
| 5 | Tanggal Jatuh Tempo | Date |

*Bagian Obat (di screen yang sama):*

| No | Kolom | Tipe |
|----|-------|------|
| 1 | Nama Obat | Text |
| 2 | Jenis Obat | Text |
| 3 | Satuan | Select |
| 4 | Harga Beli | Number |
| 5 | Discount | Number |
| 6 | Qty (Jumlah) | Number |
| 7 | No Batch | Text (per-item popup saat qty > 0) |
| 8 | Exp Date | Date (per-item popup saat qty > 0) |

**Mekanisme Batch & Exp Date:**
Saat user mengisi qty (misal: 3), muncul popup berisi 3 form untuk mengisi No Batch dan Exp Date satu per satu untuk setiap unit obat.

**Fitur:**

| No | Fitur | Akses |
|----|-------|-------|
| 1 | Lihat daftar faktur | Super Admin, Admin |
| 2 | Tambah faktur + obat | Super Admin, Admin |
| 3 | Detail faktur (lihat isi obat) | Super Admin, Admin |
| 4 | Edit faktur | Super Admin, Admin |
| 5 | Hapus faktur | Super Admin saja |

### 4.5 Piutang (Otomatis dari Faktur)

Tabel piutang **otomatis** diisi dari data faktur. Tidak ada input manual piutang.

**Kolom tabel Piutang:**

| No | Kolom | Sumber |
|----|-------|--------|
| 1 | No Faktur | Otomatis dari faktur |
| 2 | PBF | Otomatis dari faktur |
| 3 | Tgl Faktur | Otomatis dari faktur |
| 4 | Total Harga | Otomatis dari faktur |
| 5 | Tgl Jatuh Tempo | Otomatis dari faktur |
| 6 | Status | Belum Lunas (default) / Lunas |
| 7 | Tgl Lunas | Otomatis saat dilunasi |
| 8 | Bukti Pembayaran | Upload foto saat pelunasan |

**Fitur:**

| No | Fitur | Keterangan |
|----|-------|------------|
| 1 | Lihat daftar piutang | Otomatis dari faktur |
| 2 | Lunasi piutang | Upload bukti bayar → status jadi Lunas |
| 3 | Lihat bukti bayar | Tampilkan foto + tombol Back |
| 4 | Filter status | Lunas / Belum Lunas |
| 5 | Filter periode | Per bulan |
| 6 | Export | Excel / PDF |

### 4.6 Laporan Expired (Otomatis)

Sepenuhnya **otomatis** dari data obat di stok masuk. Menyortir obat yang **≤ 6 bulan** dari expired.

**Kolom tabel:**

| No | Kolom | Keterangan |
|----|-------|------------|
| 1 | Nama Obat | Dari data stok |
| 2 | No Batch | Batch obat |
| 3 | Exp Date | Tanggal kedaluwarsa |
| 4 | Sisa Hari | Otomatis hitung sisa hari |
| 5 | Qty | Jumlah |
| 6 | Satuan | Satuan obat |
| 7 | PBF | Asal PBF |
| 8 | No Faktur | Dari faktur |

**Filter:** PBF, rentang expired, nama obat, status (segera expired / sudah expired).

### 4.7 Manajemen Admin (Tidak berubah)

Super Admin only. CRUD akun admin.

### 4.8 Log Aktivitas — SEMUA Aksi Tercatat

**Kolom tabel:**

| No | Kolom | Keterangan |
|----|-------|------------|
| 1 | Waktu | Timestamp aktivitas |
| 2 | User | Nama user |
| 3 | Role | Role user |
| 4 | Aksi | Jenis aksi |
| 5 | Keterangan | Detail lengkap |

**Daftar SEMUA aksi yang dicatat:**

- **Auth:** Login, Logout
- **PBF:** Tambah PBF, Edit PBF, Hapus PBF, Lihat Detail PBF
- **Faktur/Stok:** Tambah Faktur, Edit Faktur, Hapus Faktur, Lihat Detail Faktur, Tambah Obat ke Faktur, Edit Obat, Hapus Obat
- **Piutang:** Lihat Piutang, Lunasi Piutang (Upload Bukti), Lihat Bukti Bayar, Export Laporan Piutang
- **Laporan Expired:** Lihat Laporan Expired, Filter Laporan
- **Admin:** Tambah Admin, Edit Admin, Hapus Admin
- **Dashboard:** Akses Dashboard
