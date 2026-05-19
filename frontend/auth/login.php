<?php
/**
 * Halaman Login - Apotek Ananda Jadimulya
 * Premium Glassmorphism Login UI
 */

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
require_once __DIR__ . '/../../backend/helpers/csrf_helper.php';
require_once __DIR__ . '/../../backend/helpers/captcha_helper.php';

initSecureSession();

// Jika sudah login
if (isLoggedIn()) {

    if (isSuperAdmin()) {

        redirect(BASE_URL . '/frontend/superadmin/dashboard.php');

    } else {

        redirect(BASE_URL . '/frontend/admin/dashboard.php');
    }
}

$flash           = getFlashMessage();
$csrfToken       = getCSRFToken();
$captchaQuestion = generateAndStoreCaptcha();
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Apotek Ananda Jadimulya</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:'Poppins',sans-serif;
    }

    body{
        min-height:100vh;

        display:flex;
        justify-content:center;
        align-items:center;

        padding:20px;

        overflow:hidden;

        position:relative;

        background:
            linear-gradient(
                135deg,
                #f5f9ff 0%,
                #edf4ff 45%,
                #ffffff 100%
            );
    }

    /* =========================
       BACKGROUND GLOW
    ========================= */

    body::before{
        content:'';

        position:fixed;
        inset:-20%;

        background:
            radial-gradient(circle at 20% 20%, rgba(59,130,246,.18), transparent 28%),
            radial-gradient(circle at 80% 30%, rgba(125,211,252,.18), transparent 28%),
            radial-gradient(circle at 50% 80%, rgba(96,165,250,.12), transparent 30%);

        filter:blur(70px);

        animation:bgMove 18s linear infinite;

        z-index:-5;
    }

    body::after{
        content:'';

        position:fixed;
        inset:0;

        background-image:
            linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);

        background-size:36px 36px;

        mask-image:
            radial-gradient(circle at center, black 35%, transparent 85%);

        z-index:-4;
    }

    @keyframes bgMove{

        0%{
            transform:translate(0,0) rotate(0deg);
        }

        50%{
            transform:translate(35px,-25px) rotate(180deg);
        }

        100%{
            transform:translate(0,0) rotate(360deg);
        }
    }

    /* =========================
       FLOATING BUBBLE
    ========================= */

    .bg-bubble{
        position:fixed;

        border-radius:50%;

        background:
            radial-gradient(
                circle at 30% 30%,
                rgba(255,255,255,.95),
                rgba(255,255,255,.08)
            );

        filter:blur(10px);

        animation:floating 14s ease-in-out infinite;

        z-index:-2;
    }

    .bg-bubble.one{
        width:220px;
        height:220px;
        top:-60px;
        left:-70px;
    }

    .bg-bubble.two{
        width:280px;
        height:280px;
        bottom:-90px;
        right:-100px;

        animation-duration:18s;
    }

    @keyframes floating{

        0%,100%{
            transform:translateY(0) translateX(0);
        }

        50%{
            transform:translateY(-18px) translateX(12px);
        }
    }

    /* =========================
       LOGIN WRAPPER
    ========================= */

    .login-wrapper{
        width:100%;
        max-width:440px;

        position:relative;
        z-index:10;
    }

    .glass-container{
        position:relative;

        overflow:hidden;

        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.58),
                rgba(255,255,255,.26)
            );

        border:1px solid rgba(255,255,255,.7);

        backdrop-filter:blur(20px);
        -webkit-backdrop-filter:blur(20px);

        border-radius:28px;

        padding:18px;

        box-shadow:
            0 10px 30px rgba(15,23,42,.08),
            inset 0 1px 0 rgba(255,255,255,.85);
    }

    .glass-container::before{
        content:'';

        position:absolute;
        top:0;
        left:-120%;

        width:60%;
        height:100%;

        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.2),
                transparent
            );

        transform:skewX(-25deg);

        animation:shine 8s linear infinite;
    }

    @keyframes shine{

        0%{
            left:-120%;
        }

        100%{
            left:150%;
        }
    }

    .glass-inner{
        background:rgba(255,255,255,.30);

        border:1px solid rgba(255,255,255,.65);

        border-radius:22px;

        backdrop-filter:blur(12px);

        padding:34px 28px;
    }

    /* =========================
       HEADER
    ========================= */

    .login-header{
        text-align:center;
        margin-bottom:28px;
    }

    .logo-image{
        width:95px;
        height:95px;

        margin:0 auto 16px;

        border-radius:24px;

        overflow:hidden;

        background:#fff;

        padding:4px;

        border:2px solid rgba(255,255,255,.7);

        box-shadow:
            0 12px 24px rgba(37,99,235,.20);
    }

    .logo-image img{
        width:100%;
        height:100%;

        object-fit:cover;

        border-radius:20px;

        display:block;
    }

    .login-header h1{
        font-size:24px;
        font-weight:700;
        color:#0f172a;

        margin-bottom:6px;
    }

    .login-header p{
        font-size:13px;
        color:#64748b;

        line-height:1.7;
    }

    /* =========================
       ALERT
    ========================= */

    .alert{
        padding:12px 14px;

        border-radius:14px;

        margin-bottom:18px;

        font-size:12px;
        font-weight:600;
    }

    .alert-error{
        background:rgba(254,226,226,.75);
        color:#991b1b;
        border:1px solid rgba(252,165,165,.4);
    }

    .alert-success{
        background:rgba(220,252,231,.75);
        color:#166534;
        border:1px solid rgba(134,239,172,.4);
    }

    /* =========================
       FORM
    ========================= */

    .form-group{
        margin-bottom:18px;
    }

    .form-group label{
        display:block;

        margin-bottom:8px;

        font-size:12px;
        font-weight:700;

        color:#475569;
    }

    .form-control{
        width:100%;

        border:none;

        background:rgba(255,255,255,.68);

        border-radius:14px;

        padding:13px 14px;

        font-size:13px;
        font-weight:500;

        color:#0f172a;

        outline:none;

        transition:.2s ease;

        box-shadow:
            inset 0 0 0 1px rgba(203,213,225,.75);
    }

    .form-control:focus{
        box-shadow:
            inset 0 0 0 2px rgba(59,130,246,.35),
            0 0 0 4px rgba(59,130,246,.08);
    }

    .form-control::placeholder{
        color:#94a3b8;
    }

    /* =========================
       CAPTCHA
    ========================= */

    .captcha-group{
        display:flex;
        gap:10px;
        align-items:center;
    }

    .captcha-question{
        min-width:110px;

        text-align:center;

        padding:13px 14px;

        border-radius:14px;

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,.85),
                rgba(255,255,255,.55)
            );

        border:1px solid rgba(255,255,255,.8);

        font-size:18px;
        font-weight:700;

        color:#0f172a;

        letter-spacing:1px;

        user-select:none;
    }

    .captcha-input{
        flex:1;
    }

    .refresh-btn{
        width:50px;
        height:50px;

        border:none;

        border-radius:14px;

        cursor:pointer;

        font-size:18px;

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,.85),
                rgba(255,255,255,.55)
            );

        color:#2563eb;

        transition:.2s ease;
    }

    .refresh-btn:hover{
        transform:rotate(90deg);
    }

    /* =========================
       BUTTON
    ========================= */

    .btn-login{
        width:100%;

        border:none;

        border-radius:16px;

        padding:14px;

        font-size:14px;
        font-weight:700;

        color:#fff;

        cursor:pointer;

        transition:.2s ease;

        background:
            linear-gradient(
                135deg,
                #60a5fa,
                #2563eb
            );

        box-shadow:
            0 12px 24px rgba(37,99,235,.20);
    }

    .btn-login:hover{
        transform:translateY(-2px);
    }

    .btn-login:active{
        transform:translateY(0);
    }

    /* =========================
       FOOTER
    ========================= */

    .security-note{
        margin-top:22px;

        text-align:center;

        font-size:11px;

        color:#64748b;

        line-height:1.7;
    }

    .security-note span{
        color:#2563eb;
        font-weight:700;
    }

    /* =========================
       MOBILE
    ========================= */

    @media(max-width:576px){

        body{
            padding:14px;
        }

        .glass-container{
            padding:10px;
            border-radius:22px;
        }

        .glass-inner{
            padding:26px 20px;
            border-radius:18px;
        }

        .login-header h1{
            font-size:20px;
        }

        .captcha-group{
            flex-wrap:wrap;
        }

        .captcha-question{
            width:100%;
        }

        .refresh-btn{
            width:100%;
            height:46px;
        }

        .bg-bubble{
            display:none;
        }
    }

    </style>

