<?php
/**
 * Model: PBF (Pedagang Besar Farmasi) - Apotek Ananda Jadimulya
 * Query layer untuk tabel `pbf`.
 */

require_once __DIR__ . '/../config/database.php';

class PBF {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT p.*, u.nama_lengkap AS created_by 
             FROM pbf p LEFT JOIN users u ON p.id_user = u.id_user 
             ORDER BY p.nama_pbf ASC"
        );
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nama_lengkap AS created_by 
             FROM pbf p LEFT JOIN users u ON p.id_user = u.id_user 
             WHERE p.id_pbf = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function create(string $namaPbf, int $userId): int {
        $stmt = $this->db->prepare("INSERT INTO pbf (nama_pbf, id_user) VALUES (:nama, :user_id)");
        $stmt->execute(['nama' => $namaPbf, 'user_id' => $userId]);
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, string $namaPbf): bool {
        $stmt = $this->db->prepare("UPDATE pbf SET nama_pbf = :nama WHERE id_pbf = :id");
        return $stmt->execute(['nama' => $namaPbf, 'id' => $id]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM pbf WHERE id_pbf = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function nameExists(string $namaPbf, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pbf WHERE nama_pbf = :nama AND id_pbf != :id");
            $stmt->execute(['nama' => $namaPbf, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pbf WHERE nama_pbf = :nama");
            $stmt->execute(['nama' => $namaPbf]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }
    
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM pbf")->fetchColumn();
    }
    
    public function countStokByPBF(int $id): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM stok_masuk WHERE id_pbf = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }
}
