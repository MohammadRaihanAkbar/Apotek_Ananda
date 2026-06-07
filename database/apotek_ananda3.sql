-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql105.infinityfree.com
-- Generation Time: May 19, 2026 at 03:39 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41843273_apotek_ananda3`
--

-- --------------------------------------------------------

--
-- Table structure for table `faktur`
--

CREATE TABLE `faktur` (
  `id_faktur` int(11) NOT NULL,
  `no_faktur` varchar(100) NOT NULL,
  `id_pbf` int(11) NOT NULL,
  `tanggal_faktur` date NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `jumlah_obat` int(11) NOT NULL DEFAULT 0,
  `total_qty` int(11) NOT NULL DEFAULT 0,
  `total_harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_bayar` enum('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas',
  `tanggal_lunas` date DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faktur`
--

INSERT INTO `faktur` (`id_faktur`, `no_faktur`, `id_pbf`, `tanggal_faktur`, `tanggal_masuk`, `tanggal_jatuh_tempo`, `jumlah_obat`, `total_qty`, `total_harga`, `status_bayar`, `tanggal_lunas`, `bukti_pembayaran`, `id_user`, `created_at`, `updated_at`) VALUES
(1, '00931', 1, '2026-05-20', '2026-05-03', '2026-05-31', 1, 1, '900.00', 'belum_lunas', NULL, NULL, 1, '2026-05-03 23:56:01', '2026-05-18 12:31:04'),
(2, '54720', 2, '2026-05-28', '2026-05-04', NULL, 1, 2, '10000.00', 'lunas', '2026-05-04', 'uploads/bukti_pembayaran/bukti_20260504_062720_c8e4157b.jpeg', 1, '2026-05-04 00:01:34', '2026-05-04 11:54:58'),
(3, '0005', 3, '2026-05-18', '2026-05-18', NULL, 5, 10, '89500.00', 'lunas', '2026-05-18', 'uploads/bukti_pembayaran/bukti_20260518_055050_be0d46a2.png', 1, '2026-05-18 02:41:06', '2026-05-18 02:50:50'),
(4, '61928', 3, '2026-05-19', '2026-05-21', '2026-06-18', 1, 2, '9500.00', 'belum_lunas', NULL, NULL, 1, '2026-05-18 12:28:50', '2026-05-18 12:28:50'),
(5, '4652345', 4, '2026-05-19', '2026-05-20', '2026-05-21', 2, 6, '146000.00', 'lunas', '2026-05-18', 'uploads/bukti_pembayaran/bukti_20260519_020821_7cfd55c8.jpg', 1, '2026-05-18 22:54:32', '2026-05-18 23:08:20');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(50) NOT NULL,
  `attempted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `aksi`, `keterangan`, `created_at`) VALUES
(1, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-03 23:10:13'),
(2, 1, 'Tambah PBF', 'Menambahkan PBF baru: redup', '2026-05-03 23:30:38'),
(3, 1, 'Tambah Faktur', 'Menambahkan faktur 00931 dari PBF redup dengan 1 item obat', '2026-05-03 23:56:02'),
(4, 1, 'Tambah PBF', 'Menambahkan PBF baru: yudia', '2026-05-04 00:00:14'),
(5, 1, 'Tambah Faktur', 'Menambahkan faktur 5472u dari PBF yudia dengan 1 item obat', '2026-05-04 00:01:34'),
(6, 1, 'Ubah Status Faktur (Lunas)', 'Melunasi faktur 5472u - PBF yudia', '2026-05-04 00:04:18'),
(7, 1, 'Tambah Akun Admin', 'Menambahkan akun admin baru: redup (admin)', '2026-05-04 00:07:12'),
(8, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-04 00:08:01'),
(9, 2, 'Login', 'User redup login ke sistem.', '2026-05-04 00:08:24'),
(10, 2, 'Edit Faktur', 'Mengubah faktur 54720 dengan 1 item obat', '2026-05-04 00:11:03'),
(11, 2, 'Logout', 'User redup logout dari sistem.', '2026-05-04 00:16:34'),
(12, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 00:16:55'),
(13, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 00:49:50'),
(14, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 11:10:28'),
(15, 1, 'Ubah Status Faktur (Belum Lunas)', 'Mengubah faktur 54720 menjadi belum lunas', '2026-05-04 11:27:07'),
(16, 1, 'Ubah Status Faktur (Lunas)', 'Melunasi faktur 54720 - PBF yudia', '2026-05-04 11:27:20'),
(17, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-04 11:38:31'),
(18, 2, 'Login', 'User redup login ke sistem.', '2026-05-04 11:39:00'),
(19, 2, 'Logout', 'User redup logout dari sistem.', '2026-05-04 11:41:47'),
(20, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 11:42:26'),
(21, 1, 'Edit Faktur', 'Mengubah faktur 54720 dengan 1 item obat', '2026-05-04 11:54:59'),
(22, 1, 'Edit Faktur', 'Mengubah faktur 00931 dengan 1 item obat', '2026-05-04 11:55:58'),
(23, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-04 11:56:19'),
(24, 2, 'Login', 'User redup login ke sistem.', '2026-05-04 11:57:10'),
(25, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 14:58:37'),
(26, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 16:11:51'),
(27, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-04 17:43:20'),
(28, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:20:37'),
(29, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:23:38'),
(30, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:23:44'),
(31, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:24:49'),
(32, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:45:44'),
(33, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-05 22:47:43'),
(34, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-06 02:36:26'),
(35, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-06 20:27:03'),
(36, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-06 20:27:38'),
(37, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-08 01:16:47'),
(38, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-08 01:21:25'),
(39, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-08 01:21:47'),
(40, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-12 20:42:48'),
(41, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-13 05:38:33'),
(42, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 01:17:34'),
(43, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 02:09:05'),
(44, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-18 02:17:29'),
(45, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 02:20:40'),
(46, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 02:20:48'),
(47, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-18 02:22:37'),
(48, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 02:27:03'),
(49, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-18 02:29:57'),
(50, 2, 'Login', 'User redup login ke sistem.', '2026-05-18 02:30:11'),
(51, 2, 'Logout', 'User redup logout dari sistem.', '2026-05-18 02:32:08'),
(52, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 02:32:38'),
(53, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-18 02:34:28'),
(54, 1, 'Tambah PBF', 'Menambahkan PBF baru: UIN', '2026-05-18 02:34:54'),
(55, 1, 'Tambah Faktur', 'Menambahkan faktur 0005 dari PBF UIN dengan 3 item obat', '2026-05-18 02:41:06'),
(56, 1, 'Edit Faktur', 'Mengubah faktur 0005 dengan 4 item obat', '2026-05-18 02:42:17'),
(57, 1, 'Edit Faktur', 'Mengubah faktur 0005 dengan 5 item obat', '2026-05-18 02:43:14'),
(58, 1, 'Ubah Status Faktur (Lunas)', 'Melunasi faktur 0005 - PBF UIN', '2026-05-18 02:50:50'),
(59, 2, 'Login', 'User redup login ke sistem.', '2026-05-18 05:09:38'),
(60, 2, 'Logout', 'User redup logout dari sistem.', '2026-05-18 05:10:15'),
(61, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 05:10:49'),
(62, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 06:02:28'),
(63, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 07:07:14'),
(64, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 08:42:26'),
(65, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 10:17:37'),
(66, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 11:06:58'),
(67, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 11:09:41'),
(68, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 12:26:54'),
(69, 1, 'Tambah Faktur', 'Menambahkan faktur 61928 dari PBF UIN dengan 1 item obat', '2026-05-18 12:28:50'),
(70, 1, 'Edit Faktur', 'Mengubah faktur 00931 dengan 1 item obat', '2026-05-18 12:31:04'),
(71, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 15:06:56'),
(72, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 17:11:10'),
(73, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 22:42:39'),
(74, 1, 'Tambah PBF', 'Menambahkan PBF baru: Aman Farma', '2026-05-18 22:46:34'),
(75, 1, 'Edit PBF', 'Mengubah data PBF: Aman Farma', '2026-05-18 22:47:09'),
(76, 1, 'Tambah Faktur', 'Menambahkan faktur 4652345 dari PBF Aman Farma dengan 2 item obat', '2026-05-18 22:54:32'),
(77, 1, 'Ubah Status Faktur (Lunas)', 'Melunasi faktur 4652345 - PBF Aman Farma', '2026-05-18 23:08:20'),
(78, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 23:20:23'),
(79, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-18 23:23:12'),
(80, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-18 23:24:45'),
(81, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-19 13:34:44'),
(82, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-19 13:34:56'),
(83, 1, 'Lihat Detail Faktur', 'Melihat detail faktur 61928 - PBF UIN', '2026-05-19 13:49:52'),
(84, 1, 'Export Piutang PDF', 'Mengekspor laporan piutang PDF sebanyak 5 data', '2026-05-19 13:51:10'),
(85, 1, 'Export Piutang PDF', 'Mengekspor laporan piutang PDF sebanyak 5 data', '2026-05-19 13:51:32'),
(86, 1, 'Logout', 'User Apoteker Ananda logout dari sistem.', '2026-05-19 14:34:47'),
(87, 1, 'Login', 'User Apoteker Ananda login ke sistem.', '2026-05-19 14:35:48');

-- --------------------------------------------------------

--
-- Table structure for table `obat_batch`
--

CREATE TABLE `obat_batch` (
  `id_batch` int(11) NOT NULL,
  `id_obat_faktur` int(11) NOT NULL,
  `no_batch` varchar(50) NOT NULL,
  `expired_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `obat_batch`
