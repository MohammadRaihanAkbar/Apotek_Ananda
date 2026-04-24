<?php
/**
 * Controller: Piutang - Apotek Ananda Jadimulya
 * CRUD piutang, pelunasan dengan upload bukti, dan export laporan.
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
    case 'create':       handleCreate(); break;
    case 'update':       handleUpdate(); break;
    case 'lunasi':       handleLunasi(); break;
    case 'delete':       handleDelete(); break;
    case 'export_excel': handleExportExcel(); break;
    case 'export_pdf':   handleExportPDF(); break;
    default:
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        break;
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return;
    }
    requireValidCSRF();
    
    $data = [
        'no_faktur'           => sanitize($_POST['no_faktur'] ?? ''),
        'nama_pbf'            => sanitize($_POST['nama_pbf'] ?? ''),
        'tanggal_faktur'      => $_POST['tanggal_faktur'] ?? '',
        'tanggal_jatuh_tempo' => $_POST['tanggal_jatuh_tempo'] ?? '',
        'jumlah_harga'        => sanitizeDecimal($_POST['jumlah_harga'] ?? 0),
        'status'              => 'belum_lunas',
        'bukti_pembayaran'    => null,
    ];
    
    if (empty($data['no_faktur']) || empty($data['nama_pbf']) || 
        !isValidDate($data['tanggal_faktur']) || !isValidDate($data['tanggal_jatuh_tempo']) ||
        $data['jumlah_harga'] <= 0) {
        setFlashMessage('error', 'Data tidak lengkap atau tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    // Handle upload bukti (opsional saat input awal)
    if (!empty($_FILES['bukti_pembayaran']['name'])) {
        $uploadResult = handleUploadBukti();
        if ($uploadResult === false) return;
        $data['bukti_pembayaran'] = $uploadResult;
    }
    
    $model = new Piutang();
    $id = $model->create($data, getCurrentUserId());
    
    if ($id) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Tambah Piutang Baru', 
            "Menambahkan piutang: Faktur {$data['no_faktur']} - PBF {$data['nama_pbf']} - Rp " . number_format($data['jumlah_harga'], 0, ',', '.'));
        setFlashMessage('success', 'Data piutang berhasil ditambahkan.');
    } else {
        setFlashMessage('error', 'Gagal menambahkan piutang.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_piutang'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID piutang tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    $data = [
        'no_faktur'           => sanitize($_POST['no_faktur'] ?? ''),
        'nama_pbf'            => sanitize($_POST['nama_pbf'] ?? ''),
        'tanggal_faktur'      => $_POST['tanggal_faktur'] ?? '',
        'tanggal_jatuh_tempo' => $_POST['tanggal_jatuh_tempo'] ?? '',
        'jumlah_harga'        => sanitizeDecimal($_POST['jumlah_harga'] ?? 0),
    ];
    
    if (empty($data['no_faktur']) || empty($data['nama_pbf'])) {
        setFlashMessage('error', 'Data tidak lengkap.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    $model = new Piutang();
    if ($model->update($id, $data)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Edit Piutang', 
            "Mengubah piutang ID {$id}: Faktur {$data['no_faktur']}");
        setFlashMessage('success', 'Data piutang berhasil diperbarui.');
    } else {
        setFlashMessage('error', 'Gagal memperbarui piutang.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

function handleLunasi(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_piutang'] ?? 0);
    if ($id <= 0) {
        setFlashMessage('error', 'ID piutang tidak valid.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    // Upload bukti pembayaran (WAJIB saat pelunasan)
    $buktiPath = null;
    if (!empty($_FILES['bukti_pembayaran']['name'])) {
        $buktiPath = handleUploadBukti();
        if ($buktiPath === false) return;
    } else {
        setFlashMessage('error', 'Bukti pembayaran wajib diupload saat pelunasan.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    $model = new Piutang();
    $piutang = $model->findById($id);
    
    if (!$piutang) {
        setFlashMessage('error', 'Piutang tidak ditemukan.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return;
    }
    
    if ($model->lunasi($id, $buktiPath)) {
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Ubah Status Piutang (Lunas)', 
            "Melunasi piutang Faktur {$piutang['no_faktur']} - PBF {$piutang['nama_pbf']}");
        setFlashMessage('success', 'Piutang berhasil dilunasi.');
    } else {
        setFlashMessage('error', 'Gagal melunasi piutang.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(BASE_URL . '/frontend/superadmin/piutang.php'); return;
    }
    requireValidCSRF();
    
    $id = sanitizeInt($_POST['id_piutang'] ?? 0);
    $model = new Piutang();
    $piutang = $model->findById($id);
    
    if ($piutang && $model->delete($id)) {
        // Hapus file bukti jika ada
        if (!empty($piutang['bukti_pembayaran'])) {
            $filePath = __DIR__ . '/../../' . $piutang['bukti_pembayaran'];
            if (file_exists($filePath)) unlink($filePath);
        }
        
        $logModel = new LogAktivitas();
        $logModel->catat(getCurrentUserId(), 'Hapus Piutang', 
            "Menghapus piutang Faktur {$piutang['no_faktur']}");
        setFlashMessage('success', 'Piutang berhasil dihapus.');
    } else {
        setFlashMessage('error', 'Gagal menghapus piutang.');
    }
    
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

/**
 * Upload file bukti pembayaran
 * @return string|false Path relatif file jika berhasil, false jika gagal
 */
