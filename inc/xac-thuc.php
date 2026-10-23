<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function nguoiDungHienTai(): ?array {
    return $_SESSION['nguoi_dung'] ?? null;
}

function yeuCauQuyen(?string $vaiTro = null): array {
    $user = nguoiDungHienTai();
    
    // Chưa đăng nhập -> Chuyển sang đăng nhập
    if (!$user) {
        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => 'Vui lòng đăng nhập để tiếp tục.'
        ];
        header('Location: dang-nhap.php');
        exit;
    }

    // Sai vai trò (ví dụ: Khách hàng mở trang Admin) -> Trả về 403.php
    $vaiTroUser = $user['vaiTro'] ?? $user['vai_tro'] ?? '';
    if ($vaiTro !== null && $vaiTroUser !== $vaiTro) {
        http_response_code(403);
        require_once __DIR__ . '/../403.php';
        exit;
    }

    return $user;
}