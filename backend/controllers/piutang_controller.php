<?php
/**
 * Controller: Piutang - status pembayaran faktur stok dan export laporan.
 * Akses: Super Admin SAJA.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../helpers/export_helper.php';
require_once __DIR__ . '/../models/piutang.php';
require_once __DIR__ . '/../models/log_aktivitas.php';

initSecureSession();
requireSuperAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'lunasi':       handleLunasi(); break;
    case 'belum_lunas':  handleBelumLunas(); break;
    case 'export_excel': handleExportExcel(); break;
    case 'export_pdf':   handleExportPDF(); break;
    case 'create':
    case 'update':
    case 'delete':
        setFlashMessage('error', 'Data piutang sekarang otomatis dari faktur stok. Tambah/edit/hapus dilakukan lewat Manajemen Stok.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        break;
    default:
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        break;
}

function handleLunasi(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return; }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_faktur'] ?? $_POST['id_piutang'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID faktur tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    $buktiPath = null;
    if (!empty($_FILES['bukti_pembayaran']['name'])) {
        $buktiPath = handleUploadBukti();
        if ($buktiPath === false) return;
    }
    
    $model = new Piutang();
    $piutang = $model->findById($id);
    if (!$piutang) {
        setFlashMessage('error', 'Faktur tidak ditemukan.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    if ($model->lunasi($id, $buktiPath)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Ubah Status Faktur (Lunas)', "Melunasi faktur {$piutang['no_faktur']} - PBF {$piutang['nama_pbf']}");
        setFlashMessage('success', 'Status faktur berhasil diubah menjadi lunas.');
    } else {
        setFlashMessage('error', 'Gagal mengubah status faktur.');
    }
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

function handleBelumLunas(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return; }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_faktur'] ?? $_POST['id_piutang'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID faktur tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    $model = new Piutang();
    $piutang = $model->findById($id);
    if (!$piutang) {
        setFlashMessage('error', 'Faktur tidak ditemukan.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    if ($model->belumLunas($id)) {
        (new LogAktivitas())->catat(getCurrentUserId(), 'Ubah Status Faktur (Belum Lunas)', "Mengubah faktur {$piutang['no_faktur']} menjadi belum lunas");
        setFlashMessage('success', 'Status faktur berhasil diubah menjadi belum lunas.');
    } else {
        setFlashMessage('error', 'Gagal mengubah status faktur.');
    }
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

function handleUploadBukti() {
    $file = $_FILES['bukti_pembayaran'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('error', 'Gagal mengupload file.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        setFlashMessage('error', 'Ukuran file maksimal 5MB.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowedTypes)) {
        setFlashMessage('error', 'Format file tidak didukung. Gunakan JPG, PNG, WebP, atau PDF.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    $uploadDir = __DIR__ . '/../../uploads/bukti_pembayaran/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    // Derive extension from validated MIME type, not user filename
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    $ext = $extMap[$mimeType] ?? pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/bukti_pembayaran/' . $filename;
    }
    setFlashMessage('error', 'Gagal menyimpan file.');
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
    return false;
}

function handleExportExcel(): void {
    $bulan = $_GET['bulan'] ?? null;
    $status = $_GET['status'] ?? null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    $data = (new Piutang())->getAll($status, $bulan, $search);
    $headers = ['No', 'No. Faktur', 'Nama PBF', 'Tgl Faktur', 'Jatuh Tempo', 'Total Faktur', 'Status', 'Tgl Lunas'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [$no++, $row['no_faktur'], $row['nama_pbf'], $row['tanggal_faktur'], ($row['tanggal_jatuh_tempo'] ?? '-'), number_format($row['jumlah_harga'], 0, ',', '.'), $row['status'] === 'lunas' ? 'Lunas' : 'Belum Lunas', $row['tanggal_lunas'] ?? '-'];
    }
    exportToExcel('Laporan_Piutang_' . ($bulan ?: date('Y-m')) . '_' . date('Ymd'), 'Laporan Piutang Apotek Ananda', $headers, $rows);
}

function handleExportPDF(): void {
    $bulan = $_GET['bulan'] ?? null;
    $status = $_GET['status'] ?? null;
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
    $model = new Piutang();
    $data = $model->getAll($status, $bulan, $search);
    $summary = $model->getSummary();
    $headers = ['No', 'No. Faktur', 'Nama PBF', 'Tgl Faktur', 'Jatuh Tempo', 'Total Faktur', 'Status', 'Tgl Lunas'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [$no++, $row['no_faktur'], $row['nama_pbf'], $row['tanggal_faktur'], ($row['tanggal_jatuh_tempo'] ?? '-'), 'Rp ' . number_format($row['jumlah_harga'], 0, ',', '.'), $row['status'] === 'lunas' ? 'Lunas' : 'Belum Lunas', $row['tanggal_lunas'] ?? '-'];
    }
    $summaryDisplay = [
        'Total Faktur'       => 'Rp ' . number_format($summary['total_semua'], 0, ',', '.'),
        'Total Lunas'        => 'Rp ' . number_format($summary['total_lunas'], 0, ',', '.'),
        'Total Belum Lunas'  => 'Rp ' . number_format($summary['total_belum_lunas'], 0, ',', '.'),
        'Jumlah Faktur Lunas'=> $summary['count_lunas'],
        'Jumlah Faktur Belum'=> $summary['count_belum_lunas'],
    ];
    exportToPDFView('Laporan Piutang' . ($bulan ? " - Periode $bulan" : ''), $headers, $rows, $summaryDisplay);
}
