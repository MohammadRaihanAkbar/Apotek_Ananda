<?php
/**
 * Halaman Login - Apotek Ananda Jadimulya
 * Dengan Math CAPTCHA, CSRF protection, dan rate limiting.
 */
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
require_once __DIR__ . '/../../backend/helpers/csrf_helper.php';
require_once __DIR__ . '/../../backend/helpers/captcha_helper.php';

initSecureSession();

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    if (isSuperAdmin()) {
        redirect(BASE_URL . '/frontend/superadmin/dashboard.php');
    } else {
        redirect(BASE_URL . '/frontend/admin/dashboard.php');
    }
}

$flash = getFlashMessage();
$csrfToken = getCSRFToken();
$captchaQuestion = generateAndStoreCaptcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apotek Ananda Jadimulya</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:linear-gradient(135deg,#1e293b 0%,#334155 100%);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
        .login-container{background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.3);padding:40px;width:100%;max-width:420px}
        .login-header{text-align:center;margin-bottom:30px}
        .login-logo{display:block;width:280px;max-width:100%;height:auto;margin:0 auto 18px;object-fit:contain}
        .login-header p{color:#64748b;font-size:13px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
        .form-control{width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;transition:all .2s}
        .form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
        .captcha-group{display:flex;gap:10px;align-items:center;margin-bottom:18px}
        .captcha-question{background:#f1f5f9;border:1px solid #d1d5db;border-radius:6px;padding:10px 16px;font-size:18px;font-weight:700;color:#1e293b;letter-spacing:2px;white-space:nowrap;user-select:none}
        .captcha-group .refresh-btn{background:#f1f5f9;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;cursor:pointer;font-size:16px;transition:all .2s;display:flex;align-items:center;justify-content:center}
        .captcha-group .refresh-btn:hover{background:#e2e8f0}
        .captcha-input{flex:1}
        .btn-login{width:100%;padding:12px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
        .btn-login:hover{background:#1d4ed8;transform:translateY(-1px)}
        .btn-login:active{transform:translateY(0)}
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13px}
        .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
        .alert-success{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
        .security-note{margin-top:20px;text-align:center;font-size:11px;color:#94a3b8}
        .security-note span{color:#60a5fa}
        @media (max-width: 480px) {
            body { padding: 12px; }
            .login-container { padding: 28px 20px; }
            .login-logo { width: 230px; margin-bottom: 14px; }
            .captcha-group { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="<?= BASE_URL ?>/frontend/assets/images/logo_ananda_group.jpg" alt="Apotek Ananda Group" class="login-logo">
            <p>Sistem Informasi Manajemen Stok Obat</p>
        </div>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= BASE_URL ?>/backend/controllers/auth_controller.php?action=login" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            
            <div class="form-group">
                <label>Verifikasi CAPTCHA</label>
                <div class="captcha-group">
                    <span class="captcha-question" id="captchaQuestion"><?= htmlspecialchars($captchaQuestion) ?></span>
                    <button type="button" class="refresh-btn" onclick="refreshCaptcha()" title="Soal baru"><span class="material-icons-round">autorenew</span></button>
                    <input type="text" name="captcha" class="form-control captcha-input" placeholder="Jawaban" required autocomplete="off">
                </div>
            </div>
            
            <button type="submit" class="btn-login"><span class="material-icons-round">login</span>Masuk</button>
        </form>
        
        <div class="security-note">
            <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">verified_user</span> Dilindungi oleh <span>CAPTCHA</span> &amp; <span>CSRF Protection</span>
        </div>
    </div>
    
    <script>
        function refreshCaptcha() {
            fetch('<?= BASE_URL ?>/captcha.php?refresh=1&_=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    document.getElementById('captchaQuestion').textContent = data.question;
                })
                .catch(err => console.error('Captcha refresh error:', err));
        }
    </script>
</body>
</html>
