<?php
/**
 * API Search Controller - autocomplete.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/session_helper.php';

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
            $stmt = $db->prepare("SELECT DISTINCT nama_obat AS label, nama_obat AS value FROM obat_faktur WHERE nama_obat LIKE :term
                                  UNION SELECT DISTINCT no_faktur AS label, no_faktur AS value FROM faktur WHERE no_faktur LIKE :term LIMIT 10");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'piutang':
            $stmt = $db->prepare("SELECT DISTINCT f.no_faktur AS label, f.no_faktur AS value FROM faktur f WHERE f.no_faktur LIKE :term
                                  UNION SELECT DISTINCT p.nama_pbf AS label, p.nama_pbf AS value FROM faktur f JOIN pbf p ON f.id_pbf = p.id_pbf WHERE p.nama_pbf LIKE :term LIMIT 10");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'expired':
            $stmt = $db->prepare("SELECT DISTINCT ofa.nama_obat AS label, ofa.nama_obat AS value
                                  FROM obat_faktur ofa
                                  JOIN obat_batch ob ON ofa.id_obat_faktur = ob.id_obat_faktur
                                  WHERE ofa.nama_obat LIKE :term
                                  LIMIT 10");
            $stmt->execute(['term' => $termLike]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
