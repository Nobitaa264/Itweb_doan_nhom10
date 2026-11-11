<?php
/**
 * inc/config.php
 */

// TH7 - Header bao mat: dat truoc session_start() va truoc khi xuat HTML.
// Loai bo thong tin phien ban PHP khoi header phan hoi.
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');

    // TH7.11.6: CSP CHAN CHINH THUC sau khi da kiem tra Report-Only.
    // Khong cho phep JavaScript/CSS inline; chi cho phep cac nguon can thiet.
    // Neu co tai nguyen bi chan, xem Chrome DevTools > Console.
    $chinhSachCsp = implode('; ', [
        "default-src 'self'",
        "base-uri 'self'",
        "object-src 'none'",
        "frame-ancestors 'self'",
        "form-action 'self'",
        "script-src 'self'",
        "style-src 'self'",
        "img-src 'self' data:",
        "font-src 'self' data:",
        "connect-src 'self' https://jsonplaceholder.typicode.com",
        "frame-src 'self' https://maps.google.com https://www.google.com https://www.youtube-nocookie.com",
        "media-src 'self'",
    ]);

    header_remove('Content-Security-Policy-Report-Only');
    header('Content-Security-Policy: ' . $chinhSachCsp);

    // HSTS chi duoc gui tren ket noi HTTPS, khong bat tren HTTP localhost.
    $dangHttps = !empty($_SERVER['HTTPS']) &&$_SERVER['HTTPS'] !== 'off';
    if ($dangHttps) {
        header('Strict-Transport-Security: max-age=15552000');
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ham.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xac-thuc.php';

const MOI_TRUONG = 'dev';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Khởi tạo phiên trước khi nạp xác thực và trước khi tạo mã CSRF.
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) &&$_SERVER['HTTPS'] !== 'off',
    ]);

    session_start();
}

// TH7: Tự động hết hạn phiên sau 30 phút không có request.
// Chỉ dùng thời gian trên máy chủ; không tin vào thời gian phía trình duyệt.
$thoiGianKhongHoatDongToiDa = 30 * 60;
$thoiDiemHienTai = time();
$hoatDongCuoi =$_SESSION['hoat_dong_cuoi'] ?? null;

if (
    is_int($hoatDongCuoi)
    && ($thoiDiemHienTai - $hoatDongCuoi) >=$thoiGianKhongHoatDongToiDa
) {
    // Hủy dữ liệu phiên cũ, bao gồm uid và CSRF token, trước khi xử lý request.
    $_SESSION = [];

    // Không cho tiếp tục sử dụng mã session ID đã hết hạn.
    // Phiên mới nhận cookie PHPSESSID mới với cấu hình an toàn đã khai báo.
    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Không thể tạo phiên mới sau khi phiên cũ hết hạn.');
    }
}

// Gia hạn thời gian sống khi người dùng thực sự tạo request đến máy chủ.
$_SESSION['hoat_dong_cuoi'] =$thoiDiemHienTai;

// Chỉ uid đại diện tài khoản; dữ liệu tài khoản đọc từ CSDL.
unset($_SESSION['nguoi_dung']);

// Nạp các hàm kiểm tra CSRF và xác thực dùng chung.
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/xac-thuc.php';

// Mỗi phiên có một mã CSRF được tạo bằng random_bytes(32).
csrfToken();

// Bảo vệ tập trung mọi yêu cầu POST nạp inc/config.php.
// POST thiếu hoặc sai mã sẽ kết thúc bằng HTTP 403 trước khi xử lý nghiệp vụ.
// API nhận phản hồi JSON, form thông thường nhận phản hồi HTML.
csrfKiemTraPost();

$goc = './';
$trang =$trang ?? 'index';
$tieuDe =$tieuDe ?? 'StyleHub - Nhóm 10';