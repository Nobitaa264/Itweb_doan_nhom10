<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/config.php';

use App\Data\KhoSanPham;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $khoSanPham = new KhoSanPham();

    $tuKhoa = trim((string) ($_GET['q'] ?? ''));
    $danhMuc = trim((string) ($_GET['danh_muc'] ?? 'tat-ca'));
    $sapXep = trim((string) ($_GET['sap_xep'] ?? 'mac-dinh'));

    $danhSach = $khoSanPham->timKiem(
        $tuKhoa,
        $danhMuc,
        $sapXep
    );

    $duLieu = [];

    foreach ($danhSach as $sanPham) {
        $duLieu[] = [
            'id' => $sanPham->id,
            'ten' => $sanPham->ten,
            'gia' => $sanPham->gia,
            'danhMuc' => $sanPham->danhMuc,
            'hinhAnh' => $sanPham->hinhAnh,
            'moTa' => $sanPham->moTa,
            'chatLieu' => $sanPham->chatLieu,
            'mauSac' => $sanPham->mauSac,
            'kichCo' => $sanPham->kichCo,
            'tinhTrang' => $sanPham->tinhTrang,
        ];
    }

    echo json_encode([
        'thanhCong' => true,
        'tongSo' => count($duLieu),
        'duLieu' => $duLieu,
    ], JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_THROW_ON_ERROR);

} catch (\Throwable $e) {
    error_log('[API_SAN_PHAM] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'loi' => 'Lỗi máy chủ'
    ], JSON_UNESCAPED_UNICODE);
}
