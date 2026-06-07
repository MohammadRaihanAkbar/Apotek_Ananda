<?php
/**
 * Model: Piutang - otomatis bersumber dari tabel faktur.
 */

require_once __DIR__ . '/../config/database.php';

class Piutang {
    private PDO $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    private function baseSelectSql(): string {
        return "SELECT
                    f.id_faktur,
                    f.id_faktur AS id_piutang,
                    f.no_faktur,
                    f.id_pbf,
                    p.nama_pbf,
                    f.tanggal_faktur,
                    f.tanggal_jatuh_tempo,
                    f.status_bayar AS status,
                    f.status_bayar AS status_pembayaran,
                    f.tanggal_lunas,
                    f.bukti_pembayaran,
                    f.created_at,
                    u.nama_lengkap AS created_by,
                    COUNT(ofa.id_obat_faktur) AS jumlah_item,
                    COALESCE(SUM(ofa.total), 0) AS jumlah_harga
                FROM faktur f
                JOIN pbf p ON f.id_pbf = p.id_pbf
                LEFT JOIN users u ON f.id_user = u.id_user
                LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur";
    }

    private function groupOrderSql(): string {
        return " GROUP BY f.id_faktur, f.no_faktur, f.id_pbf, p.nama_pbf, f.tanggal_faktur,
                         f.tanggal_jatuh_tempo, f.status_bayar, f.tanggal_lunas,
                         f.bukti_pembayaran, f.created_at, u.nama_lengkap
                  ORDER BY f.created_at DESC";
    }

