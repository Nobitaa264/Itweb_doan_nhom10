<?php
/*
 * Tệp: thanhvien/3120224170_nguyenhuutuan/gioithieu.php — Trang cá nhân Nguyễn Hữu Tuân (3120224170).
 * Chức năng CSDL: (1) Danh sách kỹ năng (tv_ky_nang) có Tìm kiếm, Lọc, Sắp xếp + JSON API (?api=ky_nang) gọi bằng fetch;
 *                 (2) Sổ lưu bút (tv_luu_but) có phân trang, chủ trang đăng nhập mới xóa được; (3) Đếm lượt xem 7 ngày (tv_luot_xem).
 * Cách thử: Lọc/tìm kỹ năng hoặc bấm "Tải qua Fetch API"; gửi lưu bút, chuyển ?page=2; đăng nhập chủ trang để xóa lời nhắn.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

use App\Config\Database;

date_default_timezone_set('Asia/Ho_Chi_Minh');

$pdo = Database::connect();

/* =========================
   THÔNG TIN CÁ NHÂN
========================= */
$hoTen       = 'Nguyễn Hữu Tuân';
$mssv        = '3120224170';
$lop         = '24CNTT2';
$nganh       = 'Công nghệ thông tin';
$khoa        = 'Toán - Tin';
$truong      = 'Đại học Sư phạm - Đại học Đà Nẵng';
$email       = '3120224170@udn.edu.vn';
$tenDangNhap = 'huutuan';

