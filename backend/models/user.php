<?php
/**
 * Model: User - Apotek Ananda Jadimulya
 * 
 * Query layer untuk tabel `users`.
 * Mengelola autentikasi, CRUD akun admin, dan rate limiting.
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    // =========================================
    // AUTENTIKASI
    // =========================================
    
    /**
     * Cari user berdasarkan username
     */
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Cari user berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id_user, nama_lengkap, username, role, created_at, updated_at FROM users WHERE id_user = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Verifikasi password
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    // =========================================
    // RATE LIMITING (Brute-force Protection)
    // =========================================
    
    /**
     * Catat percobaan login gagal
     */
    public function recordLoginAttempt(string $ip, string $username): void {
        $stmt = $this->db->prepare(
            "INSERT INTO login_attempts (ip_address, username, attempted_at) VALUES (:ip, :username, NOW())"
        );
        $stmt->execute(['ip' => $ip, 'username' => $username]);
    }
    
    /**
     * Hitung jumlah percobaan login gagal dalam waktu lockout
     */
    public function getRecentAttempts(string $ip, string $username): int {
        $lockoutTime = defined('LOGIN_LOCKOUT_TIME') ? LOGIN_LOCKOUT_TIME : 900;
        
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE (ip_address = :ip OR username = :username) 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :seconds SECOND)"
        );
        $stmt->execute([
            'ip'       => $ip,
            'username' => $username,
            'seconds'  => $lockoutTime
        ]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Cek apakah akun/IP terkunci
     */
    public function isLockedOut(string $ip, string $username): bool {
        $maxAttempts = defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 5;
        return $this->getRecentAttempts($ip, $username) >= $maxAttempts;
    }
    
    /**
     * Bersihkan percobaan login setelah berhasil
     */
    public function clearLoginAttempts(string $ip, string $username): void {
        $stmt = $this->db->prepare(
            "DELETE FROM login_attempts WHERE ip_address = :ip OR username = :username"
        );
        $stmt->execute(['ip' => $ip, 'username' => $username]);
    }
    
    /**
     * Bersihkan percobaan login yang sudah expired
     */
    public function cleanupOldAttempts(): void {
        $this->db->exec("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    }
    
    // =========================================
    // CRUD ADMIN (Super Admin only)
    // =========================================
    
    /**
     * Ambil semua akun admin (bukan super_admin)
     */
    public function getAllAdmins(): array {
        $stmt = $this->db->query(
            "SELECT id_user, nama_lengkap, username, role, created_at, updated_at 
             FROM users WHERE role = 'admin' ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Ambil semua user (untuk keperluan internal)
     */
    public function getAllUsers(): array {
        $stmt = $this->db->query(
            "SELECT id_user, nama_lengkap, username, role, created_at, updated_at 
             FROM users ORDER BY role ASC, created_at DESC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Tambah akun admin baru
     */
    public function createAdmin(string $namaLengkap, string $username, string $password): bool {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $this->db->prepare(
            "INSERT INTO users (nama_lengkap, username, password, role) 
             VALUES (:nama, :username, :password, 'admin')"
        );
        
        return $stmt->execute([
            'nama'     => $namaLengkap,
            'username' => $username,
            'password' => $hashedPassword
        ]);
    }
    
    /**
     * Update data admin
     */
    public function updateAdmin(int $id, string $namaLengkap, string $username, ?string $password = null): bool {
        if ($password !== null && $password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare(
                "UPDATE users SET nama_lengkap = :nama, username = :username, password = :password 
                 WHERE id_user = :id AND role = 'admin'"
            );
            return $stmt->execute([
                'nama'     => $namaLengkap,
                'username' => $username,
                'password' => $hashedPassword,
                'id'       => $id
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE users SET nama_lengkap = :nama, username = :username 
                 WHERE id_user = :id AND role = 'admin'"
            );
            return $stmt->execute([
                'nama'     => $namaLengkap,
                'username' => $username,
                'id'       => $id
            ]);
        }
    }
    
    /**
     * Hapus akun admin
     */
    public function deleteAdmin(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = :id AND role = 'admin'");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Cek apakah username sudah ada
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM users WHERE username = :username AND id_user != :id"
            );
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Hitung total admin
     */
    public function countAdmins(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        return (int) $stmt->fetchColumn();
    }
}
