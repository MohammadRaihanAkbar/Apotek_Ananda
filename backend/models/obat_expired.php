<?php
/**
 * Model: Laporan Expired - otomatis dari obat_batch.
 */

require_once __DIR__ . '/../config/database.php';

class ObatExpired {
    private PDO $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    public function getExpiredReport(array $filters = []): array {
        $sql = "SELECT
                    MIN(ob.id_batch) AS id_batch,
                    ofa.id_obat_faktur,
                    ofa.nama_obat,
                    ofa.jenis_obat,
                    ofa.satuan,
                    ofa.harga_beli,
                    COUNT(ob.id_batch) AS qty,
                    ob.no_batch AS batch,
                    ob.expired_date,
                    DATEDIFF(ob.expired_date, CURDATE()) AS sisa_hari,
                    p.nama_pbf,
                    f.no_faktur,
                    f.tanggal_faktur,
                    f.tanggal_masuk,
                    'otomatis' AS sumber
                FROM obat_batch ob
                JOIN obat_faktur ofa ON ob.id_obat_faktur = ofa.id_obat_faktur
                JOIN faktur f ON ofa.id_faktur = f.id_faktur
                JOIN pbf p ON f.id_pbf = p.id_pbf";
        $params = [];
        $conditions = [];

        // Default otomatis: semua batch yang sudah expired atau akan expired <= 6 bulan dari hari ini.
        $conditions[] = "ob.expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)";

        if (!empty($filters['pbf_id'])) {
            $conditions[] = "f.id_pbf = :pbf_id";
            $params['pbf_id'] = (int) $filters['pbf_id'];
        }

        if (!empty($filters['nama_obat'])) {
            $conditions[] = "ofa.nama_obat LIKE :nama_obat";
            $params['nama_obat'] = '%' . $filters['nama_obat'] . '%';
        }

        if (!empty($filters['date_start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_start'])) {
            $conditions[] = "ob.expired_date >= :date_start";
            $params['date_start'] = $filters['date_start'];
        }

        if (!empty($filters['date_end']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_end'])) {
            $conditions[] = "ob.expired_date <= :date_end";
            $params['date_end'] = $filters['date_end'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'expired') {
                $conditions[] = "ob.expired_date < CURDATE()";
            } elseif ($filters['status'] === 'segera_expired') {
                $conditions[] = "ob.expired_date >= CURDATE()";
            }
        }

        $sql .= " WHERE " . implode(' AND ', $conditions);
        $sql .= " GROUP BY ofa.id_obat_faktur, ofa.nama_obat, ofa.jenis_obat, ofa.satuan, ofa.harga_beli, ob.no_batch, ob.expired_date, p.nama_pbf, f.no_faktur, f.tanggal_faktur, f.tanggal_masuk
                  ORDER BY ob.expired_date ASC, ofa.nama_obat ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCombinedExpiredReport(?string $search = null): array {
        return $this->getExpiredReport(['nama_obat' => $search]);
    }

    public function getSummaryStats(array $filters = []): array {
        $data = $this->getExpiredReport($filters);
        $stats = [
            'expired_count' => 0,
            'six_month_count' => 0,
            'potential_loss' => 0,
        ];

        foreach ($data as $item) {
            if ((int)$item['sisa_hari'] < 0) {
                $stats['expired_count'] += (int)$item['qty'];
            } else {
                $stats['six_month_count'] += (int)$item['qty'];
            }
            $stats['potential_loss'] += ((int)$item['qty'] * (float)$item['harga_beli']);
        }
        return $stats;
    }

    public function count(): int {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM obat_batch WHERE expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)"
        )->fetchColumn();
    }
}
