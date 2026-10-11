<?php
/**
 * inc/bao-ve.php
 * Kiểm tra người dùng đã đăng nhập trước khi vào trang quản trị.
 */

require_once __DIR__ . '/config.php';

if (
    !isset($_SESSION['nguoi_dung']) ||
    !is_array($_SESSION['nguoi_dung'])
) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Vui lòng đăng nhập để tiếp tục.',
    ];

    header('Location: dang-nhap.php');
    exit;
}