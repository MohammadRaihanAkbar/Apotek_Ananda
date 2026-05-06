<?php
/**
 * Logout Handler - Apotek Ananda Jadimulya
 * Redirect ke auth_controller logout action.
 */

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/session_helper.php';

// Redirect ke single logout implementation di auth_controller
header('Location: ' . BASE_URL . '/backend/controllers/auth_controller.php?action=logout');
exit;