</head>

<body>

    <div class="bg-bubble one"></div>
    <div class="bg-bubble two"></div>

    <div class="login-wrapper">

        <div class="glass-container">

            <div class="glass-inner">

                <div class="login-header">

                    <div class="logo-image">

                        <img
                            src="https://i.ibb.co.com/T9RmYQN/Whats-App-Image-2026-05-18-at-16-25-09.jpg"
                            alt="Apotek Ananda Jadimulya"
                        >

                    </div>

                    <h1>Apotek Ananda Jadimulya</h1>

                    <p>
                        Sistem Informasi Manajemen
                        <br>
                        Stok & Faktur Obat
                    </p>

                </div>

                <?php if ($flash): ?>

                    <div class="alert alert-<?= $flash['type'] ?>">

                        <?= htmlspecialchars($flash['message']) ?>

                    </div>

                <?php endif; ?>

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/backend/controllers/auth_controller.php?action=login"
                    autocomplete="off"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken) ?>"
                    >

                    <div class="form-group">

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Masukkan username"
                            required
                            autofocus
                        >

                    </div>

                    <div class="form-group">

                        <label>Password</label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label>Verifikasi CAPTCHA</label>

                        <div class="captcha-group">

                            <span
                                class="captcha-question"
                                id="captchaQuestion"
                            >
                                <?= htmlspecialchars($captchaQuestion) ?>
                            </span>

                            <button
                                type="button"
                                class="refresh-btn"
                                onclick="refreshCaptcha()"
                                title="Refresh CAPTCHA"
                            >
                                ↻
                            </button>

                            <input
                                type="text"
                                name="captcha"
                                class="form-control captcha-input"
                                placeholder="Jawaban"
                                required
                                autocomplete="off"
                            >

                        </div>

                    </div>

                    <button type="submit" class="btn-login">
                        🔐 Masuk ke Sistem
                    </button>

                </form>

                <div class="security-note">

                    🔒 Dilindungi oleh
                    <span>CAPTCHA</span>
                    &
                    <span>CSRF Protection</span>

                </div>

            </div>

        </div>

    </div>

    <script>

    function refreshCaptcha(){

        fetch(
            '<?= BASE_URL ?>/captcha.php?refresh=1&_=' + Date.now()
        )

        .then(res => res.json())

        .then(data => {

            document.getElementById('captchaQuestion')
                .textContent = data.question;
        })

        .catch(err => {

            console.error(
                'Captcha refresh error:',
                err
            );
        });
    }

    </script>

</body>
</html>
