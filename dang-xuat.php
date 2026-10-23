<?php
declare(strict_types=1);

/**
 * dang-xuat.php
 * Hủy trạng thái đăng nhập và chuyển về trang chủ.
 */

require_once __DIR__ . '/inc/config.php';

// Chỉ giữ thông báo để hiển thị sau khi chuyển hướng.
$_SESSION = [];

$_SESSION['flash'] = [
    'loai' => 'thanh-cong',
    'noiDung' => 'Bạn đã đăng xuất thành công.',
];

session_regenerate_id(true);

header('Location: index.php');
exit;
