<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
requireSuperAdmin();
require_once __DIR__ . '/../../backend/models/piutang.php';

$id = isset($_GET['id']) ? sanitizeInt($_GET['id']) : 0;
$piutang = $id > 0 ? (new Piutang())->findById($id) : null;
if (!$piutang || empty($piutang['bukti_pembayaran'])) {
    setFlashMessage('error', 'Bukti pembayaran tidak ditemukan di data faktur.');
    redirect(BASE_URL . '/frontend/superadmin/piutang.php');
}

$pageTitle = 'Lihat Bukti Pembayaran';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/sidebar.php';

$path = str_replace('\\', '/', ltrim($piutang['bukti_pembayaran'], '/'));
$absolutePath = realpath(__DIR__ . '/../../' . $path);
$expectedRoot = realpath(__DIR__ . '/../../');
$fileExists = $absolutePath && $expectedRoot && strpos($absolutePath, $expectedRoot) === 0 && is_file($absolutePath);
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));
$fileUrl = BASE_URL . '/' . $encodedPath;
?>

<div class="page-header">
    <h1>Bukti Pembayaran</h1>
    <p>Faktur <?= htmlspecialchars($piutang['no_faktur']) ?> — <?= htmlspecialchars($piutang['nama_pbf']) ?></p>
</div>

<div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= BASE_URL ?>/frontend/superadmin/piutang.php" class="btn btn-secondary">← Back</a>
    <?php if ($fileExists): ?>
        <a href="<?= htmlspecialchars($fileUrl) ?>" class="btn btn-primary" target="_blank">Buka File</a>
    <?php endif; ?>
</div>

<div class="card" style="text-align:center;">
    <?php if (!$fileExists): ?>
        <div class="alert alert-error" style="justify-content:center;text-align:left;">
            File bukti belum ditemukan di folder project.<br>
            Path database: <code><?= htmlspecialchars($path) ?></code><br>
            Pastikan file upload ada di folder <code>uploads/bukti_pembayaran</code>.
        </div>
    <?php elseif ($ext === 'pdf'): ?>
        <iframe src="<?= htmlspecialchars($fileUrl) ?>" style="width:100%;height:75vh;border:1px solid var(--border);border-radius:12px;"></iframe>
    <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)): ?>
        <img src="<?= htmlspecialchars($fileUrl) ?>" alt="Bukti pembayaran" style="max-width:100%;max-height:75vh;border-radius:12px;border:1px solid var(--border);">
    <?php else: ?>
        <p>Preview format file ini tidak tersedia.</p>
        <a href="<?= htmlspecialchars($fileUrl) ?>" class="btn btn-primary" target="_blank">Buka File</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
