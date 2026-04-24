<?php
/**
 * CAPTCHA Helper - Apotek Ananda Jadimulya
 * 
 * Menggunakan PHP GD Library untuk generate CAPTCHA image.
 * CAPTCHA diterapkan pada halaman login untuk mencegah brute-force attack.
 */

/**
 * Generate kode CAPTCHA acak (huruf + angka, tanpa karakter ambigu)
 * 
 * @param int $length Panjang kode
 * @return string Kode CAPTCHA
 */
function generateCaptchaCode(int $length = 5): string {
    // Hindari karakter yang mirip: 0/O, 1/I/l, 2/Z, 5/S, 8/B
    $characters = 'ABCDEFGHJKLMNPQRTUVWXY3467';
    $code = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $max)];
    }
    
    return $code;
}

/**
 * Simpan kode CAPTCHA ke session
 * 
 * @param string $code Kode CAPTCHA
 */
function storeCaptchaCode(string $code): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['captcha_code'] = strtoupper($code);
    $_SESSION['captcha_time'] = time();
}

/**
 * Validasi input CAPTCHA terhadap kode di session
 * 
 * @param string $input Input user
 * @return bool True jika cocok
 */
function validateCaptcha(string $input): bool {
    if (!defined('CAPTCHA_ENABLED') || !CAPTCHA_ENABLED) {
        return true; // Skip jika CAPTCHA disabled
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($input) || !isset($_SESSION['captcha_code'])) {
        return false;
    }
    
    // CAPTCHA expired setelah 5 menit
    if (isset($_SESSION['captcha_time']) && (time() - $_SESSION['captcha_time']) > 300) {
        unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
        return false;
    }
    
    $valid = strtoupper(trim($input)) === $_SESSION['captcha_code'];
    
    // Hapus CAPTCHA setelah validasi (one-time use)
    unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);
    
    return $valid;
}

/**
 * Generate CAPTCHA image menggunakan GD Library
 * Output langsung sebagai PNG image
 */
function renderCaptchaImage(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $code = generateCaptchaCode(5);
    storeCaptchaCode($code);
    
    $width = 180;
    $height = 60;
    
    // Buat canvas
    $image = imagecreatetruecolor($width, $height);
    
    // Anti-aliasing
    imageantialias($image, true);
    
    // Warna
    $bgColor     = imagecolorallocate($image, 240, 244, 248);   // Light gray
    $textColor   = imagecolorallocate($image, 44, 62, 80);       // Dark blue
    $noiseColor1 = imagecolorallocate($image, 100, 149, 237);    // Cornflower
    $noiseColor2 = imagecolorallocate($image, 170, 180, 190);    // Gray
    $lineColor   = imagecolorallocate($image, 52, 152, 219);     // Blue
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Tambah garis acak (noise)
    for ($i = 0; $i < 6; $i++) {
        $color = ($i % 2 === 0) ? $noiseColor1 : $lineColor;
        imageline($image, 
            random_int(0, $width), random_int(0, $height),
            random_int(0, $width), random_int(0, $height),
            $color
        );
    }
    
    // Tambah titik acak (noise)
    for ($i = 0; $i < 100; $i++) {
        imagesetpixel($image, 
            random_int(0, $width), 
            random_int(0, $height), 
            $noiseColor2
        );
    }
    
    // Render teks CAPTCHA karakter per karakter
    $fontSize = 5; // Built-in font size (1-5)
    $charWidth = imagefontwidth($fontSize);
    $charHeight = imagefontheight($fontSize);
    $textWidth = $charWidth * strlen($code);
    $startX = ($width - $textWidth) / 2;
    $startY = ($height - $charHeight) / 2;
    
    for ($i = 0; $i < strlen($code); $i++) {
        $x = $startX + ($i * $charWidth) + random_int(-2, 2);
        $y = $startY + random_int(-5, 5);
        
        // Warna bervariasi per karakter
        $r = random_int(20, 80);
        $g = random_int(20, 80);
        $b = random_int(80, 160);
        $charColor = imagecolorallocate($image, $r, $g, $b);
        
        imagechar($image, $fontSize, (int)$x, (int)$y, $code[$i], $charColor);
    }
    
    // Tambah border halus
    $borderColor = imagecolorallocate($image, 189, 195, 199);
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
    
    // Output image
    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    imagepng($image);
    imagedestroy($image);
}