/* =========================
   HÀM BẢO MẬT HIỂN THỊ (Đề phòng config.php chưa khai báo)
========================= */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/* =========================
   CHỨC NĂNG API JSON NHỎ CHO KỸ NĂNG (Hiển thị bằng fetch)
   Thử trực tiếp: gioithieu.php?api=ky_nang
========================= */
if (isset($_GET['api']) && $_GET['api'] === 'ky_nang') {
    header('Content-Type: application/json; charset=utf-8');
    $stmtApi = $pdo->prepare('
        SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
        FROM tv_ky_nang
        WHERE mssv = :mssv
        ORDER BY muc_do DESC, id ASC
    ');
    $stmtApi->execute([':mssv' => $mssv]);
    echo json_encode([
        'thanh_vien' => $hoTen,
        'mssv'       => $mssv,
        'tong_so'    => $stmtApi->rowCount(),
        'du_lieu'    => $stmtApi->fetchAll(PDO::FETCH_ASSOC),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   KIỂM TRA QUYỀN CHỦ TRANG Ở MÁY CHỦ
   Đối chiếu nguoi_dung_id trong bảng thanh_vien với session hiện tại
========================= */
$stmtChuTrang = $pdo->prepare('
    SELECT tv.nguoi_dung_id, nd.ten_dang_nhap
    FROM thanh_vien tv
    LEFT JOIN nguoi_dung nd ON tv.nguoi_dung_id = nd.id
    WHERE tv.mssv = :mssv
    LIMIT 1
');
$stmtChuTrang->execute([':mssv' => $mssv]);
$thongTinChu = $stmtChuTrang->fetch(PDO::FETCH_ASSOC);

// Thu thập ID và tên đăng nhập từ các cấu trúc $_SESSION phổ biến trong dự án
$sessionUserId = $_SESSION['nguoi_dung']['id']
    ?? $_SESSION['user']['id']
    ?? $_SESSION['nguoi_dung_id']
    ?? $_SESSION['user_id']
    ?? null;

$sessionUsername = $_SESSION['nguoi_dung']['ten_dang_nhap']
    ?? $_SESSION['user']['ten_dang_nhap']
    ?? $_SESSION['ten_dang_nhap']
    ?? $_SESSION['username']
    ?? '';

$laChuTrang = false;
if (
    $thongTinChu &&
    $thongTinChu['nguoi_dung_id'] !== null &&
    $sessionUserId !== null &&
    (int) $sessionUserId === (int) $thongTinChu['nguoi_dung_id']
) {
    $laChuTrang = true;
} elseif ($sessionUsername !== '' && $sessionUsername === $tenDangNhap) {
    $laChuTrang = true;
}

/* =========================
   CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM THEO NGÀY (tv_luot_xem)
   Dùng INSERT ... ON DUPLICATE KEY UPDATE
========================= */
$khoaPhienXem = 'da_xem_' . $mssv . '_' . date('Y-m-d');

if (!isset($_SESSION[$khoaPhienXem])) {
    $stmtTangLuot = $pdo->prepare('
        INSERT INTO tv_luot_xem (mssv, ngay, so_luot)
        VALUES (:mssv, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE so_luot = so_luot + 1
    ');
    $stmtTangLuot->execute([':mssv' => $mssv]);
    $_SESSION[$khoaPhienXem] = true;
}

// Tổng lượt xem
$stmtTongLuot = $pdo->prepare('
    SELECT COALESCE(SUM(so_luot), 0)
    FROM tv_luot_xem
    WHERE mssv = :mssv
');
$stmtTongLuot->execute([':mssv' => $mssv]);
$luotXem = (int) $stmtTongLuot->fetchColumn();

// Bảng 7 ngày gần nhất
$stmtBayNgay = $pdo->prepare('
    SELECT ngay, so_luot
    FROM tv_luot_xem
    WHERE mssv = :mssv
    ORDER BY ngay DESC
    LIMIT 7
');
$stmtBayNgay->execute([':mssv' => $mssv]);
$danhSachLuotXem = $stmtBayNgay->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CHỨC NĂNG 2: DANH SÁCH KỸ NĂNG (tv_ky_nang)
   Tìm kiếm + Lọc theo nhóm + Sắp xếp
========================= */
$tuKhoaKyNang = trim((string) ($_GET['q'] ?? ''));
$nhomLoc      = trim((string) ($_GET['nhom'] ?? ''));
$sapXep       = trim((string) ($_GET['sort'] ?? 'muc_do_desc'));

// Lấy các nhóm kỹ năng để hiển thị trong thẻ <select>
$stmtNhom = $pdo->prepare('
    SELECT DISTINCT nhom_ky_nang
    FROM tv_ky_nang
    WHERE mssv = :mssv
    ORDER BY nhom_ky_nang ASC
');
$stmtNhom->execute([':mssv' => $mssv]);
$cacNhomKyNang = $stmtNhom->fetchAll(PDO::FETCH_COLUMN);

// Xây dựng câu truy vấn Prepared Statement có lọc và sắp xếp an toàn
$sqlKyNang = '
    SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
    FROM tv_ky_nang
    WHERE mssv = :mssv
';
$thamSoKyNang = [':mssv' => $mssv];

if ($tuKhoaKyNang !== '') {
    $sqlKyNang .= ' AND (ten_ky_nang LIKE :kw1 OR mo_ta LIKE :kw2)';
    $thamSoKyNang[':kw1'] = '%' . $tuKhoaKyNang . '%';
    $thamSoKyNang[':kw2'] = '%' . $tuKhoaKyNang . '%';
}

if ($nhomLoc !== '') {
    $sqlKyNang .= ' AND nhom_ky_nang = :nhom';
    $thamSoKyNang[':nhom'] = $nhomLoc;
}

switch ($sapXep) {
    case 'muc_do_asc':
        $sqlKyNang .= ' ORDER BY muc_do ASC, ten_ky_nang ASC';
        break;
    case 'ten_asc':
        $sqlKyNang .= ' ORDER BY ten_ky_nang ASC';
        break;
    case 'ten_desc':
        $sqlKyNang .= ' ORDER BY ten_ky_nang DESC';
        break;
    default:
        $sapXep = 'muc_do_desc';
        $sqlKyNang .= ' ORDER BY muc_do DESC, ten_ky_nang ASC';
        break;
}

$stmtKyNang = $pdo->prepare($sqlKyNang);
$stmtKyNang->execute($thamSoKyNang);
$danhSachKyNang = $stmtKyNang->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CHỨC NĂNG 3: SỔ LƯU BÚT (tv_luu_but)
   Thêm lời nhắn (PRG) + Chủ trang xóa lời nhắn (Kiểm tra quyền ở Server) + Phân trang
========================= */
$thongBaoLoi       = (string) ($_SESSION['tv_flash_error'] ?? '');
$thongBaoThanhCong = (string) ($_SESSION['tv_flash_success'] ?? '');
unset($_SESSION['tv_flash_error'], $_SESSION['tv_flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = (string) ($_POST['hanh_dong'] ?? 'them_luu_but');

    // Xử lý xóa lời nhắn (Chỉ chủ trang mới có quyền)
    if ($hanhDong === 'xoa_luu_but') {
        if (!$laChuTrang) {
            $_SESSION['tv_flash_error'] = 'Truy cập bị từ chối: Chỉ chủ trang mới được xóa lời nhắn.';
        } else {
            $idXoa = filter_input(INPUT_POST, 'id_xoa', FILTER_VALIDATE_INT);
            if ($idXoa !== false && $idXoa !== null && $idXoa > 0) {
                $stmtXoa = $pdo->prepare('
                    DELETE FROM tv_luu_but
                    WHERE id = :id AND mssv = :mssv
                ');
                $stmtXoa->execute([
                    ':id'   => $idXoa,
                    ':mssv' => $mssv,
                ]);
                $_SESSION['tv_flash_success'] = 'Đã xóa lời nhắn khỏi sổ lưu bút.';
            } else {
                $_SESSION['tv_flash_error'] = 'Mã lời nhắn cần xóa không hợp lệ.';
            }
        }
        header('Location: gioithieu.php#so-luu-but');
        exit;
    }

    // Xử lý thêm lời nhắn mới
    if ($hanhDong === 'them_luu_but') {
        $ten     = trim((string) ($_POST['ten'] ?? ''));
        $loiNhan = trim((string) ($_POST['loiNhan'] ?? ''));

        if ($ten === '' || $loiNhan === '') {
            $_SESSION['tv_flash_error'] = 'Vui lòng nhập đầy đủ họ tên và lời nhắn.';
        } elseif (mb_strlen($ten, 'UTF-8') > 100 || mb_strlen($loiNhan, 'UTF-8') > 1000) {
            $_SESSION['tv_flash_error'] = 'Tên hoặc lời nhắn vượt quá độ dài cho phép.';
        } else {
            $stmtThem = $pdo->prepare('
                INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung)
                VALUES (:mssv, :ten_nguoi_gui, :noi_dung)
            ');
            $stmtThem->execute([
                ':mssv'          => $mssv,
                ':ten_nguoi_gui' => $ten,
                ':noi_dung'      => $loiNhan,
            ]);
            $_SESSION['tv_flash_success'] = 'Cảm ơn bạn đã gửi lời nhắn!';
        }
        header('Location: gioithieu.php#so-luu-but');
        exit;
    }
}

// Phân trang sổ lưu bút (5 lời nhắn / trang)
$soMucMoiTrang = 5;
$trangHienTai  = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($trangHienTai === false || $trangHienTai === null || $trangHienTai < 1) {
    $trangHienTai = 1;
}

$stmtDemLuuBut = $pdo->prepare('
    SELECT COUNT(*) FROM tv_luu_but WHERE mssv = :mssv
');
$stmtDemLuuBut->execute([':mssv' => $mssv]);
$tongSoLoiNhan = (int) $stmtDemLuuBut->fetchColumn();

$tongSoTrang = max(1, (int) ceil($tongSoLoiNhan / $soMucMoiTrang));
if ($trangHienTai > $tongSoTrang) {
    $trangHienTai = $tongSoTrang;
}
$viTriBatDau = ($trangHienTai - 1) * $soMucMoiTrang;

$stmtLuuBut = $pdo->prepare('
    SELECT id, ten_nguoi_gui, noi_dung, created_at
    FROM tv_luu_but
    WHERE mssv = :mssv
    ORDER BY created_at DESC, id DESC
    LIMIT :gioi_han OFFSET :vi_tri
');
$stmtLuuBut->bindValue(':mssv', $mssv, PDO::PARAM_STR);
$stmtLuuBut->bindValue(':gioi_han', $soMucMoiTrang, PDO::PARAM_INT);
$stmtLuuBut->bindValue(':vi_tri', $viTriBatDau, PDO::PARAM_INT);
$stmtLuuBut->execute();
$danhSachLoiNhan = $stmtLuuBut->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   THỜI KHÓA BIỂU TUẦN
========================= */
$thuTrongTuan = [
    'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'
];

$thoiKhoaBieu = [
    'Sáng'  => ['Học', 'Học', 'Học', 'Học', 'Học', 'Tự học', 'Nghỉ'],
    'Chiều' => ['Tự học', 'Học', 'Tự học', 'Học', 'Tự học', 'Làm bài tập', 'Nghỉ'],
    'Tối'   => ['Tự học', 'Làm bài tập', 'Tự học', 'Làm bài nhóm', 'Nghỉ', 'Giải trí', 'Chuẩn bị tuần mới']
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu cá nhân - <?= e($hoTen) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .navigation-back {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .navigation-back a {
            display: inline-block;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .navigation-back a:hover {
            opacity: 0.85;
        }
        .btn-home {
            background-color: #007bff;
        }
        .btn-intro {
            background-color: #6c757d;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }
        .schedule-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .schedule-table {
            min-width: 750px;
        }
        .data-table {
            margin-top: 12px;
        }
        .schedule-table caption,
        .data-table caption {
            font-size: 17px;
            font-weight: bold;
            padding: 10px;
        }
        .schedule-table th,
        .schedule-table td,
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
        }
        .schedule-table thead,
        .data-table thead {
            background-color: #007bff;
            color: white;
        }
        .schedule-table tbody tr:nth-child(even),
        .data-table tbody tr:nth-child(even) {
            background-color: #f2f6fc;
        }
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .filter-group input,
        .filter-group select {
            padding: 7px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn-action {
            padding: 8px 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .guestbook-message {
            margin: 15px 0;
            padding: 12px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .guestbook-message small {
            color: #666;
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .error-message {
            color: #dc3545;
            font-weight: 500;
        }
        .success-message {
            color: #198754;
            font-weight: 500;
        }
        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .pagination a,
        .pagination span {
            padding: 6px 12px;
            border: 1px solid #007bff;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
        }
        .pagination .active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .json-box {
            margin-top: 12px;
            padding: 12px;
            background: #eef6ff;
            border-left: 4px solid #007bff;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>

<main class="container main-content">

    <!-- NÚT ĐIỀU HƯỚNG -->
    <nav class="navigation-back" aria-label="Điều hướng trang">
        <a href="../../index.php" class="btn-home">&larr; Trang chủ</a>
        <a href="../../gioi-thieu.php" class="btn-intro">&larr; Trang Giới thiệu</a>
    </nav>

    <!-- GIỚI THIỆU BẢN THÂN -->
    <section class="content-section">
        <h1 class="section-title">Giới thiệu bản thân</h1>

        <div class="profile">
            <div class="profile-image">
                <img src="image.jpg" alt="Ảnh Nguyễn Hữu Tuân" width="150" height="200">
            </div>

            <div class="profile-info">
                <p><strong>Họ và tên:</strong> <?= e($hoTen) ?></p>
                <p><strong>MSSV:</strong> <?= e($mssv) ?></p>
                <p><strong>Lớp:</strong> <?= e($lop) ?></p>
                <p><strong>Ngành:</strong> <?= e($nganh) ?></p>
                <p><strong>Khoa:</strong> <?= e($khoa) ?></p>
                <p><strong>Trường:</strong> <?= e($truong) ?></p>
                <p><strong>Email:</strong> <?= e($email) ?></p>
            </div>
        </div>

        <p class="description">
            Xin chào, tôi là Nguyễn Hữu Tuân, hiện đang là sinh viên ngành Công nghệ thông tin
            thuộc Khoa Toán - Tin, Trường Đại học Sư phạm - Đại học Đà Nẵng.
        </p>
        <p class="description">
            Tôi yêu thích lập trình web, phát triển phần mềm và tìm hiểu những công nghệ mới.
            Trong quá trình học tập tôi luôn cố gắng nâng cao kiến thức về HTML, CSS, PHP, MySQL.
        </p>
        <p class="description">
            Mục tiêu của tôi là trở thành một lập trình viên có chuyên môn tốt, khả năng giải quyết vấn đề
            và tạo ra những sản phẩm công nghệ hữu ích.
        </p>
    </section>

    <!-- TƯƠNG TÁC CÁ NHÂN & BẢNG LƯỢT XEM 7 NGÀY GẦN NHẤT -->
    <section class="content-section">
        <h2 class="section-title">Tương tác cá nhân &amp; Thống kê lượt xem</h2>

        <p>Tổng số lượt xem trang: <strong><?= e($luotXem) ?></strong></p>

        <div class="table-container">
            <table class="data-table">
                <caption>Thống kê lượt xem 7 ngày gần nhất</caption>
                <thead>
                    <tr>
                        <th scope="col">Ngày</th>
                        <th scope="col">Số lượt xem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSachLuotXem)): ?>
                        <tr>
                            <td colspan="2">Chưa có dữ liệu lượt xem.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSachLuotXem as $dongXem): ?>
                            <tr>
                                <td><?= e(date('d/m/Y', strtotime((string) $dongXem['ngay']))) ?></td>
                                <td><strong><?= e($dongXem['so_luot']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="clock-box">
            🕒 Thời gian hiện tại: <span id="clock">00:00:00</span>
        </div>

        <p>
            <strong>Ngày giờ trên server:</strong> <?= e(date('d/m/Y H:i:s')) ?>
        </p>

        <button id="btnCopyEmail" type="button">📋 Sao chép Email</button>
        <p id="message"></p>
    </section>

    <!-- DỰ ÁN VÀ KỸ NĂNG (BẢNG RIÊNG tv_ky_nang: TÌM KIẾM, LỌC, SẮP XẾP + FETCH API) -->
    <section class="content-section" id="ky-nang">
        <h2 class="section-title">Dự án và Kỹ năng chuyên môn (CSDL)</h2>

        <p>
            Trong môn Thiết kế và Lập trình Web, tôi cùng nhóm 10 thực hiện dự án xây dựng
            website bán hàng quần áo <strong>StyleHub</strong>.
        </p>

        <form method="get" action="gioithieu.php#ky-nang" class="filter-bar">
            <div class="filter-group">
                <label for="q">Tìm kỹ năng:</label>
                <input type="text" id="q" name="q" value="<?= e($tuKhoaKyNang) ?>" placeholder="VD: PHP, MySQL...">
            </div>

            <div class="filter-group">
                <label for="nhom">Nhóm kỹ năng:</label>
                <select id="nhom" name="nhom">
                    <option value="">-- Tất cả nhóm --</option>
                    <?php foreach ($cacNhomKyNang as $tenNhom): ?>
                        <option value="<?= e($tenNhom) ?>" <?= $nhomLoc === $tenNhom ? 'selected' : '' ?>>
                            <?= e($tenNhom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort">Sắp xếp:</label>
                <select id="sort" name="sort">
                    <option value="muc_do_desc" <?= $sapXep === 'muc_do_desc' ? 'selected' : '' ?>>Mức độ giảm dần</option>
                    <option value="muc_do_asc" <?= $sapXep === 'muc_do_asc' ? 'selected' : '' ?>>Mức độ tăng dần</option>
                    <option value="ten_asc" <?= $sapXep === 'ten_asc' ? 'selected' : '' ?>>Tên A &rarr; Z</option>
                    <option value="ten_desc" <?= $sapXep === 'ten_desc' ? 'selected' : '' ?>>Tên Z &rarr; A</option>
                </select>
            </div>

            <button type="submit" class="btn-action">Lọc &amp; Sắp xếp</button>
            <a href="gioithieu.php#ky-nang" class="btn-action btn-secondary">Đặt lại</a>
            <button type="button" id="btnFetchApi" class="btn-action">⚡ Tải JSON qua Fetch API</button>
        </form>

        <div id="apiResultBox" class="json-box" aria-live="polite"></div>

        <div class="table-container">
            <table class="data-table">
                <caption>Danh sách kỹ năng của <?= e($hoTen) ?> (<?= e(count($danhSachKyNang)) ?> mục)</caption>
                <thead>
                    <tr>
                        <th scope="col">Kỹ năng</th>
                        <th scope="col">Nhóm</th>
                        <th scope="col">Mức độ (%)</th>
                        <th scope="col">Mô tả chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSachKyNang)): ?>
                        <tr>
                            <td colspan="4">Không tìm thấy kỹ năng phù hợp với bộ lọc.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSachKyNang as $kn): ?>
                            <tr>
                                <td><strong><?= e($kn['ten_ky_nang']) ?></strong></td>
                                <td><?= e($kn['nhom_ky_nang']) ?></td>
                                <td><strong><?= e($kn['muc_do']) ?>%</strong></td>
                                <td style="text-align: left;"><?= e($kn['mo_ta']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- THỜI KHÓA BIỂU TUẦN -->
    <section class="content-section">
        <h2 class="section-title">Thời khóa biểu tuần</h2>

        <div class="table-container">
            <table class="schedule-table">
                <caption>Thời khóa biểu tuần của <?= e($hoTen) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">Thời gian</th>
                        <?php foreach ($thuTrongTuan as $thu): ?>
                            <th scope="col"><?= e($thu) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($thoiKhoaBieu as $buoi => $lichHoc): ?>
                        <tr>
                            <th scope="row"><?= e($buoi) ?></th>
                            <?php foreach ($lichHoc as $hoatDong): ?>
                                <td><?= e($hoatDong) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SỔ LƯU BÚT (CSDL + PHÂN TRANG + XÓA KHI ĐĂNG NHẬP CHỦ TRANG) -->
    <section class="content-section" id="so-luu-but">
        <h2 class="section-title">Sổ lưu bút (<?= e($tongSoLoiNhan) ?> lời nhắn)</h2>

        <?php if ($laChuTrang): ?>
            <p class="success-message">
                🔑 Bạn đang đăng nhập với quyền <strong>Chủ trang (<?= e($hoTen) ?>)</strong> — có thể xóa lời nhắn bên dưới.
            </p>
        <?php endif; ?>

        <?php if ($thongBaoLoi !== ''): ?>
            <p class="error-message" role="alert"><?= e($thongBaoLoi) ?></p>
        <?php endif; ?>

        <?php if ($thongBaoThanhCong !== ''): ?>
            <p class="success-message" role="status"><?= e($thongBaoThanhCong) ?></p>
        <?php endif; ?>

        <form method="post" action="gioithieu.php">
            <input type="hidden" name="hanh_dong" value="them_luu_but">

            <label for="ten">Họ tên:</label>
            <input type="text" id="ten" name="ten" maxlength="100" required>

            <label for="loiNhan">Lời nhắn:</label>
            <textarea id="loiNhan" name="loiNhan" maxlength="1000" required></textarea>

            <button type="submit">Gửi lời nhắn</button>
        </form>

        <h3>Danh sách lời nhắn (Trang <?= e($trangHienTai) ?> / <?= e($tongSoTrang) ?>)</h3>

        <?php if (empty($danhSachLoiNhan)): ?>
            <p>Chưa có lời nhắn nào.</p>
        <?php else: ?>
            <?php foreach ($danhSachLoiNhan as $muc): ?>
                <div class="guestbook-message">
                    <div>
                        <p>
                            <strong><?= e($muc['ten_nguoi_gui']) ?></strong>:
                            <?= nl2br(e($muc['noi_dung'])) ?>
                        </p>
                        <small><?= e(date('d/m/Y H:i:s', strtotime((string) $muc['created_at']))) ?></small>
                    </div>

                    <?php if ($laChuTrang): ?>
                        <form method="post" action="gioithieu.php" onsubmit="return confirm('Xóa lời nhắn này khỏi sổ lưu bút?');">
                            <input type="hidden" name="hanh_dong" value="xoa_luu_but">
                            <input type="hidden" name="id_xoa" value="<?= e($muc['id']) ?>">
                            <button type="submit" class="btn-delete">Xóa</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($tongSoTrang > 1): ?>
                <nav class="pagination" aria-label="Phân trang sổ lưu bút">
                    <?php if ($trangHienTai > 1): ?>
                        <a href="?page=<?= e($trangHienTai - 1) ?>#so-luu-but">&laquo; Trước</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $tongSoTrang; $i++): ?>
                        <?php if ($i === $trangHienTai): ?>
                            <span class="active"><?= e($i) ?></span>
                        <?php else: ?>
                            <a href="?page=<?= e($i) ?>#so-luu-but"><?= e($i) ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($trangHienTai < $tongSoTrang): ?>
                        <a href="?page=<?= e($trangHienTai + 1) ?>#so-luu-but">Sau &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

<script src="js/canhan.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnFetch = document.getElementById('btnFetchApi');
    const resultBox = document.getElementById('apiResultBox');

    if (btnFetch && resultBox) {
        btnFetch.addEventListener('click', function () {
            resultBox.style.display = 'block';
            resultBox.textContent = 'Đang tải dữ liệu từ gioithieu.php?api=ky_nang ...';

            fetch('gioithieu.php?api=ky_nang')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    const danhSach = (data.du_lieu || []).map(function (item) {
                        return item.ten_ky_nang + ' (' + item.nhom_ky_nang + ' - ' + item.muc_do + '%)';
                    }).join(' | ');
                    resultBox.textContent = 'Kết quả JSON API (' + data.tong_so + ' kỹ năng): ' + danhSach;
                })
                .catch(function () {
                    resultBox.textContent = 'Không thể tải dữ liệu API.';
                });
        });
    }
});
</script>

</body>
</html>
