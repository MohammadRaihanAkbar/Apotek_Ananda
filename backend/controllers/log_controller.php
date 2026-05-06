<?php
/**
 * Controller: Log Aktivitas - Apotek Ananda Jadimulya
 * Menampilkan riwayat aktivitas seluruh user dengan filter.
 * Akses: Super Admin DAN Admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireLogin();

/**
 * Ambil data log untuk halaman log_aktivitas dengan filter
 */
function getLogData(): array {
    $logModel = new LogAktivitas();

    $role = $_GET['role'] ?? null;
    if (!in_array($role, ['super_admin', 'admin'], true)) {
        $role = null;
    }

    $date = $_GET['date'] ?? null;
    if ($date !== null && $date !== '' && !isValidDate($date)) {
        $date = null;
    }

    $aksi = $_GET['aksi'] ?? null;
    $aksi = $aksi !== null && $aksi !== '' ? sanitize($aksi) : null;

    // Revisi: Admin juga bisa melihat log semua role, bukan hanya log dirinya sendiri.
    return $logModel->getAll(null, $role, $date, $aksi);
}

/**
 * Ambil log terbaru untuk widget dashboard
 */
function getRecentLogs(int $limit = 10): array {
    $logModel = new LogAktivitas();
    return $logModel->getRecent($limit);
}

/**
 * Ambil daftar kategori aksi unik untuk filter
 */
function getLogActions(): array {
    $logModel = new LogAktivitas();
    return $logModel->getUniqueActions();
}