    private function buildFilterConditions(array $filters, array &$params, string $prefix = 'filter'): array {
        $conditions = [];

        if (!empty($filters['pbf']) && (int)$filters['pbf'] > 0) {
            $conditions[] = "f.id_pbf = :{$prefix}_pbf";
            $params["{$prefix}_pbf"] = (int)$filters['pbf'];
        }

        if (!empty($filters['bulan']) && preg_match('/^\d{4}-\d{2}$/', $filters['bulan'])) {
            $conditions[] = "DATE_FORMAT(f.tanggal_faktur, '%Y-%m') = :{$prefix}_bulan";
            $params["{$prefix}_bulan"] = $filters['bulan'];
        }

        if (!empty($filters['tempo'])) {
            switch ($filters['tempo']) {
                case 'no_due_date':
                    $conditions[] = "f.status_bayar = 'belum_lunas' AND f.tanggal_jatuh_tempo IS NULL";
                    break;
                case 'overdue':
                    $conditions[] = "f.status_bayar = 'belum_lunas' AND f.tanggal_jatuh_tempo IS NOT NULL AND f.tanggal_jatuh_tempo < CURDATE()";
                    break;
                case 'today':
                    $conditions[] = "f.status_bayar = 'belum_lunas' AND f.tanggal_jatuh_tempo = CURDATE()";
                    break;
                case 'due_soon':
                    $conditions[] = "f.status_bayar = 'belum_lunas' AND f.tanggal_jatuh_tempo IS NOT NULL AND f.tanggal_jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    break;
                case 'safe':
                    $conditions[] = "f.status_bayar = 'belum_lunas' AND f.tanggal_jatuh_tempo IS NOT NULL AND f.tanggal_jatuh_tempo > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
        }

        return $conditions;
    }

    public function getAllByStatus(string $status, ?string $search = null, array $filters = []): array {
        $sql = $this->baseSelectSql() . " WHERE f.status_bayar = :status";
        $params = ['status' => $status];

        if ($search !== null && $search !== '') {
            $sql .= " AND (f.no_faktur LIKE :search_faktur OR p.nama_pbf LIKE :search_pbf OR ofa.nama_obat LIKE :search_obat)";
            $likeSearch = "%$search%";
            $params['search_faktur'] = $likeSearch;
            $params['search_pbf'] = $likeSearch;
            $params['search_obat'] = $likeSearch;
        }

        foreach ($this->buildFilterConditions($filters, $params) as $condition) {
            $sql .= " AND " . $condition;
        }

        $sql .= $this->groupOrderSql();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(?string $status = null, ?string $bulan = null, ?string $search = null, array $filters = []): array {
        if ($bulan !== null && $bulan !== '') {
            $filters['bulan'] = $bulan;
        }

        $sql = $this->baseSelectSql();
        $params = [];
        $conditions = [];

        if ($status !== null && in_array($status, ['lunas', 'belum_lunas'], true)) {
            $conditions[] = "f.status_bayar = :status";
            $params['status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $conditions[] = "(f.no_faktur LIKE :search_faktur OR p.nama_pbf LIKE :search_pbf OR ofa.nama_obat LIKE :search_obat)";
            $likeSearch = "%$search%";
            $params['search_faktur'] = $likeSearch;
            $params['search_pbf'] = $likeSearch;
            $params['search_obat'] = $likeSearch;
        }

        $conditions = array_merge($conditions, $this->buildFilterConditions($filters, $params));

        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= $this->groupOrderSql();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT
                f.id_faktur,
                f.id_faktur AS id_piutang,
                f.no_faktur,
                f.id_pbf,
                p.nama_pbf,
                f.tanggal_faktur,
                f.tanggal_jatuh_tempo,
                f.status_bayar AS status,
                f.status_bayar AS status_pembayaran,
                f.tanggal_lunas,
                f.bukti_pembayaran,
                COALESCE(SUM(ofa.total), 0) AS jumlah_harga
             FROM faktur f
             JOIN pbf p ON f.id_pbf = p.id_pbf
             LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur
             WHERE f.id_faktur = :id
             GROUP BY f.id_faktur, f.no_faktur, f.id_pbf, p.nama_pbf, f.tanggal_faktur,
                      f.tanggal_jatuh_tempo, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function lunasi(int $id, ?string $buktiPath = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE faktur
             SET status_bayar = 'lunas', tanggal_lunas = CURDATE(), bukti_pembayaran = COALESCE(:bukti, bukti_pembayaran)
             WHERE id_faktur = :id"
        );
        return $stmt->execute(['bukti' => $buktiPath, 'id' => $id]);
    }

    public function belumLunas(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE faktur
             SET status_bayar = 'belum_lunas', tanggal_lunas = NULL, bukti_pembayaran = NULL
             WHERE id_faktur = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function getSummary(?string $status = null, ?string $search = null, array $filters = []): array {
        $sql = "SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(total_faktur), 0) AS total_semua,
                    COALESCE(SUM(CASE WHEN status_bayar = 'lunas' THEN total_faktur ELSE 0 END), 0) AS total_lunas,
                    COALESCE(SUM(CASE WHEN status_bayar = 'belum_lunas' THEN total_faktur ELSE 0 END), 0) AS total_belum_lunas,
                    COUNT(CASE WHEN status_bayar = 'lunas' THEN 1 END) AS count_lunas,
                    COUNT(CASE WHEN status_bayar = 'belum_lunas' THEN 1 END) AS count_belum_lunas
                FROM (
                    SELECT f.id_faktur, f.status_bayar, COALESCE(SUM(ofa.total), 0) AS total_faktur
                    FROM faktur f
                    JOIN pbf p ON f.id_pbf = p.id_pbf
                    LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur";

        $params = [];
        $conditions = [];

        if ($status !== null && in_array($status, ['lunas', 'belum_lunas'], true)) {
            $conditions[] = "f.status_bayar = :summary_status";
            $params['summary_status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $conditions[] = "(f.no_faktur LIKE :summary_search_faktur OR p.nama_pbf LIKE :summary_search_pbf OR ofa.nama_obat LIKE :summary_search_obat)";
            $likeSearch = "%$search%";
            $params['summary_search_faktur'] = $likeSearch;
            $params['summary_search_pbf'] = $likeSearch;
            $params['summary_search_obat'] = $likeSearch;
        }

        $conditions = array_merge($conditions, $this->buildFilterConditions($filters, $params, 'summary_filter'));

        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY f.id_faktur, f.status_bayar
                ) x";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ?: [
            'total_records' => 0,
            'total_semua' => 0,
            'total_lunas' => 0,
            'total_belum_lunas' => 0,
            'count_lunas' => 0,
            'count_belum_lunas' => 0,
        ];
    }

    public function getAvailableMonths(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT DATE_FORMAT(tanggal_faktur, '%Y-%m') AS bulan
             FROM faktur ORDER BY bulan DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM faktur")->fetchColumn();
    }
}