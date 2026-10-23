<?php
declare(strict_types=1);

function kiemTraSanPham(array $duLieu, PDO $pdo): array {
    $loi = [];

    if (empty($duLieu['id']) || !preg_match('/^[A-Z0-9]{2,20}$/i', $duLieu['id'])) {
        $loi['id'] = 'Mã sản phẩm phải từ 2-20 ký tự chữ/số.';
    }

    if (empty($duLieu['ten']) || mb_strlen($duLieu['ten']) > 255) {
        $loi['ten'] = 'Tên sản phẩm không được để trống và tối đa 255 ký tự.';
    }

    $gia = filter_var($duLieu['gia'], FILTER_VALIDATE_INT);
    if ($gia === false || $gia < 1000) {
        $loi['gia'] = 'Giá phải là số nguyên tối thiểu 1.000 VNĐ.';
    }

    $dmId = filter_var($duLieu['danh_muc_id'], FILTER_VALIDATE_INT);
    if ($dmId === false || $dmId < 1) {
        $loi['danh_muc_id'] = 'Vui lòng chọn danh mục hợp lệ.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM danh_muc WHERE id = ?');
        $stmt->execute([$dmId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $loi['danh_muc_id'] = 'Danh mục chọn không tồn tại.';
        }
    }

    if (empty($duLieu['hinh_anh'])) {
        $loi['hinh_anh'] = 'Vui lòng nhập đường dẫn hình ảnh.';
    }

    return $loi;
}