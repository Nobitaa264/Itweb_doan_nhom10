<?php
declare(strict_types=1);

namespace App\Data;

use App\Config\Database;
use PDO;
use PDOException;
use RuntimeException;

final class KhoNguoiDung
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /**
     * Kiểm tra tên đăng nhập hoặc email đã tồn tại.
     */
    public function daTonTai(
        string $tenDangNhap,
        string $email
    ): bool {
        $sql = '
            SELECT 1
            FROM nguoi_dung
            WHERE ten_dang_nhap = :ten_dang_nhap
               OR email = :email
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ten_dang_nhap' => $tenDangNhap,
            'email' => $email,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Tạo tài khoản khách hàng mới.
     * Không cho phép tự đăng ký tài khoản quản trị.
     */
    public function taoTaiKhoan(
        string $ten,
        string $tenDangNhap,
        string $email,
        string $matKhau
    ): int {
        if ($this->daTonTai($tenDangNhap, $email)) {
            throw new RuntimeException(
                'Tên đăng nhập hoặc email đã được sử dụng.'
            );
        }

        $hash = password_hash(
            $matKhau,
            PASSWORD_DEFAULT
        );

        if ($hash === false) {
            throw new RuntimeException(
                'Không thể mã hóa mật khẩu.'
            );
        }

        $sql = '
            INSERT INTO nguoi_dung (
                ten,
                ten_dang_nhap,
                email,
                mat_khau,
                vai_tro
            ) VALUES (
                :ten,
                :ten_dang_nhap,
                :email,
                :mat_khau,
                :vai_tro
            )
        ';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'ten' => $ten,
                'ten_dang_nhap' => $tenDangNhap,
                'email' => $email,
                'mat_khau' => $hash,
                'vai_tro' => 'customer',
            ]);
        } catch (PDOException $e) {
            // Unique constraint vẫn là lớp bảo vệ cuối cùng
            // khi hai yêu cầu đăng ký xảy ra đồng thời.
            if ($e->getCode() === '23000') {
                throw new RuntimeException(
                    'Tên đăng nhập hoặc email đã được sử dụng.',
                    0,
                    $e
                );
            }

            throw $e;
        }

        return (int) $this->pdo->lastInsertId();
    }
}