function handleUploadBukti() {
    $file = $_FILES['bukti_pembayaran'];
    
    // Validasi error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('error', 'Gagal mengupload file.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    
    // Validasi ukuran (maks 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        setFlashMessage('error', 'Ukuran file maksimal 5MB.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    
    // Validasi tipe file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        setFlashMessage('error', 'Format file tidak didukung. Gunakan JPG, PNG, WebP, atau PDF.');
        redirect(BASE_URL . '/frontend/superadmin/piutang.php');
        return false;
    }
    
    // Buat direktori upload jika belum ada
    $uploadDir = __DIR__ . '/../../uploads/bukti_pembayaran/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate nama file unik
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
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
    
    $model = new Piutang();
    $data = $model->getAll($status, $bulan);
    
    $headers = ['No', 'No. Faktur', 'Nama PBF', 'Tgl Faktur', 'Jatuh Tempo', 'Jumlah Harga', 'Status', 'Tgl Lunas'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            $row['no_faktur'],
            $row['nama_pbf'],
            $row['tanggal_faktur'],
            $row['tanggal_jatuh_tempo'],
            number_format($row['jumlah_harga'], 0, ',', '.'),
            $row['status'] === 'lunas' ? 'Lunas' : 'Belum Lunas',
            $row['tanggal_lunas'] ?? '-'
        ];
    }
    
    $filename = 'Laporan_Piutang_' . ($bulan ?: date('Y-m')) . '_' . date('Ymd');
    exportToExcel($filename, 'Laporan Piutang Apotek Ananda', $headers, $rows);
}

function handleExportPDF(): void {
    $bulan = $_GET['bulan'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $model = new Piutang();
    $data = $model->getAll($status, $bulan);
    $summary = $model->getSummary($bulan);
    
    $headers = ['No', 'No. Faktur', 'Nama PBF', 'Tgl Faktur', 'Jatuh Tempo', 'Jumlah Harga', 'Status', 'Tgl Lunas'];
    $rows = [];
    $no = 1;
    foreach ($data as $row) {
        $rows[] = [
            $no++,
            $row['no_faktur'],
            $row['nama_pbf'],
            $row['tanggal_faktur'],
            $row['tanggal_jatuh_tempo'],
            'Rp ' . number_format($row['jumlah_harga'], 0, ',', '.'),
            $row['status'] === 'lunas' ? 'Lunas' : 'Belum Lunas',
            $row['tanggal_lunas'] ?? '-'
        ];
    }
    
    $summaryDisplay = [
        'Total Piutang'       => 'Rp ' . number_format($summary['total_semua'], 0, ',', '.'),
        'Total Lunas'         => 'Rp ' . number_format($summary['total_lunas'], 0, ',', '.'),
        'Total Belum Lunas'   => 'Rp ' . number_format($summary['total_belum_lunas'], 0, ',', '.'),
        'Jumlah Record Lunas' => $summary['count_lunas'],
        'Jumlah Record Belum' => $summary['count_belum_lunas'],
    ];
    
    $title = 'Laporan Piutang' . ($bulan ? " - Periode $bulan" : '');
    exportToPDFView($title, $headers, $rows, $summaryDisplay);
}
