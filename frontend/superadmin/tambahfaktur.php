<?php
require_once __DIR__ . '/../../backend/helpers/session_helper.php';
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
redirect(BASE_URL . '/frontend/superadmin/tambah_faktur.php' . $query);
