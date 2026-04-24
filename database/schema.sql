-- =============================================
-- DATABASE: apotek_ananda
-- Apotek Ananda Jadimulya - Sistem Manajemen Stok Obat
-- =============================================

CREATE DATABASE IF NOT EXISTS apotek_ananda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apotek_ananda;

-- =============================================
-- TABEL: users
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- TABEL: pbf
-- =============================================
CREATE TABLE IF NOT EXISTS pbf (
    id_pbf INT AUTO_INCREMENT PRIMARY KEY,
    nama_pbf VARCHAR(100) NOT NULL,
    id_user INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABEL: stok_masuk
-- =============================================
CREATE TABLE IF NOT EXISTS stok_masuk (
    id_masuk INT AUTO_INCREMENT PRIMARY KEY,
    id_pbf INT NOT NULL,
    no_faktur VARCHAR(100) NOT NULL,
    tanggal_masuk DATE NOT NULL,
    nama_obat VARCHAR(100) NOT NULL,
    satuan ENUM('Tube', 'FLS', 'Strip', 'Sach', 'Box', 'Kaleng', 'Pcs', 'Tablet', 'Kapsul', 'Ampul', 'Supp', 'Ovula', 'Pack') NOT NULL,
    expired_date DATE NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    jumlah_masuk INT NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pbf) REFERENCES pbf(id_pbf) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABEL: obat_expired (input manual)
-- =============================================
CREATE TABLE IF NOT EXISTS obat_expired (
    id_expired INT AUTO_INCREMENT PRIMARY KEY,
    nama_obat VARCHAR(100) NOT NULL,
    qty INT NOT NULL DEFAULT 0,
    satuan ENUM('Tube', 'FLS', 'Strip', 'Sach', 'Box', 'Kaleng', 'Pcs', 'Tablet', 'Kapsul', 'Ampul', 'Supp', 'Ovula', 'Pack') NOT NULL,
    batch VARCHAR(50),
    expired_date DATE NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
    nama_pbf VARCHAR(100),
    id_user INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABEL: piutang
-- =============================================
CREATE TABLE IF NOT EXISTS piutang (
    id_piutang INT AUTO_INCREMENT PRIMARY KEY,
    no_faktur VARCHAR(100) NOT NULL,
    nama_pbf VARCHAR(100) NOT NULL,
    tanggal_faktur DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    jumlah_harga DECIMAL(12,2) NOT NULL,
    status ENUM('lunas', 'belum_lunas') NOT NULL DEFAULT 'belum_lunas',
    tanggal_lunas DATE NULL,
    bukti_pembayaran VARCHAR(255) NULL,
    id_user INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABEL: log_aktivitas
-- =============================================
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    keterangan TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABEL: login_attempts (rate limiting)
-- =============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_username_time (username, attempted_at)
) ENGINE=InnoDB;

-- =============================================
-- INDEX untuk performa query
-- =============================================
CREATE INDEX idx_pbf_nama ON pbf(nama_pbf);
CREATE INDEX idx_stok_masuk_no_faktur ON stok_masuk(no_faktur);
CREATE INDEX idx_stok_masuk_tanggal ON stok_masuk(tanggal_masuk);
CREATE INDEX idx_stok_masuk_obat ON stok_masuk(nama_obat);
CREATE INDEX idx_stok_masuk_exp ON stok_masuk(expired_date);
CREATE INDEX idx_expired_date ON obat_expired(expired_date);
CREATE INDEX idx_expired_obat ON obat_expired(nama_obat);
CREATE INDEX idx_piutang_status ON piutang(status);
CREATE INDEX idx_piutang_jatuh_tempo ON piutang(tanggal_jatuh_tempo);
CREATE INDEX idx_piutang_faktur ON piutang(no_faktur);
CREATE INDEX idx_log_user ON log_aktivitas(id_user);
CREATE INDEX idx_log_time ON log_aktivitas(created_at);

-- =============================================
-- SEED: Default Super Admin
-- Password: superadmin123 (bcrypt hashed)
-- =============================================
INSERT INTO users (nama_lengkap, username, password, role) VALUES
('Apoteker Ananda', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- NOTE: Default password is 'password'. Change immediately after first login!
-- To generate a proper hash: php -r "echo password_hash('superadmin123', PASSWORD_BCRYPT);"
