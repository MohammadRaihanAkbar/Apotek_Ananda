<?php
/**
 * API Search Controller - Apotek Ananda Jadimulya
 * Endpoint untuk AJAX autocomplete (rekomendasi pencarian).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';

// Cek autentikasi
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$term = $_GET['term'] ?? '';

if (empty($term) || strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$db = getDBConnection();
$results = [];

try {
    $termLike = "%$term%";
    
    switch ($type) {
        case 'stok_obat':
            // Cari nama obat atau no faktur di stok_masuk
            $stmt = $db->prepare("
                SELECT DISTINCT nama_obat AS label, nama_obat AS value 
                FROM stok_masuk 
                WHERE nama_obat LIKE :term 
                UNION 
                SELECT DISTINCT no_faktur AS label, no_faktur AS value 
                FROM stok_masuk 
                WHERE no_faktur LIKE :term
                LIMIT 10
            ");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'piutang':
            // Cari no faktur atau nama pbf di piutang
            $stmt = $db->prepare("
                SELECT DISTINCT no_faktur AS label, no_faktur AS value 
                FROM piutang 
                WHERE no_faktur LIKE :term 
                UNION 
                SELECT DISTINCT nama_pbf AS label, nama_pbf AS value 
                FROM piutang 
                WHERE nama_pbf LIKE :term
                LIMIT 10
            ");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'expired':
            // Cari nama obat di obat_expired atau stok_masuk
            $stmt = $db->prepare("
                SELECT DISTINCT nama_obat AS label, nama_obat AS value 
                FROM obat_expired 
                WHERE nama_obat LIKE :term 
                UNION 
                SELECT DISTINCT nama_obat AS label, nama_obat AS value 
                FROM stok_masuk 
                WHERE nama_obat LIKE :term
                LIMIT 10
            ");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
