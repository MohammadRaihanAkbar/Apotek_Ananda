<?php
/**
 * Model: Stok/Faktur - Apotek Ananda Jadimulya
 * Struktur v3: faktur (header) + obat_faktur (detail) + obat_batch (batch per qty).
 */

require_once __DIR__ . '/../config/database.php';

class StokMasuk {
    private PDO $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    public function getAll(?int $pbfId = null, ?string $search = null): array {
        $sql = "SELECT
                    f.*,
                    f.status_bayar AS status_pembayaran,
                    p.nama_pbf,
                    COUNT(ofa.id_obat_faktur) AS jumlah_item,
                    COALESCE(SUM(ofa.qty), 0) AS total_qty,
                    COALESCE(SUM(ofa.total), 0) AS total_faktur,
                    GROUP_CONCAT(DISTINCT ofa.nama_obat ORDER BY ofa.nama_obat SEPARATOR ', ') AS daftar_obat
                FROM faktur f
                JOIN pbf p ON f.id_pbf = p.id_pbf
                LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur";
        $params = [];
        $conditions = [];

        if ($pbfId !== null && $pbfId > 0) {
            $conditions[] = "f.id_pbf = :pbf_id";
            $params['pbf_id'] = $pbfId;
        }

        if ($search !== null && $search !== '') {
            $conditions[] = "(f.no_faktur LIKE :search_faktur OR p.nama_pbf LIKE :search_pbf OR ofa.nama_obat LIKE :search_obat)";
            $likeSearch = "%$search%";
            $params['search_faktur'] = $likeSearch;
            $params['search_pbf'] = $likeSearch;
            $params['search_obat'] = $likeSearch;
        }

        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY f.id_faktur, f.no_faktur, f.id_pbf, f.tanggal_faktur, f.tanggal_masuk, f.tanggal_jatuh_tempo, f.jumlah_obat, f.total_qty, f.total_harga, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran, f.id_user, f.created_at, f.updated_at, p.nama_pbf
                  ORDER BY f.tanggal_masuk DESC, f.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getNamaObatList(): array {
        $stmt = $this->db->query("SELECT DISTINCT nama_obat FROM obat_faktur ORDER BY nama_obat ASC LIMIT 300");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findFakturById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT f.*, f.status_bayar AS status_pembayaran, p.nama_pbf, u.nama_lengkap AS created_by,
                    COUNT(ofa.id_obat_faktur) AS jumlah_item,
                    COALESCE(SUM(ofa.qty), 0) AS total_qty,
                    COALESCE(SUM(ofa.total), 0) AS total_faktur
             FROM faktur f
             JOIN pbf p ON f.id_pbf = p.id_pbf
             LEFT JOIN users u ON f.id_user = u.id_user
             LEFT JOIN obat_faktur ofa ON f.id_faktur = ofa.id_faktur
             WHERE f.id_faktur = :id
             GROUP BY f.id_faktur, f.no_faktur, f.id_pbf, f.tanggal_faktur, f.tanggal_masuk, f.tanggal_jatuh_tempo, f.jumlah_obat, f.total_qty, f.total_harga, f.status_bayar, f.tanggal_lunas, f.bukti_pembayaran, f.id_user, f.created_at, f.updated_at, p.nama_pbf, u.nama_lengkap
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getDetailsByFakturId(int $id): array {
        $stmt = $this->db->prepare(
            "SELECT ofa.*, ofa.id_obat_faktur AS id_detail, ofa.qty AS jumlah_masuk,
                    COALESCE(MIN(ob.expired_date), NULL) AS expired_date_ringkas,
                    COUNT(ob.id_batch) AS jumlah_batch
             FROM obat_faktur ofa
             LEFT JOIN obat_batch ob ON ofa.id_obat_faktur = ob.id_obat_faktur
             WHERE ofa.id_faktur = :id
             GROUP BY ofa.id_obat_faktur, ofa.id_faktur, ofa.nama_obat, ofa.jenis_obat, ofa.satuan, ofa.harga_beli, ofa.discount, ofa.qty, ofa.total, ofa.created_at, ofa.updated_at
             ORDER BY ofa.id_obat_faktur ASC"
        );
        $stmt->execute(['id' => $id]);
        $details = $stmt->fetchAll();

        $batchStmt = $this->db->prepare(
            "SELECT id_batch, id_obat_faktur, no_batch, expired_date
             FROM obat_batch
             WHERE id_obat_faktur = :id
             ORDER BY id_batch ASC"
        );

        foreach ($details as &$detail) {
            $batchStmt->execute(['id' => $detail['id_obat_faktur']]);
            $detail['batches'] = $batchStmt->fetchAll();
            $detail['batch'] = implode(', ', array_map(fn($b) => $b['no_batch'], $detail['batches']));
            $detail['expired_date'] = $detail['expired_date_ringkas'];
        }
        unset($detail);

        return $details;
    }

    public function findFakturWithDetails(int $id): ?array {
        $faktur = $this->findFakturById($id);
        if (!$faktur) return null;
        $faktur['details'] = $this->getDetailsByFakturId($id);
        return $faktur;
    }

    public function createFakturWithDetails(array $header, array $items): int {
        $this->db->beginTransaction();
        try {
            $totals = $this->calculateTotals($items);
            $stmt = $this->db->prepare(
                "INSERT INTO faktur (no_faktur, id_pbf, tanggal_faktur, tanggal_masuk, tanggal_jatuh_tempo, jumlah_obat, total_qty, total_harga, status_bayar, tanggal_lunas, id_user)
                 VALUES (:no_faktur, :id_pbf, :tanggal_faktur, :tanggal_masuk, :tanggal_jatuh_tempo, :jumlah_obat, :total_qty, :total_harga, :status_bayar, :tanggal_lunas, :id_user)"
            );
            $status = $header['status_bayar'] ?? 'belum_lunas';
            $stmt->execute([
                'no_faktur'             => $header['no_faktur'],
                'id_pbf'                => $header['id_pbf'],
                'tanggal_faktur'        => $header['tanggal_faktur'],
                'tanggal_masuk'         => $header['tanggal_masuk'],
                'tanggal_jatuh_tempo'   => $header['tanggal_jatuh_tempo'] ?: null,
                'jumlah_obat'           => $totals['jumlah_obat'],
                'total_qty'             => $totals['total_qty'],
                'total_harga'           => $totals['total_harga'],
                'status_bayar'          => $status,
                'tanggal_lunas'         => $status === 'lunas' ? date('Y-m-d') : null,
                'id_user'               => $header['id_user'],
            ]);
            $idFaktur = (int) $this->db->lastInsertId();
            $this->insertDetails($idFaktur, $items);
            $this->db->commit();
            return $idFaktur;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Create faktur failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateFakturWithDetails(int $idFaktur, array $header, array $items): bool {
        $this->db->beginTransaction();
        try {
            $totals = $this->calculateTotals($items);
            $status = $header['status_bayar'] ?? 'belum_lunas';
            $stmt = $this->db->prepare(
                "UPDATE faktur SET
                    no_faktur = :no_faktur,
                    id_pbf = :id_pbf,
                    tanggal_faktur = :tanggal_faktur,
                    tanggal_masuk = :tanggal_masuk,
                    tanggal_jatuh_tempo = :tanggal_jatuh_tempo,
                    jumlah_obat = :jumlah_obat,
                    total_qty = :total_qty,
                    total_harga = :total_harga,
                    status_bayar = :status_bayar,
                    tanggal_lunas = CASE WHEN :status2 = 'lunas' AND tanggal_lunas IS NULL THEN CURDATE()
                                         WHEN :status3 = 'belum_lunas' THEN NULL
                                         ELSE tanggal_lunas END,
                    bukti_pembayaran = CASE WHEN :status4 = 'belum_lunas' THEN NULL ELSE bukti_pembayaran END
                 WHERE id_faktur = :id_faktur"
            );
            $stmt->execute([
                'no_faktur'             => $header['no_faktur'],
                'id_pbf'                => $header['id_pbf'],
                'tanggal_faktur'        => $header['tanggal_faktur'],
                'tanggal_masuk'         => $header['tanggal_masuk'],
                'tanggal_jatuh_tempo'   => $header['tanggal_jatuh_tempo'] ?: null,
                'jumlah_obat'           => $totals['jumlah_obat'],
                'total_qty'             => $totals['total_qty'],
                'total_harga'           => $totals['total_harga'],
                'status_bayar'          => $status,
                'status2'               => $status,
                'status3'               => $status,
                'status4'               => $status,
                'id_faktur'             => $idFaktur,
            ]);

            $del = $this->db->prepare("DELETE FROM obat_faktur WHERE id_faktur = :id");
            $del->execute(['id' => $idFaktur]);
            $this->insertDetails($idFaktur, $items);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Update faktur failed: ' . $e->getMessage());
            return false;
        }
    }

    private function calculateTotals(array $items): array {
        $totalQty = 0;
        $totalHarga = 0;
        foreach ($items as $item) {
            $qty = (int) $item['qty'];
            $harga = (float) $item['harga_beli'];
            $disc = (float) $item['discount'];
            $totalQty += $qty;
            $totalHarga += ($harga * $qty) * max(1 - ($disc / 100), 0);
        }
        return [
            'jumlah_obat' => count($items),
            'total_qty' => $totalQty,
            'total_harga' => $totalHarga,
        ];
    }

    private function insertDetails(int $idFaktur, array $items): void {
        $detailStmt = $this->db->prepare(
            "INSERT INTO obat_faktur (id_faktur, nama_obat, jenis_obat, satuan, harga_beli, discount, qty, total)
             VALUES (:id_faktur, :nama_obat, :jenis_obat, :satuan, :harga_beli, :discount, :qty, :total)"
        );
        $batchStmt = $this->db->prepare(
            "INSERT INTO obat_batch (id_obat_faktur, no_batch, expired_date)
             VALUES (:id_obat_faktur, :no_batch, :expired_date)"
        );

        foreach ($items as $item) {
            $harga = (float) $item['harga_beli'];
            $disc = (float) $item['discount'];
            $qty = (int) $item['qty'];
            $total = ($harga * $qty) * max(1 - ($disc / 100), 0);
            $detailStmt->execute([
                'id_faktur'   => $idFaktur,
                'nama_obat'   => $item['nama_obat'],
                'jenis_obat'  => $item['jenis_obat'] ?? null,
                'satuan'      => $item['satuan'],
                'harga_beli'  => $harga,
                'discount'    => $disc,
                'qty'         => $qty,
                'total'       => $total,
            ]);
            $idObatFaktur = (int) $this->db->lastInsertId();
            foreach (($item['batches'] ?? []) as $batch) {
                $batchStmt->execute([
                    'id_obat_faktur' => $idObatFaktur,
                    'no_batch'       => $batch['no_batch'],
                    'expired_date'   => $batch['expired_date'],
                ]);
            }
        }
    }

    public function deleteFaktur(int $idFaktur): bool {
        $stmt = $this->db->prepare("DELETE FROM faktur WHERE id_faktur = :id");
        return $stmt->execute(['id' => $idFaktur]);
    }

    public function updateBatch(int $idBatch, string $batch): bool {
        $stmt = $this->db->prepare("UPDATE obat_batch SET no_batch = :batch WHERE id_batch = :id");
        return $stmt->execute(['batch' => $batch, 'id' => $idBatch]);
    }

    public function getTotalStok(): int {
        return (int) $this->db->query("SELECT COALESCE(SUM(qty), 0) FROM obat_faktur")->fetchColumn();
    }

    public function getExpiringSixMonths(): array {
        $stmt = $this->db->query(
            "SELECT ofa.nama_obat, ofa.jenis_obat, ofa.satuan, ofa.harga_beli, ofa.discount,
                    COUNT(ob.id_batch) AS jumlah_masuk,
                    MIN(ob.expired_date) AS expired_date,
                    GROUP_CONCAT(DISTINCT ob.no_batch ORDER BY ob.no_batch SEPARATOR ', ') AS batch,
                    f.id_faktur, f.no_faktur, f.tanggal_faktur, f.tanggal_masuk, p.nama_pbf
             FROM obat_batch ob
             JOIN obat_faktur ofa ON ob.id_obat_faktur = ofa.id_obat_faktur
             JOIN faktur f ON ofa.id_faktur = f.id_faktur
             JOIN pbf p ON f.id_pbf = p.id_pbf
             WHERE ob.expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY ofa.id_obat_faktur, ofa.nama_obat, ofa.jenis_obat, ofa.satuan, ofa.harga_beli, ofa.discount, f.id_faktur, f.no_faktur, f.tanggal_faktur, f.tanggal_masuk, p.nama_pbf
             ORDER BY MIN(ob.expired_date) ASC"
        );
        return $stmt->fetchAll();
    }

    public function countExpiringSixMonths(): int {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM obat_batch
             WHERE expired_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)"
        )->fetchColumn();
    }

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM faktur")->fetchColumn();
    }
}
