<?php
/**
 * Controller: Dashboard - Apotek Ananda Jadimulya
 * Menyediakan ringkasan untuk dashboard Super Admin dan Admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../models/stok_masuk.php';
require_once __DIR__ . '/../models/log_aktivitas.php';
require_once __DIR__ . '/../models/piutang.php';

initSecureSession();
requireLogin();

/**
 * Ambil data dashboard berdasarkan role.
 * Dashboard hanya menampilkan ringkasan; detail diarahkan ke menu masing-masing.
 */
function getDashboardData(): array {
    $stokModel = new StokMasuk();
    $logModel  = new LogAktivitas();

    $data = [
        'total_stok'  => $stokModel->getTotalStok(),
        'total_faktur'=> $stokModel->count(),
        'total_log'   => $logModel->count(),
    ];

    if (isSuperAdmin()) {
        $piutangSummary = (new Piutang())->getSummary();
        $data['piutang_belum_lunas_count'] = (int)($piutangSummary['count_belum_lunas'] ?? 0);
        $data['piutang_belum_lunas_total'] = (float)($piutangSummary['total_belum_lunas'] ?? 0);
        $data['expiring_6months_count'] = $stokModel->countExpiringSixMonths();
    }

    return $data;
}
