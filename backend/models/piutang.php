<?php
/**
 * Model: Piutang - Apotek Ananda Jadimulya
 * Query layer untuk tabel `piutang`.
 */

require_once __DIR__ . '/../config/database.php';

class Piutang {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Ambil semua piutang dengan filter opsional
     */
    public function getAll(?string $status = null, ?string $bulan = null, ?string $search = null): array {
        $sql = "SELECT pi.*, u.nama_lengkap AS created_by FROM piutang pi LEFT JOIN users u ON pi.id_user = u.id_user";
        $params = [];
        $conditions = [];
        
        if ($status !== null && in_array($status, ['lunas', 'belum_lunas'])) {
            $conditions[] = "pi.status = :status";
            $params['status'] = $status;
        }
        
        if ($bulan !== null && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $conditions[] = "DATE_FORMAT(pi.tanggal_faktur, '%Y-%m') = :bulan";
            $params['bulan'] = $bulan;
        }
        
        if ($search !== null && $search !== '') {
            $conditions[] = "(pi.no_faktur LIKE :search OR pi.nama_pbf LIKE :search2)";
            $params['search']  = "%$search%";
            $params['search2'] = "%$search%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY pi.tanggal_jatuh_tempo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT pi.*, u.nama_lengkap AS created_by FROM piutang pi LEFT JOIN users u ON pi.id_user = u.id_user WHERE pi.id_piutang = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function create(array $data, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO piutang (no_faktur, nama_pbf, tanggal_faktur, tanggal_jatuh_tempo, jumlah_harga, status, bukti_pembayaran, id_user) 
             VALUES (:no_faktur, :nama_pbf, :tanggal_faktur, :tanggal_jatuh_tempo, :jumlah_harga, :status, :bukti, :id_user)"
        );
        $stmt->execute([
            'no_faktur'           => $data['no_faktur'],
            'nama_pbf'            => $data['nama_pbf'],
            'tanggal_faktur'      => $data['tanggal_faktur'],
            'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
            'jumlah_harga'        => $data['jumlah_harga'],
            'status'              => $data['status'] ?? 'belum_lunas',
            'bukti'               => $data['bukti_pembayaran'] ?? null,
            'id_user'             => $userId
        ]);
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE piutang SET 
                no_faktur = :no_faktur, nama_pbf = :nama_pbf, tanggal_faktur = :tanggal_faktur,
                tanggal_jatuh_tempo = :tanggal_jatuh_tempo, jumlah_harga = :jumlah_harga
             WHERE id_piutang = :id"
        );
        return $stmt->execute([
            'no_faktur'           => $data['no_faktur'],
            'nama_pbf'            => $data['nama_pbf'],
            'tanggal_faktur'      => $data['tanggal_faktur'],
            'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
            'jumlah_harga'        => $data['jumlah_harga'],
            'id'                  => $id
        ]);
    }
    
    /**
     * Lunasi piutang: set status lunas + tanggal + bukti pembayaran
     */
    public function lunasi(int $id, ?string $buktiPath = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE piutang SET status = 'lunas', tanggal_lunas = CURDATE(), bukti_pembayaran = :bukti WHERE id_piutang = :id"
        );
        return $stmt->execute(['bukti' => $buktiPath, 'id' => $id]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM piutang WHERE id_piutang = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Ringkasan total piutang per status
     */
    public function getSummary(?string $bulan = null): array {
        $sql = "SELECT 
                    COUNT(*) AS total_records,
                    COALESCE(SUM(jumlah_harga), 0) AS total_semua,
                    COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah_harga ELSE 0 END), 0) AS total_lunas,
                    COALESCE(SUM(CASE WHEN status = 'belum_lunas' THEN jumlah_harga ELSE 0 END), 0) AS total_belum_lunas,
                    COUNT(CASE WHEN status = 'lunas' THEN 1 END) AS count_lunas,
                    COUNT(CASE WHEN status = 'belum_lunas' THEN 1 END) AS count_belum_lunas
                FROM piutang";
        $params = [];
        
        if ($bulan !== null && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $sql .= " WHERE DATE_FORMAT(tanggal_faktur, '%Y-%m') = :bulan";
            $params['bulan'] = $bulan;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Daftar bulan yang ada datanya (untuk filter dropdown)
     */
    public function getAvailableMonths(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT DATE_FORMAT(tanggal_faktur, '%Y-%m') AS bulan 
             FROM piutang ORDER BY bulan DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM piutang")->fetchColumn();
    }
}
