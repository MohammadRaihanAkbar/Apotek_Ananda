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

    /**
     * Get piutang filtered by status (belum_lunas / lunas) with optional search.
     * Ordered by created_at DESC (last-input-first).
     */
    public function getAllByStatus(string $status, ?string $search = null): array {
        $sql = "SELECT
                    f.id_faktur,
                    f.id_faktur AS id_piutang,
                    f.no_faktur,
                    p.nama_pbf,
                    f.tanggal_faktur,
                    f.tanggal_jatuh_tempo,
                    f.status_bayar AS status,
                    f.status_bayar AS status_pembayaran,
                    f.tanggal_lunas,
                    f.bukti_pembayaran,
                    u.nama_lengkap AS created_by,
                    COUNT(ofa.id_obat_faktur) AS jumlah_item,
                    COALESCE(SUM(ofa.total), 0) AS jumlah_harga
                FROM faktur f
                JOIN pbf p ON f.id_pbf = p.id_pbf
                LEFT JOIN users u ON f.id_user = u.id_user
                LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur
                WHERE f.status_bayar = :status";
        $params = ['status' => $status];

        if ($search !== null && $search !== '') {
            $sql .= " AND (f.no_faktur LIKE :search_faktur OR p.nama_pbf LIKE :search_pbf OR ofa.nama_obat LIKE :search_obat)";
            $likeSearch = "%$search%";
            $params['search_faktur'] = $likeSearch;
            $params['search_pbf'] = $likeSearch;
            $params['search_obat'] = $likeSearch;
        }

        $sql .= " GROUP BY f.id_faktur, f.no_faktur, p.nama_pbf, f.tanggal_faktur, f.tanggal_jatuh_tempo, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran, u.nama_lengkap
                  ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Legacy getAll — still available for export or other uses.
     */
    public function getAll(?string $status = null, ?string $bulan = null, ?string $search = null): array {
        $sql = "SELECT
                    f.id_faktur,
                    f.id_faktur AS id_piutang,
                    f.no_faktur,
                    p.nama_pbf,
                    f.tanggal_faktur,
                    f.tanggal_jatuh_tempo,
                    f.status_bayar AS status,
                    f.status_bayar AS status_pembayaran,
                    f.tanggal_lunas,
                    f.bukti_pembayaran,
                    u.nama_lengkap AS created_by,
                    COUNT(ofa.id_obat_faktur) AS jumlah_item,
                    COALESCE(SUM(ofa.total), 0) AS jumlah_harga
                FROM faktur f
                JOIN pbf p ON f.id_pbf = p.id_pbf
                LEFT JOIN users u ON f.id_user = u.id_user
                LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur";
        $params = [];
        $conditions = [];

        if ($status !== null && in_array($status, ['lunas', 'belum_lunas'], true)) {
            $conditions[] = "f.status_bayar = :status";
            $params['status'] = $status;
        }
        if ($bulan !== null && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $conditions[] = "DATE_FORMAT(f.tanggal_faktur, '%Y-%m') = :bulan";
            $params['bulan'] = $bulan;
        }
        if ($search !== null && $search !== '') {
            $conditions[] = "(f.no_faktur LIKE :search_faktur OR p.nama_pbf LIKE :search_pbf OR ofa.nama_obat LIKE :search_obat)";
            $likeSearch = "%$search%";
            $params['search_faktur'] = $likeSearch;
            $params['search_pbf'] = $likeSearch;
            $params['search_obat'] = $likeSearch;
        }
        if ($conditions) $sql .= " WHERE " . implode(' AND ', $conditions);

        $sql .= " GROUP BY f.id_faktur, f.no_faktur, p.nama_pbf, f.tanggal_faktur, f.tanggal_jatuh_tempo, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran, u.nama_lengkap
                  ORDER BY f.created_at DESC";
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
             GROUP BY f.id_faktur, f.no_faktur, p.nama_pbf, f.tanggal_faktur, f.tanggal_jatuh_tempo, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran
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

    public function getSummary(): array {
        $sql = "SELECT
                    COUNT(*) AS total_records,
                    COALESCE(SUM(total_faktur), 0) AS total_semua,
                    COALESCE(SUM(CASE WHEN status_bayar = 'lunas' THEN total_faktur ELSE 0 END), 0) AS total_lunas,
                    COALESCE(SUM(CASE WHEN status_bayar = 'belum_lunas' THEN total_faktur ELSE 0 END), 0) AS total_belum_lunas,
                    COUNT(CASE WHEN status_bayar = 'lunas' THEN 1 END) AS count_lunas,
                    COUNT(CASE WHEN status_bayar = 'belum_lunas' THEN 1 END) AS count_belum_lunas
                FROM (
                    SELECT f.id_faktur, f.status_bayar, f.tanggal_faktur, COALESCE(SUM(ofa.total), 0) AS total_faktur
                    FROM faktur f
                    LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur
                    GROUP BY f.id_faktur, f.status_bayar, f.tanggal_faktur) x";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
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
