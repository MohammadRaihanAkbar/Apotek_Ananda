# PRD v3.0 — Part 3: Activity Diagrams Lengkap

---

## Activity Diagram 1 — Login

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Buka Halaman Login]
        B --> C[Input Username dan Password]
        C --> D[Klik Tombol Login]
    end

    subgraph Sistem
        E{Validasi Kredensial}
        F[Tampilkan Pesan Error]
        G{Cek Role User}
        H[Redirect ke Dashboard Super Admin]
        I[Redirect ke Dashboard Admin]
        J[Catat Log - Login]
        K([End])
    end

    D --> E
    E -->|Tidak Valid| F
    F --> C
    E -->|Valid| G
    G -->|Super Admin| H
    G -->|Admin| I
    H --> J
    I --> J
    J --> K
```

---

## Activity Diagram 2 — Logout

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Klik Tombol Logout]
    end

    subgraph Sistem
        C[Catat Log - Logout]
        D[Destroy Session]
        E[Redirect ke Halaman Login]
        F([End])
    end

    B --> C
    C --> D
    D --> E
    E --> F
```

---

## Activity Diagram 3 — Dashboard

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Akses Menu Dashboard]
        H[Klik Tombol Selengkapnya]
    end

    subgraph Sistem
        C{Cek Role}
        D[Tampilkan Widget Lengkap]
        E[Tampilkan Widget Terbatas]
        F[Redirect ke Halaman Terkait]
        G([End])
    end

    B --> C
    C -->|Super Admin| D
    C -->|Admin| E
    D --> H
    E --> H
    H --> F
    F --> G
```

---

## Activity Diagram 4 — Manajemen PBF

```mermaid
flowchart TD
    subgraph SuperAdmin[Super Admin]
        A([Start]) --> B[Login Super Admin]
        B --> C[Buka Menu PBF]
        I[Isi Form PBF Baru]
        L[Edit Data PBF]
        N[Konfirmasi Hapus PBF]
        Q[Klik Simpan]
    end

    subgraph Sistem
        D[Tampilkan Tabel PBF]
        E{Pilih Aksi}
        F[Tampilkan Detail PBF]
        R[Simpan ke Database]
        S[Catat Log Aktivitas]
        T([End])
        O{Konfirmasi Hapus}
        P[Hapus Data PBF]
    end

    C --> D
    D --> E
    E -->|Tambah| I
    E -->|Edit| L
    E -->|Hapus| N
    E -->|Detail| F
    I --> Q
    L --> Q
    Q --> R
    R --> S
    S --> T
    N --> O
    O -->|Ya| P
    O -->|Tidak| D
    P --> S
    F --> D
```

---

## Activity Diagram 5 — Lihat Daftar Faktur

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Login ke Sistem]
        B --> C[Buka Menu Manajemen Stok]
    end

    subgraph Sistem
        D[Tampilkan Tabel Daftar Faktur]
        E{Pilih Aksi}
        F[Ke Screen Tambah Faktur]
        G[Ke Screen Detail Faktur]
        H[Ke Screen Edit Faktur]
        I{Konfirmasi Hapus}
        J[Hapus Faktur dan Isinya]
        K[Catat Log Hapus Faktur]
        L([End])
    end

    C --> D
    D --> E
    E -->|Tambah Faktur| F
    E -->|Detail| G
    E -->|Edit| H
    E -->|Hapus - Super Admin| I
    I -->|Ya| J
    I -->|Tidak| D
    J --> K
    K --> D
    F --> L
    G --> L
    H --> L
```

---

## Activity Diagram 6 — Tambah Faktur Baru

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Klik Tambah Faktur]
        D[Isi Header Faktur]
        F[Isi Data Obat]
        H[Isi Batch dan Exp Date per Unit]
        K[Klik Simpan Faktur]
    end

    subgraph Sistem
        C[Tampilkan Form Tambah Faktur]
        E[Tampilkan Bagian Input Obat]
        G[Tampilkan Popup Batch per Qty]
        I[Obat Masuk ke Daftar]
        J{Tambah Obat Lagi}
        L[Hitung Total Otomatis]
        M[Simpan Faktur ke Database]
        N[Catat Log Tambah Faktur]
        O[Redirect ke Tabel Faktur]
        P([End])
    end

    B --> C
    C --> D
    D --> E
    E --> F
    F --> G
    G --> H
    H --> I
    I --> J
    J -->|Ya| F
    J -->|Tidak| L
    L --> K
    K --> M
    M --> N
    N --> O
    O --> P
```

---

## Activity Diagram 7 — Detail Faktur

```mermaid
flowchart TD
    subgraph User
        A([Start]) --> B[Klik Detail Faktur]
        G[Edit Data Obat]
        I[Klik Tombol Kembali]
    end

    subgraph Sistem
        C[Tampilkan Info Header Faktur]
        D[Tampilkan Tabel Obat dalam Faktur]
        E{Pilih Aksi}
        F[Simpan dan Catat Log]
        H[Hapus Obat dan Catat Log]
        J[Kembali ke Tabel Faktur]
        K([End])
    end

    B --> C
    C --> D
    D --> E
    E -->|Edit Obat| G
    E -->|Hapus - Super Admin| H
    E -->|Kembali| I
    G --> F
    F --> D
    H --> D
    I --> J
    J --> K
