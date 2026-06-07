<?php
/**
 * Controller: Laporan Expired otomatis dari obat_batch.
 * Akses: Super Admin.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../models/obat_expired.php';
require_once __DIR__ . '/../models/log_aktivitas.php';
require_once __DIR__ . '/../helpers/export_helper.php';

initSecureSession();
requireSuperAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'export_excel': handleExportExcel(); break;
    case 'export_pdf':   handleExportPDF(); break;
    case 'create':
    case 'update':
    case 'delete':
        setFlashMessage('error', 'Laporan expired sekarang otomatis dari batch obat. Input manual sudah tidak digunakan.');
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        break;
    default:
        redirect(BASE_URL . '/frontend/superadmin/laporan_expired.php');
        break;
}

function getExpiredFilters(): array {
    return [
        'pbf_id' => isset($_GET['pbf_id']) ? sanitizeInt($_GET['pbf_id']) : null,
        'nama_obat' => isset($_GET['nama_obat']) ? sanitize($_GET['nama_obat']) : null,
        'date_start' => $_GET['date_start'] ?? null,
        'date_end' => $_GET['date_end'] ?? null,
        'status' => in_array(($_GET['status'] ?? ''), ['expired', 'segera_expired'], true) ? $_GET['status'] : null,
    ];
}

function handleExportExcel(): void {
    $data = (new ObatExpired())->getExpiredReport(getExpiredFilters());

    (new LogAktivitas())->catat(
        getCurrentUserId(),
        'Export Laporan Expired Excel',
        'Mengekspor laporan expired Excel sebanyak ' . count($data) . ' data'
    );

    $headers = ['No', 'Nama Obat', 'Merk Dagang', 'No Batch', 'Exp Date', 'Sisa Hari', 'Qty', 'Satuan', 'Harga Beli', 'PBF', 'No Faktur'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            $row['nama_obat'],
            $row['merk_dagang'] ?: '-',
            $row['batch'],
            $row['expired_date'],
            (int)$row['sisa_hari'],
            (int)$row['qty'],
            $row['satuan'],
            number_format($row['harga_beli'], 0, ',', '.'),
            $row['nama_pbf'],
            $row['no_faktur']
        ];
    }
    exportToExcel('Laporan_Expired_' . date('Ymd'), 'Laporan Obat Expired Otomatis Apotek Ananda', $headers, $rows);
}

function handleExportPDF(): void {
    $data = (new ObatExpired())->getExpiredReport(getExpiredFilters());

    (new LogAktivitas())->catat(
        getCurrentUserId(),
        'Export Laporan Expired PDF',
        'Mengekspor laporan expired PDF sebanyak ' . count($data) . ' data'
    );

    $headers = ['No', 'Nama Obat', 'Merk Dagang', 'No Batch', 'Exp Date', 'Sisa Hari', 'Qty', 'Satuan', 'PBF', 'No Faktur'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            $row['nama_obat'],
            $row['merk_dagang'] ?: '-',
            $row['batch'],
            $row['expired_date'],
            (int)$row['sisa_hari'],
            (int)$row['qty'],
            $row['satuan'],
            $row['nama_pbf'],
            $row['no_faktur']
        ];
    }
    exportToPDFView('Laporan Obat Expired Otomatis', $headers, $rows);
}
