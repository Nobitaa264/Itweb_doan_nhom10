<?php
declare(strict_types=1);

/**
 * dang-xuat.php – Chỉ chấp nhận POST có CSRF hợp lệ.
 * GET luôn trả 405 và không làm thay đổi trạng thái đăng nhập.
 */

require_once __DIR__ . '/inc/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Phương thức không được hỗ trợ. Vui lòng dùng nút Đăng xuất.');
}

csrfKiemTraPost();
dangXuat();

header('Cache-Control: no-store');
header('Location: index.php', true, 303);
exit;