```

---

## Activity Diagram 8 — Piutang

```mermaid
flowchart TD
    subgraph SuperAdmin[Super Admin]
        A([Start]) --> B[Login Super Admin]
        B --> C[Buka Menu Piutang]
        K[Upload Foto Bukti Bayar]
        L[Klik Simpan]
        R[Klik Tombol Back]
    end

    subgraph Sistem
        D[Tampilkan Tabel Piutang dari Faktur]
        E{Filter Data}
        F[Filter Status]
        G[Filter Periode]
        H{Pilih Aksi}
        I[Tampilkan Form Pelunasan]
        M[Update Status ke Lunas]
        N[Catat Log Lunasi Piutang]
        O[Tampilkan Foto Bukti Bayar]
        S[Kembali ke Tabel]
        T[Generate File Export]
        U[File Terunduh]
        V[Catat Log Export]
        W([End])
    end

    C --> D
    D --> E
    E -->|Status| F
    E -->|Periode| G
    E -->|Tidak| H
    F --> D
    G --> D
    H -->|Lunasi| I
    H -->|Lihat Bukti| O
    H -->|Export| T
    I --> K
    K --> L
    L --> M
    M --> N
    N --> D
    O --> R
    R --> S
    S --> D
    T --> U
    U --> V
    V --> W
```

---

## Activity Diagram 9 — Laporan Expired Otomatis

```mermaid
flowchart TD
    subgraph SuperAdmin[Super Admin]
        A([Start]) --> B[Login Super Admin]
        B --> C[Buka Menu Laporan Expired]
    end

    subgraph Sistem
        D[Query Otomatis Obat Expired 6 Bulan]
        E[Tampilkan Tabel Expired]
        F{Gunakan Filter}
        G[Filter PBF]
        H[Filter Rentang Tanggal]
        I[Filter Nama Obat]
        J[Filter Status Expired]
        K[Catat Log Lihat Laporan]
        L([End])
    end

    C --> D
    D --> E
    E --> F
    F -->|PBF| G
    F -->|Tanggal| H
    F -->|Nama Obat| I
    F -->|Status| J
    F -->|Tidak| K
    G --> E
    H --> E
    I --> E
    J --> E
    K --> L
```

---

## Activity Diagram 10 — Kelola Akun Admin

```mermaid
flowchart TD
    subgraph SuperAdmin[Super Admin]
        A([Start]) --> B[Login Super Admin]
        B --> C[Buka Menu Kelola Admin]
        H[Isi Form Admin Baru]
        I[Edit Data Admin]
        J[Konfirmasi Hapus Admin]
        N[Klik Simpan]
    end

    subgraph Sistem
        D[Tampilkan Daftar Admin]
        E{Pilih Aksi}
        F[Tampilkan Detail Admin]
        K{Konfirmasi Hapus}
        LL[Hapus Akun Admin]
        O[Simpan ke Database]
        P[Catat Log Aktivitas]
        Q([End])
    end

    C --> D
    D --> E
    E -->|Tambah| H
    E -->|Edit| I
    E -->|Hapus| J
    E -->|Detail| F
    H --> N
    I --> N
    N --> O
    O --> P
    P --> D
    J --> K
    K -->|Ya| LL
    K -->|Tidak| D
    LL --> P
    F --> D
    D --> Q
```

---

## Activity Diagram 11 — Log Aktivitas

```mermaid
flowchart TD
    subgraph User[Super Admin atau Admin]
        A([Start]) --> B[Login ke Sistem]
        B --> C[Buka Menu Log Aktivitas]
    end

    subgraph Sistem
        D[Tampilkan Tabel Log Aktivitas]
        E{Gunakan Filter}
        F[Filter User]
        G[Filter Jenis Aksi]
        H[Filter Rentang Tanggal]
        I([End])
    end

    C --> D
    D --> E
    E -->|User| F
    E -->|Aksi| G
    E -->|Tanggal| H
    E -->|Tidak| I
    F --> D
    G --> D
    H --> D
```

---

## Ringkasan Perubahan v2.9 ke v3.0

| No | Perubahan | Detail |
|----|-----------|--------|
| 1 | PBF jadi menu sidebar sendiri | Super Admin only, info lengkap mitra pemasok |
| 2 | Manajemen Stok menjadi Invoice-Based | Obat dibungkus dalam faktur, screen baru |
| 3 | Tabel faktur baru | Header invoice dengan status bayar |
| 4 | Tabel obat_faktur baru | Detail obat per faktur |
| 5 | Tabel obat_batch baru | Batch dan exp date per unit obat |
| 6 | Piutang otomatis dari faktur | Tinggal lunasi dan upload bukti |
| 7 | Laporan Expired otomatis saja | Query otomatis dari obat_batch |
| 8 | Hapus tabel obat_expired | Tidak diperlukan lagi |
| 9 | Hapus tabel piutang | Digabung ke faktur via status_bayar |
| 10 | Hapus tabel stok_masuk | Diganti obat_faktur dan obat_batch |
| 11 | Log Aktivitas catat semua aksi | Termasuk lihat, filter, export |
| 12 | Dashboard plus tombol Selengkapnya | Ringkasan saja, klik untuk detail |
