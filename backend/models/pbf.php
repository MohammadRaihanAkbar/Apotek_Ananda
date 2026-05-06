<?php
/**
 * Model: PBF (Pedagang Besar Farmasi) - Apotek Ananda Jadimulya
 */

require_once __DIR__ . '/../config/database.php';

class PBF {
    private PDO $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    public function getAll(?string $search = null): array {
        $sql = "SELECT p.*, u.nama_lengkap AS created_by
                FROM pbf p
                LEFT JOIN users u ON p.id_user = u.id_user";
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " WHERE p.nama_pbf LIKE :search_nama
                       OR p.alamat LIKE :search_alamat
                       OR p.no_telepon LIKE :search_telepon
                       OR p.kontak_person LIKE :search_kontak
                       OR p.keterangan LIKE :search_keterangan";
            $like = "%$search%";
            $params = [
                'search_nama' => $like,
                'search_alamat' => $like,
                'search_telepon' => $like,
                'search_kontak' => $like,
                'search_keterangan' => $like,
            ];
        }

        $sql .= " ORDER BY p.nama_pbf ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nama_lengkap AS created_by
             FROM pbf p
             LEFT JOIN users u ON p.id_user = u.id_user
             WHERE p.id_pbf = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO pbf (nama_pbf, alamat, no_telepon, kontak_person, keterangan, id_user)
             VALUES (:nama_pbf, :alamat, :no_telepon, :kontak_person, :keterangan, :id_user)"
        );
        $stmt->execute([
            'nama_pbf'       => $data['nama_pbf'],
            'alamat'         => $data['alamat'] ?: null,
            'no_telepon'     => $data['no_telepon'] ?: null,
            'kontak_person'  => $data['kontak_person'] ?: null,
            'keterangan'     => $data['keterangan'] ?: null,
            'id_user'        => $userId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE pbf SET
                nama_pbf = :nama_pbf,
                alamat = :alamat,
                no_telepon = :no_telepon,
                kontak_person = :kontak_person,
                keterangan = :keterangan
             WHERE id_pbf = :id"
        );
        return $stmt->execute([
            'nama_pbf'       => $data['nama_pbf'],
            'alamat'         => $data['alamat'] ?: null,
            'no_telepon'     => $data['no_telepon'] ?: null,
            'kontak_person'  => $data['kontak_person'] ?: null,
            'keterangan'     => $data['keterangan'] ?: null,
            'id'             => $id,
        ]);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM faktur WHERE id_pbf = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }
}
