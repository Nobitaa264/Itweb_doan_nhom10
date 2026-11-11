<?php
declare(strict_types=1);

/**
 * Trả thông tin người dùng từ CSDL theo uid lưu trong PHP session.
 * Không lưu họ tên, vai trò, email hoặc mật khẩu trong session.
 * Một request chỉ đọc CSDL tối đa một lần qua cache tạm trong hàm.
 */
function nguoiDungHienTai(): ?array
{
    static $daNap = false;
    static $nguoiDung = null;

    if ($daNap) {
        return $nguoiDung;
    }
    $daNap = true;

    $uid = filter_var(
        $_SESSION['uid'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($uid === false || $uid === null) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, ten, ten_dang_nhap, email, vai_tro
           FROM nguoi_dung
          WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $uid]);
    $taiKhoan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($taiKhoan)) {
        // Tài khoản đã bị xóa: không giữ uid cũ.
        unset($_SESSION['uid']);
        return null;
    }

    $nguoiDung = [
        'id' => (int) $taiKhoan['id'],
        'ten' => (string) $taiKhoan['ten'],
        'ho_ten' => (string) $taiKhoan['ten'],
        'tenDangNhap' => (string) $taiKhoan['ten_dang_nhap'],
        'email' => (string) $taiKhoan['email'],
        'vaiTro' => (string) $taiKhoan['vai_tro'],
        'vai_tro' => (string) $taiKhoan['vai_tro'],
    ];

    return $nguoiDung;
}

/** Kiểm tra quyền trên dữ liệu CSDL ở phía máy chủ. */
function yeuCauQuyen(?string $vaiTro = null): array
{
    $user = nguoiDungHienTai();

    if ($user === null) {
        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => 'Vui lòng đăng nhập để tiếp tục.',
        ];
        header('Location: dang-nhap.php');
        exit;
    }

    if ($vaiTro !== null) {
        $vaiTroUser = (string) $user['vaiTro'];
        $laAdmin = in_array($vaiTroUser, ['quan_tri', 'admin'], true);
        $dungQuyen = in_array($vaiTro, ['quan_tri', 'admin'], true)
            ? $laAdmin
            : $vaiTroUser === $vaiTro;

        if (!$dungQuyen) {
            http_response_code(403);
            $file403 = __DIR__ . '/../403.php';
            if (is_file($file403)) {
                require $file403;
            } else {
                echo '<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>';
            }
            exit;
        }
    }

    return $user;
}

/**
 * Đăng xuất: xóa toàn bộ dữ liệu phiên, xóa cookie PHPSESSID và hủy phiên.
 * Được gọi chỉ sau khi kiểm tra phương thức POST và CSRF.
 */
function dangXuat(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOLEAN)) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => (string) ($params['path'] ?: '/'),
            'domain' => (string) ($params['domain'] ?? ''),
            'secure' => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => (string) ($params['samesite'] ?? 'Lax'),
        ]);
    }

    session_destroy();
}
