<?php
/**
 * dang-xuat.php
 * Hủy trạng thái đăng nhập và tạo session ID mới.
 */

require_once __DIR__ . '/inc/config.php';

unset($_SESSION['nguoi_dung']);

session_regenerate_id(true);

$_SESSION['flash'] = [
    'loai' => 'thanh-cong',
    'noiDung' => 'Bạn đã đăng xuất.',
];

header('Location: index.php');
exit;