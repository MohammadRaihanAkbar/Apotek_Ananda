<?php
/**
 * CAPTCHA Image Endpoint - Apotek Ananda Jadimulya
 * Generates a CAPTCHA image dan outputs sebagai PNG.
 * URL: /ApotekAnanda/captcha.php
 */

require_once __DIR__ . '/backend/helpers/captcha_helper.php';

renderCaptchaImage();
