<?php
declare(strict_types=1);

namespace App\Data;

use App\Config\Database;
use PDO;

final class KhoLienHe
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connect();
    }

    /**
     * Lấy toàn bộ liên hệ, mới nhất trước.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tatCa(): array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                ho_ten AS hoTen,
                so_dien_thoai AS soDienThoai,
                email,
                san_pham_id AS sanPhamId,
                san_pham_ten AS sanPhamTen,
                noi_dung AS noiDung,
                anh,
                trang_thai AS trangThai,
                created_at AS thoiGian
            FROM lien_he
            ORDER BY created_at DESC, id DESC
            SQL;

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Lưu một yêu cầu liên hệ.
     *
     * @param array<string, mixed> $duLieu
     */
    public function luu(array $duLieu): int
    {
        $sql = <<<'SQL'
            INSERT INTO lien_he (
                ho_ten,
                so_dien_thoai,
                email,
                san_pham_id,
                san_pham_ten,
                noi_dung,
                anh
            ) VALUES (
                :ho_ten,
                :so_dien_thoai,
                :email,
                :san_pham_id,
                :san_pham_ten,
                :noi_dung,
                :anh
            )
            SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'ho_ten' => $duLieu['hoTen'],
            'so_dien_thoai' => $duLieu['soDienThoai'],
            'email' => ($duLieu['email'] ?? '') !== ''
                ? $duLieu['email']
                : null,
            'san_pham_id' => ($duLieu['sanPhamId'] ?? '') !== ''
                ? $duLieu['sanPhamId']
                : null,
            'san_pham_ten' => ($duLieu['sanPhamTen'] ?? '') !== ''
                ? $duLieu['sanPhamTen']
                : null,
            'noi_dung' => $duLieu['noiDung'],
            'anh' => ($duLieu['anh'] ?? '') !== ''
                ? $duLieu['anh']
                : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
