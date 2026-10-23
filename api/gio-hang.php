<?php
declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

header('Content-Type: application/json; charset=utf-8');

// Đăng ký Handler trả lỗi 500 đúng chuẩn
set_exception_handler(function ($e) {
    error_log('[API_GIO_HANG] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['loi' => 'Lỗi máy chủ'], JSON_UNESCAPED_UNICODE);
    exit;
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

// Đồng bộ key Session duy nhất là $_SESSION['gio_hang']
if (!isset($_SESSION['gio_hang'])) {
    $_SESSION['gio_hang'] = [];
}

// 1. GET: Lấy danh sách sản phẩm trong giỏ hàng
if ($method === 'GET') {
    $gioHang = $_SESSION['gio_hang'];
    
    if (empty($gioHang)) {
        echo json_encode([
            'cacMuc' => [],
            'tongSoLuong' => 0,
            'tongTien' => 0
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = db();
    $ids = array_keys($gioHang);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT id, ten, gia, hinh_anh, tinh_trang FROM san_pham WHERE id IN ($placeholders)");
    $stmt->execute(array_values($ids));
    $sanPhams = $stmt->fetchAll();

    $cacMuc = [];
    $tongSoLuong = 0;
    $tongTien = 0;

    foreach ($sanPhams as $sp) {
        $spId = $sp['id'];
        $soLuong = (int) ($gioHang[$spId] ?? 0);
        $thanhTien = (int)$sp['gia'] * $soLuong;

        $tongSoLuong += $soLuong;
        $tongTien += $thanhTien;

        $cacMuc[] = [
            'id' => $sp['id'],
            'ten' => $sp['ten'],
            'gia' => (int)$sp['gia'],
            'hinhAnh' => $sp['hinh_anh'],
            'soLuong' => $soLuong,
            'thanhTien' => $thanhTien
        ];
    }

    echo json_encode([
        'cacMuc' => $cacMuc,
        'tongSoLuong' => $tongSoLuong,
        'tongTien' => $tongTien
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. POST: Thêm, Xóa, Cập nhật giỏ hàng qua JSON Body
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['loi' => 'Thân yêu cầu phải là dữ liệu JSON hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $hanhDong = $data['hanh_dong'] ?? $data['hanh-dong'] ?? 'them';
    $sanPhamId = trim((string)($data['san_pham_id'] ?? $data['id'] ?? ''));

    // XỬ LÝ HÀNH ĐỘNG XÓA
    if ($hanhDong === 'xoa') {
        if ($sanPhamId === '') {
            http_response_code(404);
            echo json_encode(['loi' => 'Thiếu mã sản phẩm'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        unset($_SESSION['gio_hang'][$sanPhamId]);

        echo json_encode([
            'thanhCong' => true,
            'thongBao' => 'Đã xóa sản phẩm khỏi giỏ hàng',
            'tongSoLuong' => array_sum($_SESSION['gio_hang'])
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // XỬ LÝ HÀNH ĐỘNG THÊM
    $soLuong = filter_var($data['so_luong'] ?? $data['sl'] ?? 1, FILTER_VALIDATE_INT);

    if ($sanPhamId === '') {
        http_response_code(404);
        echo json_encode(['loi' => 'Thiếu mã sản phẩm'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($soLuong === false || $soLuong <= 0) {
        http_response_code(422);
        echo json_encode(['loi' => 'Số lượng sản phẩm không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, ten, gia, tinh_trang FROM san_pham WHERE id = ?");
    $stmt->execute([$sanPhamId]);
    $sp = $stmt->fetch();

    if (!$sp) {
        http_response_code(404);
        echo json_encode(['loi' => 'Sản phẩm không tồn tại'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (isset($sp['tinh_trang']) && $sp['tinh_trang'] === 'Hết hàng') {
        http_response_code(422);
        echo json_encode(['loi' => 'Sản phẩm này hiện đã hết hàng'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $soLuongHienTai = $_SESSION['gio_hang'][$sanPhamId] ?? 0;
    $_SESSION['gio_hang'][$sanPhamId] = $soLuongHienTai + $soLuong;

    echo json_encode([
        'thanhCong' => true,
        'thongBao' => 'Đã thêm sản phẩm vào giỏ hàng',
        'tongSoLuong' => array_sum($_SESSION['gio_hang'])
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Các phương thức không hợp lệ
http_response_code(405);
header('Allow: GET, POST');
echo json_encode(['loi' => 'Phương thức không được hỗ trợ'], JSON_UNESCAPED_UNICODE);