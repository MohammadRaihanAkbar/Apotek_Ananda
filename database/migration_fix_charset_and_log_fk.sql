-- =============================================
-- Migration: Fix charset latin1 → utf8mb4 + Fix log_aktivitas FK cascade
-- Apotek Ananda Jadimulya
-- Run this ONCE on database apotek_ananda3
-- =============================================

USE `apotek_ananda3`;

-- =============================================
-- FIX #7: Convert all tables from latin1 to utf8mb4
-- =============================================

ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `faktur` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `obat_faktur` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `obat_batch` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `pbf` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `log_aktivitas` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `login_attempts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ubah default database charset juga
ALTER DATABASE `apotek_ananda3` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- FIX #8: log_aktivitas FK ON DELETE CASCADE → SET NULL
-- Agar log audit tetap tersimpan meskipun user dihapus
-- =============================================

-- Drop existing FK constraint
ALTER TABLE `log_aktivitas` DROP FOREIGN KEY `log_aktivitas_ibfk_1`;

-- Ubah kolom id_user agar nullable
ALTER TABLE `log_aktivitas` MODIFY `id_user` int(11) DEFAULT NULL;

-- Re-add FK constraint dengan ON DELETE SET NULL
ALTER TABLE `log_aktivitas` 
  ADD CONSTRAINT `log_aktivitas_ibfk_1` 
  FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
