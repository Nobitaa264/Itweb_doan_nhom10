<?php
declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

header('Content-Type: application/json; charset=utf-8');

function phanHoiGioHang(array $duLieu, int $ma = 200): never
{
    http_response_code($ma);
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$phuongThuc = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($phuongThuc, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    phanHoiGioHang(['loi' => 'Phương thức không được hỗ trợ'], 405);
}

$kho = new KhoSanPham();
$gio = new GioHang(); // Sử dụng cùng khóa session 'gio' với gio-hang.php.

if ($phuongThuc === 'GET') {
    $cacMuc = [];
    foreach ($gio->cacMuc($kho) as $muc) {
        $sp = $muc['sanPham'];
        $cacMuc[] = [
            'id' => $sp->id,
            'ten' => $sp->ten,
            'gia' => $sp->gia,
            'hinhAnh' => $sp->hinhAnh,
            'soLuong' => $muc['soLuong'],
            'thanhTien' => $muc['thanhTien'],
        ];
    }
    phanHoiGioHang([
        'cacMuc' => $cacMuc,
        'tongSoLuong' => $gio->soMon(),
        'tongTien' => $gio->tongTien($kho),
    ]);
}

// TH7: từ chối POST thiếu/sai token trước khi đọc JSON hoặc thay đổi giỏ hàng.
// csrfKiemTraPost() trả HTTP 403 + JSON {"loi": "..."} cho đường dẫn api/.
csrfKiemTraPost();

$raw = file_get_contents('php://input');
$data = json_decode($raw !== false ? $raw : '', true);
if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    phanHoiGioHang(['loi' => 'Thân yêu cầu phải là dữ liệu JSON hợp lệ'], 400);
}

$hanhDong = chuoi($data['hanh_dong'] ?? $data['hanh-dong'] ?? 'them');
$id = trim(chuoi($data['san_pham_id'] ?? $data['id'] ?? ''));
if ($id === '') {
    phanHoiGioHang(['loi' => 'Thiếu mã sản phẩm'], 404);
}

if ($hanhDong === 'xoa') {
    $gio->xoa($id);
    phanHoiGioHang([
        'thanhCong' => true,
        'thongBao' => 'Đã xóa sản phẩm khỏi giỏ hàng',
        'tongSoLuong' => $gio->soMon(),
    ]);
}

if (!in_array($hanhDong, ['them', 'cap-nhat'], true)) {
    phanHoiGioHang(['loi' => 'Thao tác giỏ hàng không hợp lệ'], 422);
}

$sl = filter_var($data['so_luong'] ?? $data['sl'] ?? 1, FILTER_VALIDATE_INT);
if ($sl === false || $sl < 1 || $sl > 99) {
    phanHoiGioHang(['loi' => 'Số lượng phải từ 1 đến 99'], 422);
}

try {
    if ($kho->trangThaiTonKho($id) === null) {
        phanHoiGioHang(['loi' => 'Sản phẩm không tồn tại'], 404);
    }
    if ($hanhDong === 'them') {
        $gio->them($id, $kho, (int) $sl);
    } else {
        $gio->capNhat($id, (int) $sl, $kho);
    }
    phanHoiGioHang([
        'thanhCong' => true,
        'thongBao' => 'Đã cập nhật giỏ hàng',
        'tongSoLuong' => $gio->soMon(),
        'tongTien' => $gio->tongTien($kho),
    ]);
} catch (DomainException $loi) {
    phanHoiGioHang(['loi' => $loi->getMessage()], 422);
}
