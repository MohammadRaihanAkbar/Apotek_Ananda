<?php
/**
 * Model: Stok Masuk - Apotek Ananda Jadimulya
 * Query layer untuk tabel `stok_masuk`.
 */

require_once __DIR__ . '/../config/database.php';

class StokMasuk {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Ambil semua stok masuk (global) dengan join PBF
     */
    public function getAll(?int $pbfId = null, ?string $search = null): array {
        $sql = "SELECT sm.*, p.nama_pbf 
                FROM stok_masuk sm 
                JOIN pbf p ON sm.id_pbf = p.id_pbf";
        $params = [];
        $conditions = [];
        
        if ($pbfId !== null) {
            $conditions[] = "sm.id_pbf = :pbf_id";
            $params['pbf_id'] = $pbfId;
        }
        
        if ($search !== null && $search !== '') {
            $conditions[] = "(sm.nama_obat LIKE :search OR p.nama_pbf LIKE :search2 OR sm.no_faktur LIKE :search3)";
            $params['search']  = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY sm.tanggal_masuk DESC, sm.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Cari stok masuk berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT sm.*, p.nama_pbf 
             FROM stok_masuk sm 
             JOIN pbf p ON sm.id_pbf = p.id_pbf 
             WHERE sm.id_masuk = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Tambah stok masuk baru
     */
    public function create(array $data): int {
        $total = ($data['harga_beli'] * (1 - ($data['discount'] / 100))) * $data['jumlah_masuk'];
        
        $stmt = $this->db->prepare(
            "INSERT INTO stok_masuk (id_pbf, no_faktur, tanggal_masuk, nama_obat, satuan, batch, expired_date, harga_beli, discount, jumlah_masuk, total) 
             VALUES (:id_pbf, :no_faktur, :tanggal_masuk, :nama_obat, :satuan, :batch, :expired_date, :harga_beli, :discount, :jumlah_masuk, :total)"
        );
        $stmt->execute([
            'id_pbf'        => $data['id_pbf'],
            'no_faktur'     => $data['no_faktur'],
            'tanggal_masuk' => $data['tanggal_masuk'],
            'nama_obat'     => $data['nama_obat'],
            'satuan'        => $data['satuan'],
            'batch'         => $data['batch'] ?? null,
            'expired_date'  => $data['expired_date'],
            'harga_beli'    => $data['harga_beli'],
            'discount'      => $data['discount'],
            'jumlah_masuk'  => $data['jumlah_masuk'],
            'total'         => $total
        ]);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update stok masuk
     */
    public function update(int $id, array $data): bool {
        $total = ($data['harga_beli'] * (1 - ($data['discount'] / 100))) * $data['jumlah_masuk'];
        
        $stmt = $this->db->prepare(
            "UPDATE stok_masuk SET 
                id_pbf = :id_pbf, no_faktur = :no_faktur, tanggal_masuk = :tanggal_masuk,
                nama_obat = :nama_obat, satuan = :satuan, batch = :batch, expired_date = :expired_date,
                harga_beli = :harga_beli, discount = :discount, jumlah_masuk = :jumlah_masuk, total = :total
             WHERE id_masuk = :id"
        );
        return $stmt->execute([
            'id_pbf'        => $data['id_pbf'],
            'no_faktur'     => $data['no_faktur'],
            'tanggal_masuk' => $data['tanggal_masuk'],
            'nama_obat'     => $data['nama_obat'],
            'satuan'        => $data['satuan'],
            'batch'         => $data['batch'] ?? null,
            'expired_date'  => $data['expired_date'],
            'harga_beli'    => $data['harga_beli'],
            'discount'      => $data['discount'],
            'jumlah_masuk'  => $data['jumlah_masuk'],
            'total'         => $total,
            'id'            => $id
        ]);
    }
    
    /**
     * Update hanya kolom batch untuk stok masuk
     */
    public function updateBatch(int $id, string $batch): bool {
        $stmt = $this->db->prepare("UPDATE stok_masuk SET batch = :batch WHERE id_masuk = :id");
        return $stmt->execute(['batch' => $batch, 'id' => $id]);
    }

    /**
     * Hapus stok masuk (Super Admin only)
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM stok_masuk WHERE id_masuk = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Hitung total stok (jumlah seluruh unit obat)
     */
    public function getTotalStok(): int {
        return (int) $this->db->query("SELECT COALESCE(SUM(jumlah_masuk), 0) FROM stok_masuk")->fetchColumn();
    }
    
    /**
     * Obat yang expiring dalam N hari ke depan
     */
    public function getExpiringStock(int $days = 60): array {
        $stmt = $this->db->prepare(
            "SELECT sm.*, p.nama_pbf 
             FROM stok_masuk sm 
             JOIN pbf p ON sm.id_pbf = p.id_pbf 
             WHERE sm.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY sm.expired_date ASC"
        );
        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obat yang expired dalam 6 bulan (untuk laporan otomatis)
     */
    public function getExpiringSixMonths(): array {
        $stmt = $this->db->query(
            "SELECT sm.*, p.nama_pbf 
             FROM stok_masuk sm 
             JOIN pbf p ON sm.id_pbf = p.id_pbf 
             WHERE sm.expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
             AND sm.expired_date >= CURDATE()
             ORDER BY sm.expired_date ASC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Hitung unit expiring dalam N hari
     */
    public function countExpiringStock(int $days = 60): int {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(jumlah_masuk), 0) FROM stok_masuk 
             WHERE expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)"
        );
        $stmt->execute(['days' => $days]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Hitung unit expiring dalam 6 bulan (Konsisten dengan tabel)
     */
    public function countExpiringSixMonths(): int {
        return (int) $this->db->query(
            "SELECT COALESCE(SUM(jumlah_masuk), 0) FROM stok_masuk 
             WHERE expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
             AND expired_date >= CURDATE()"
        )->fetchColumn();
    }
    
    /**
     * Hitung total record stok
     */
    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM stok_masuk")->fetchColumn();
    }
}