--

INSERT INTO `obat_batch` (`id_batch`, `id_obat_faktur`, `no_batch`, `expired_date`, `created_at`) VALUES
(6, 4, '72816', '2026-08-29', '2026-05-04 11:54:59'),
(7, 4, '91863', '2026-09-30', '2026-05-04 11:54:59'),
(26, 13, 'da', '2026-07-18', '2026-05-18 02:43:14'),
(27, 13, '112', '2026-06-18', '2026-05-18 02:43:14'),
(28, 13, '141', '2026-07-18', '2026-05-18 02:43:14'),
(29, 13, '4e242', '2026-08-18', '2026-05-18 02:43:14'),
(30, 13, '45r2', '2026-07-18', '2026-05-18 02:43:14'),
(31, 14, '1522', '2026-07-18', '2026-05-18 02:43:14'),
(32, 15, '41563', '2026-07-18', '2026-05-18 02:43:14'),
(33, 15, '9628195', '2026-08-18', '2026-05-18 02:43:14'),
(34, 16, '2424', '2026-07-18', '2026-05-18 02:43:14'),
(35, 17, '24e562e', '2026-08-18', '2026-05-18 02:43:14'),
(36, 18, '50918', '2026-09-30', '2026-05-18 12:28:50'),
(37, 18, '71579', '2027-02-03', '2026-05-18 12:28:50'),
(38, 19, '52653', '2026-07-31', '2026-05-18 12:31:04'),
(39, 20, '341543', '2026-12-19', '2026-05-18 22:54:32'),
(40, 21, '414', '2026-08-19', '2026-05-18 22:54:32'),
(41, 21, '4344', '2026-10-19', '2026-05-18 22:54:32'),
(42, 21, '4322', '2026-09-19', '2026-05-18 22:54:32'),
(43, 21, '6436', '2026-09-19', '2026-05-18 22:54:32'),
(44, 21, '4234', '2026-09-19', '2026-05-18 22:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `obat_faktur`
--

CREATE TABLE `obat_faktur` (
  `id_obat_faktur` int(11) NOT NULL,
  `id_faktur` int(11) NOT NULL,
  `nama_obat` varchar(100) NOT NULL,
  `merk_dagang` varchar(100) DEFAULT NULL,
  `jenis_obat` varchar(100) DEFAULT NULL,
  `satuan` enum('Tube','FLS','Strip','Sach','Box','Kaleng','Pcs','Tablet','Kapsul','Ampul','Supp','Ovula','Pack') NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `qty` int(11) NOT NULL DEFAULT 0,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `obat_faktur`
--

INSERT INTO `obat_faktur` (`id_obat_faktur`, `id_faktur`, `nama_obat`, `jenis_obat`, `satuan`, `harga_beli`, `discount`, `qty`, `total`, `created_at`, `updated_at`) VALUES
(4, 2, 'Tramadol', NULL, 'Kapsul', '10000.00', '50.00', 2, '10000.00', '2026-05-04 11:54:59', '2026-05-04 11:54:59'),
(13, 3, 'Tolak Angin', NULL, 'Box', '12000.00', '0.00', 5, '60000.00', '2026-05-18 02:43:14', '2026-05-18 02:43:14'),
(14, 3, 'Antangin', NULL, 'Pcs', '5000.00', '10.00', 1, '4500.00', '2026-05-18 02:43:14', '2026-05-18 02:43:14'),
(15, 3, 'OBH', NULL, 'Tube', '10000.00', '0.00', 2, '20000.00', '2026-05-18 02:43:14', '2026-05-18 02:43:14'),
(16, 3, 'APA', NULL, 'Tablet', '2000.00', '0.00', 1, '2000.00', '2026-05-18 02:43:14', '2026-05-18 02:43:14'),
(17, 3, 'Obat jamu buyung upi enak', NULL, 'Sach', '3000.00', '0.00', 1, '3000.00', '2026-05-18 02:43:14', '2026-05-18 02:43:14'),
(18, 4, 'ajojing', NULL, 'Tablet', '5000.00', '5.00', 2, '9500.00', '2026-05-18 12:28:50', '2026-05-18 12:28:50'),
(19, 1, 'PARACETAMOL', NULL, 'Box', '1000.00', '10.00', 1, '900.00', '2026-05-18 12:31:04', '2026-05-18 12:31:04'),
(20, 5, 'amoxcilin', NULL, 'Box', '100000.00', '4.00', 1, '96000.00', '2026-05-18 22:54:32', '2026-05-18 22:54:32'),
(21, 5, 'paracetamol', NULL, 'Box', '10000.00', '0.00', 5, '50000.00', '2026-05-18 22:54:32', '2026-05-18 22:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `pbf`
--

CREATE TABLE `pbf` (
  `id_pbf` int(11) NOT NULL,
  `nama_pbf` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pbf`
--

INSERT INTO `pbf` (`id_pbf`, `nama_pbf`, `alamat`, `no_telepon`, `kontak_person`, `keterangan`, `id_user`, `created_at`, `updated_at`) VALUES
(1, 'redup', 'cirebon', '0891389194', 'udin', NULL, 1, '2026-05-03 23:30:38', '2026-05-03 23:30:38'),
(2, 'yudia', 'cirtim', '0891389189', 'rehan', 'udin', 1, '2026-05-04 00:00:14', '2026-05-04 00:00:14'),
(3, 'UIN', 'kesambi', '09876678', '09876789', 'apa', 1, '2026-05-18 02:34:54', '2026-05-18 02:34:54'),
(4, 'Aman Farma', 'dafd', 'dddd', '54453645', 'eqr', 1, '2026-05-18 22:46:34', '2026-05-18 22:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Apoteker Ananda', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '2026-05-03 23:06:47', '2026-05-03 23:06:47'),
(2, 'redup', 'admin', '$2y$12$RsNUjlKw3ntWIdQ3nOh28OD/dcduQ1XSiNofw0JDb0ruroj5d3T1q', 'admin', '2026-05-04 00:07:12', '2026-05-04 00:07:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faktur`
--
ALTER TABLE `faktur`
  ADD PRIMARY KEY (`id_faktur`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_faktur_no` (`no_faktur`),
  ADD KEY `idx_faktur_pbf` (`id_pbf`),
  ADD KEY `idx_faktur_tanggal` (`tanggal_masuk`),
  ADD KEY `idx_faktur_status` (`status_bayar`),
  ADD KEY `idx_faktur_jatuh_tempo` (`tanggal_jatuh_tempo`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempted_at`),
  ADD KEY `idx_username_time` (`username`,`attempted_at`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_log_user` (`id_user`),
  ADD KEY `idx_log_time` (`created_at`);

--
-- Indexes for table `obat_batch`
--
ALTER TABLE `obat_batch`
  ADD PRIMARY KEY (`id_batch`),
  ADD KEY `idx_batch_exp` (`expired_date`),
  ADD KEY `idx_batch_obat` (`id_obat_faktur`);

--
-- Indexes for table `obat_faktur`
--
ALTER TABLE `obat_faktur`
  ADD PRIMARY KEY (`id_obat_faktur`),
  ADD KEY `idx_obat_faktur` (`id_faktur`),
  ADD KEY `idx_obat_nama` (`nama_obat`);

--
-- Indexes for table `pbf`
--
ALTER TABLE `pbf`
  ADD PRIMARY KEY (`id_pbf`),
  ADD UNIQUE KEY `nama_pbf` (`nama_pbf`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_pbf_nama` (`nama_pbf`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `faktur`
--
ALTER TABLE `faktur`
  MODIFY `id_faktur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `obat_batch`
--
ALTER TABLE `obat_batch`
  MODIFY `id_batch` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `obat_faktur`
--
ALTER TABLE `obat_faktur`
  MODIFY `id_obat_faktur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pbf`
--
ALTER TABLE `pbf`
  MODIFY `id_pbf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faktur`
--
ALTER TABLE `faktur`
  ADD CONSTRAINT `faktur_ibfk_1` FOREIGN KEY (`id_pbf`) REFERENCES `pbf` (`id_pbf`),
  ADD CONSTRAINT `faktur_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `obat_batch`
--
ALTER TABLE `obat_batch`
  ADD CONSTRAINT `obat_batch_ibfk_1` FOREIGN KEY (`id_obat_faktur`) REFERENCES `obat_faktur` (`id_obat_faktur`) ON DELETE CASCADE;

--
-- Constraints for table `obat_faktur`
--
ALTER TABLE `obat_faktur`
  ADD CONSTRAINT `obat_faktur_ibfk_1` FOREIGN KEY (`id_faktur`) REFERENCES `faktur` (`id_faktur`) ON DELETE CASCADE;

--
-- Constraints for table `pbf`
--
ALTER TABLE `pbf`
  ADD CONSTRAINT `pbf_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
