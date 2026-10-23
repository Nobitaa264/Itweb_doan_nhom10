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

    $vaiTroUser = $user['vaiTro'] ?? $user['vai_tro'] ?? '';

    // Nếu yêu cầu quyền 'quan_tri' hoặc 'admin', chấp nhận cả 2 giá trị này
    if ($vaiTro !== null) {
        $hopLe = false;

        if ($vaiTro === 'quan_tri' || $vaiTro === 'admin') {
            $hopLe = ($vaiTroUser === 'quan_tri' || $vaiTroUser === 'admin');
        } else {
            $hopLe = ($vaiTroUser === $vaiTro);
        }

        if (!$hopLe) {
            http_response_code(403);
            
            $file403 = __DIR__ . '/../403.php';
            if (file_exists($file403)) {
                require_once $file403;
            } else {
                echo '<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>';
            }
            exit;
        }
    }

    return $user;
}