<?php
declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';

header('Content-Type: application/json; charset=utf-8');

// Đăng ký Handler trả lỗi 500 định dạng JSON đúng yêu cầu đề bài
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

// 1. GET: Lấy danh sách sản phẩm trong giỏ hàng
if ($method === 'GET') {
    $gioHang = $_SESSION['gio'] ?? []; // Mảng [san_pham_id => so_luong]
    
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

// 2. POST: Thêm sản phẩm vào giỏ hàng (Nhận thân JSON qua php://input)
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    // Kiểm tra định dạng JSON gửi lên
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['loi' => 'Thân yêu cầu phải là dữ liệu JSON hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sanPhamId = trim((string)($data['san_pham_id'] ?? $data['id'] ?? ''));
    $soLuong = filter_var($data['so_luong'] ?? $data['sl'] ?? 1, FILTER_VALIDATE_INT);

    if ($sanPhamId === '') {
        http_response_code(404);
        echo json_encode(['loi' => 'Thiếu mã sản phẩm'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($soLuong === false || $soLuong <= 0) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['loi' => 'Số lượng sản phẩm không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Kiểm tra sản phẩm có tồn tại trong CSDL không
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, ten, gia, tinh_trang FROM san_pham WHERE id = ?");
    $stmt->execute([$sanPhamId]);
    $sp = $stmt->fetch();

    if (!$sp) {
        http_response_code(404);
        echo json_encode(['loi' => 'Sản phẩm không tồn tại'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($sp['tinh_trang'] === 'Hết hàng') {
        http_response_code(422);
        echo json_encode(['loi' => 'Sản phẩm này hiện đã hết hàng'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Cập nhật giỏ hàng trong Session
    if (!isset($_SESSION['gio'])) {
        $_SESSION['gio'] = [];
    }

    $soLuongHienTai = $_SESSION['gio'][$sanPhamId] ?? 0;
    $_SESSION['gio'][$sanPhamId] = $soLuongHienTai + $soLuong;

    // Tính lại tổng số lượng món trong giỏ để JS cập nhật trên Header
    $tongSoLuong = array_sum($_SESSION['gio']);

    echo json_encode([
        'thanhCong' => true,
        'thongBao' => 'Đã thêm sản phẩm vào giỏ hàng',
        'tongSoLuong' => $tongSoLuong
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Phương thức không được hỗ trợ (DELETE, PUT...)
http_response_code(405);
header('Allow: GET, POST');
echo json_encode(['loi' => 'Phương thức không được hỗ trợ'], JSON_UNESCAPED_UNICODE);