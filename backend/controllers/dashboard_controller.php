<?php
/**
 * Controller: Dashboard - Apotek Ananda Jadimulya
 * Menyediakan data untuk dashboard Super Admin dan Admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../models/stok_masuk.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireLogin();

/**
 * Ambil data dashboard berdasarkan role
 */
function getDashboardData(): array {
    $stokModel = new StokMasuk();
    $logModel  = new LogAktivitas();
    
    $data = [
        'total_stok'     => $stokModel->getTotalStok(),
        'recent_logs'    => $logModel->getRecent(10),
    ];
    
    // Data tambahan untuk Super Admin
    if (isSuperAdmin()) {
        $data['expiring_6months_count'] = $stokModel->countExpiringSixMonths();
        $data['expiring_6months_list']  = $stokModel->getExpiringSixMonths();
    }
    
    return $data;
}
