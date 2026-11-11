<?php
declare(strict_types=1);

/**
 * api/san-pham.php – API sản phẩm StyleHub (TH6, sửa lỗi tra cứu theo ID).
 *
 * GET /api/san-pham.php                 -> 200, danh sách sản phẩm
 * GET /api/san-pham.php?q=ao            -> 200, kết quả tìm kiếm
 * GET /api/san-pham.php?id=AK001        -> 200, chi tiết sản phẩm
 * GET /api/san-pham.php?id=SP999999     -> 404, sản phẩm không tồn tại
 * DELETE /api/san-pham.php?id=AK001     -> 405, không hỗ trợ phương thức
 */

require_once dirname(__DIR__) . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Models\SanPham;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(
        ['loi' => 'Phương thức không được hỗ trợ'],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

/**
 * Chuyển model SanPham thành cấu trúc JSON thống nhất cho cả danh sách và chi tiết.
 * Giữ nguyên các tên thuộc tính mà JavaScript đang sử dụng.
 *
 * @return array<string, mixed>
 */
function duLieuSanPhamApi(SanPham $sanPham): array
{
    return [
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

try {
    $khoSanPham = new KhoSanPham();

    // Khi URL có tham số id, CHỈ lấy đúng một sản phẩm.
    // Không được rơi xuống nhánh trả danh sách khi id không tồn tại.
    if (array_key_exists('id', $_GET)) {
        $idThamSo = $_GET['id'];

        // Dữ liệu kiểu mảng như ?id[]=AK001 không phải mã sản phẩm hợp lệ.
        if (!is_string($idThamSo)) {
            http_response_code(400);
            echo json_encode(
                ['loi' => 'Tham số id không hợp lệ'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            exit;
        }

        $id = trim($idThamSo);
        $sanPham = $id === '' ? null : $khoSanPham->timTheoId($id);

        if ($sanPham === null) {
            http_response_code(404);
            echo json_encode(
                ['loi' => 'Không tìm thấy sản phẩm'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            exit;
        }

        http_response_code(200);
        echo json_encode(
            [
                'thanhCong' => true,
                'duLieu' => duLieuSanPhamApi($sanPham),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        exit;
    }

    // Không truyền id: giữ nguyên chức năng danh sách, tìm kiếm, lọc và sắp xếp.
    // chuoi() giúp tham số dạng mảng không gây Warning/TypeError.
    $tuKhoa = chuoi($_GET['q'] ?? '');
    $danhMuc = chuoi($_GET['danh_muc'] ?? 'tat-ca');
    $sapXep = chuoi($_GET['sap_xep'] ?? 'mac-dinh');

    $danhSach = $khoSanPham->timKiem($tuKhoa, $danhMuc, $sapXep);
    $duLieu = array_map('duLieuSanPhamApi', $danhSach);

    http_response_code(200);
    echo json_encode(
        [
            'thanhCong' => true,
            'tongSo' => count($duLieu),
            'duLieu' => $duLieu,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );
} catch (Throwable $e) {
    error_log('[API_SAN_PHAM] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(
        ['loi' => 'Lỗi máy chủ'],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}
