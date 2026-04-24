<?php
/**
 * Model: Obat Expired - Apotek Ananda Jadimulya
 * Query layer untuk tabel `obat_expired` (input manual).
 */

require_once __DIR__ . '/../config/database.php';

class ObatExpired {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Ambil semua data obat expired (input manual)
     */
    public function getAll(?string $search = null): array {
        $sql = "SELECT oe.*, u.nama_lengkap AS created_by 
                FROM obat_expired oe 
                LEFT JOIN users u ON oe.id_user = u.id_user";
        $params = [];
        
        if ($search !== null && $search !== '') {
            $sql .= " WHERE (oe.nama_obat LIKE :search OR oe.nama_pbf LIKE :search2 OR oe.batch LIKE :search3)";
            $params['search']  = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        
        $sql .= " ORDER BY oe.expired_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Cari berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT oe.*, u.nama_lengkap AS created_by 
             FROM obat_expired oe 
             LEFT JOIN users u ON oe.id_user = u.id_user 
             WHERE oe.id_expired = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Tambah data expired manual
     */
    public function create(array $data, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO obat_expired (nama_obat, qty, satuan, batch, expired_date, harga_beli, nama_pbf, id_user) 
             VALUES (:nama_obat, :qty, :satuan, :batch, :expired_date, :harga_beli, :nama_pbf, :id_user)"
        );
        $stmt->execute([
            'nama_obat'    => $data['nama_obat'],
            'qty'          => $data['qty'],
            'satuan'       => $data['satuan'],
            'batch'        => $data['batch'] ?? null,
            'expired_date' => $data['expired_date'],
            'harga_beli'   => $data['harga_beli'],
            'nama_pbf'     => $data['nama_pbf'] ?? null,
            'id_user'      => $userId
        ]);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update data expired manual
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE obat_expired SET 
                nama_obat = :nama_obat, qty = :qty, satuan = :satuan, batch = :batch,
                expired_date = :expired_date, harga_beli = :harga_beli, nama_pbf = :nama_pbf
             WHERE id_expired = :id"
        );
        return $stmt->execute([
            'nama_obat'    => $data['nama_obat'],
            'qty'          => $data['qty'],
            'satuan'       => $data['satuan'],
            'batch'        => $data['batch'] ?? null,
            'expired_date' => $data['expired_date'],
            'harga_beli'   => $data['harga_beli'],
            'nama_pbf'     => $data['nama_pbf'] ?? null,
            'id'           => $id
        ]);
    }
    
    /**
     * Hapus data expired
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM obat_expired WHERE id_expired = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Laporan gabungan: data manual + otomatis dari stok_masuk (<= 6 bulan)
     */
    public function getCombinedExpiredReport(?string $search = null): array {
        $sql = "
            SELECT 'manual' AS sumber, oe.nama_obat, oe.qty, oe.satuan, oe.batch, 
                   oe.expired_date, oe.harga_beli, oe.nama_pbf, oe.id_expired AS id, NULL AS id_masuk
            FROM obat_expired oe
        ";
        
        $sql .= " UNION ALL ";
        
        $sql .= "
            SELECT 'otomatis' AS sumber, sm.nama_obat, sm.jumlah_masuk AS qty, sm.satuan, sm.batch,
                   sm.expired_date, sm.harga_beli, p.nama_pbf, NULL AS id, sm.id_masuk
            FROM stok_masuk sm 
            JOIN pbf p ON sm.id_pbf = p.id_pbf 
            WHERE sm.expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
        ";
        
        if ($search !== null && $search !== '') {
            // Wrap in subquery for search
            $sql = "SELECT * FROM ($sql) AS combined 
                    WHERE nama_obat LIKE :search OR nama_pbf LIKE :search2
                    ORDER BY expired_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['search' => "%$search%", 'search2' => "%$search%"]);
        } else {
            $sql = "SELECT * FROM ($sql) AS combined ORDER BY expired_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM obat_expired")->fetchColumn();
    }

    /**
     * Statistik ringkasan untuk dashboard laporan kadaluwarsa
     */
    public function getSummaryStats(): array {
        // Gabungan data untuk statistik
        $data = $this->getCombinedExpiredReport();
        
        $stats = [
            'expired_count' => 0,
            'nearing_count' => 0,
            'potential_loss' => 0
        ];
        
        $today = new DateTime();
        $nearingDate = (new DateTime())->modify('+30 days');
        
        foreach ($data as $item) {
            $expDate = new DateTime($item['expired_date']);
            
            if ($expDate <= $today) {
                $stats['expired_count']++;
            } elseif ($expDate <= $nearingDate) {
                $stats['nearing_count']++;
            }
            
            // Potential loss dihitung dari barang yang sudah expired atau hampir expired (6 bulan kedepan)
            $stats['potential_loss'] += ($item['qty'] * $item['harga_beli']);
        }
        
        return $stats;
    }
}
