<?php
/**
 * Model: Log Aktivitas - Apotek Ananda Jadimulya
 * Query layer untuk tabel `log_aktivitas`.
 * Merekam semua aktivitas user secara otomatis.
 */

require_once __DIR__ . '/../config/database.php';

class LogAktivitas {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Catat log aktivitas baru
     */
    public function catat(int $userId, string $aksi, string $keterangan): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO log_aktivitas (id_user, aksi, keterangan, created_at) VALUES (:id_user, :aksi, :keterangan, NOW())"
        );
        return $stmt->execute([
            'id_user'    => $userId,
            'aksi'       => $aksi,
            'keterangan' => $keterangan
        ]);
    }
    
    /**
     * Ambil semua log aktivitas dengan filter
     */
    public function getAll(?int $limit = null, ?string $role = null, ?string $date = null, ?string $aksi = null): array {
        $sql = "SELECT la.*, COALESCE(u.nama_lengkap, '(User dihapus)') AS nama_lengkap, COALESCE(u.role, 'unknown') AS role 
                FROM log_aktivitas la 
                LEFT JOIN users u ON la.id_user = u.id_user";
        
        $params = [];
        $conditions = [];
        
        if ($role) {
            $conditions[] = "u.role = :role";
            $params['role'] = $role;
        }
        
        if ($date) {
            $conditions[] = "DATE(la.created_at) = :date";
            $params['date'] = $date;
        }
        
        if ($aksi) {
            $conditions[] = "la.aksi = :aksi";
            $params['aksi'] = $aksi;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY la.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Ambil daftar aksi unik untuk filter
     */
    public function getUniqueActions(): array {
        $stmt = $this->db->query("SELECT DISTINCT aksi FROM log_aktivitas ORDER BY aksi ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Ambil log berdasarkan user tertentu
     */
    public function getByUser(int $userId, ?int $limit = null): array {
        $sql = "SELECT la.*, COALESCE(u.nama_lengkap, '(User dihapus)') AS nama_lengkap, COALESCE(u.role, 'unknown') AS role 
                FROM log_aktivitas la 
                LEFT JOIN users u ON la.id_user = u.id_user 
                WHERE la.id_user = :uid 
                ORDER BY la.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :lim";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['uid' => $userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Ambil log terbaru untuk dashboard widget
     */
    public function getRecent(int $limit = 10): array {
        return $this->getAll($limit);
    }
    
    /**
     * Hitung total log
     */
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM log_aktivitas")->fetchColumn();
    }
}
