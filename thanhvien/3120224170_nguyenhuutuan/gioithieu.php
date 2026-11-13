<?php
/*
 * Tệp: thanhvien/3120224170_nguyenhuutuan/gioithieu.php — Trang cá nhân Nguyễn Hữu Tuân (3120224170).
 * Đã rà soát và sửa theo 9 nhóm câu hỏi bảo mật (Slide 38):
 * 1. Mọi form method="post" có csrfField(), mọi fetch POST có header X-CSRF-Token.
 * 2. Mọi dữ liệu in ra qua e(), lời nhắn nhiều dòng qua nl2br(e()), JS hiển thị bằng textContent.
 * 3. Tham số chuỗi qua chuoi(), số nguyên qua filter_var (chống lỗi mảng ?page[]=1, ?trang[]=1, ten[]=...).
 * 4. Mọi truy vấn có dữ liệu người dùng đều dùng PDO Prepared Statement.
 * 5. Thao tác xóa kiểm tra chủ trang ở máy chủ (nguoiDungHienTai()) và có điều kiện AND mssv = :mssv.
 * 6. Không còn <script>, <style> hay sự kiện nội tuyến (onsubmit, onclick); không thay đổi dữ liệu bằng GET.
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
   CÁC HÀM BẢO MẬT DỰ PHÒNG (NẾU inc/ham.php CHƯA KHAI BÁO)
========================= */
if (!function_exists('e')) {
    function e(mixed $giaTri): string
    {
        if (is_array($giaTri)) {
            return '';
        }
        if (is_object($giaTri)) {
            return '';
        }
        if ($giaTri === null) {
            return '';
        }
        return htmlspecialchars((string) $giaTri, ENT_QUOTES + ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('chuoi')) {
    function chuoi(mixed $nguon, mixed $khoaHoacDoDai = 0, int $doDaiToiDa = 0): string
    {
        if (is_array($nguon) && is_string($khoaHoacDoDai)) {
            $giaTri = $nguon[$khoaHoacDoDai] ?? '';
            $maxLen = $doDaiToiDa;
        } else {
            $giaTri = $nguon;
            $maxLen = is_int($khoaHoacDoDai) ? $khoaHoacDoDai : 0;
        }

        if (!is_string($giaTri)) {
            return '';
        }

        $ketQua = trim($giaTri);
        if ($maxLen > 0) {
            $ketQua = mb_substr($ketQua, 0, $maxLen, 'UTF-8');
        }
        return $ketQua;
    }
}

if (!function_exists('csrfToken')) {
    function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token']) or !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrfField')) {
    function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
    }
}

if (!function_exists('kiemTraCsrf')) {
    function kiemTraCsrf(): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? $_SESSION['_csrf'] ?? '';
        $postToken    = $_POST['csrf_token'] ?? $_POST['_csrf'] ?? $_POST['csrf'] ?? '';
        $headerToken  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        $guiLen = (is_string($postToken) && $postToken !== '')
            ? $postToken
            : (is_string($headerToken) ? $headerToken : '');

        $hopLe = is_string($sessionToken)
            && $sessionToken !== ''
            && $guiLen !== ''
            && hash_equals($sessionToken, $guiLen);

        if (!$hopLe) {
            http_response_code(403);
            $goc = '../../';
            $file403 = __DIR__ . '/../../403.php';
            if (file_exists($file403)) {
                require $file403;
            } else {
                echo '<h1>403 Forbidden</h1><p>Mã xác thực CSRF không hợp lệ hoặc bị thiếu.</p>';
            }
            exit;
        }
    }
}

/* =========================
   CHỨC NĂNG API JSON CHO KỸ NĂNG
   Hỗ trợ cả GET (?api=ky_nang) và Fetch POST (có kiểm tra X-CSRF-Token)
========================= */
$apiParam = chuoi($_GET['api'] ?? '');
if ($apiParam === 'ky_nang') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        kiemTraCsrf();
    }
    header('Content-Type: application/json; charset=utf-8');
    $stmtApi = $pdo->prepare('
        SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
        FROM tv_ky_nang
        WHERE mssv = :mssv
        ORDER BY muc_do DESC, id ASC
    ');
    $stmtApi->execute([':mssv' => $mssv]);
    $duLieuApi = $stmtApi->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'thanh_vien' => $hoTen,
        'mssv'       => $mssv,
        'tong_so'    => count($duLieuApi),
        'du_lieu'    => $duLieuApi,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   KIỂM TRA QUYỀN CHỦ TRANG Ở MÁY CHỦ
   Sử dụng hàm nguoiDungHienTai() từ inc/xac-thuc.php và đối chiếu bảng thanh_vien
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

$userHienTai = function_exists('nguoiDungHienTai')
    ? nguoiDungHienTai()
    : ($_SESSION['nguoi_dung'] ?? $_SESSION['user'] ?? null);

$sessionUserId = is_array($userHienTai)
    ? ($userHienTai['id'] ?? $userHienTai['nguoi_dung_id'] ?? null)
    : ($_SESSION['nguoi_dung_id'] ?? $_SESSION['user_id'] ?? null);

$sessionUsername = is_array($userHienTai)
    ? chuoi($userHienTai['ten_dang_nhap'] ?? $userHienTai['tenDangNhap'] ?? $userHienTai['username'] ?? '')
    : chuoi($_SESSION['ten_dang_nhap'] ?? $_SESSION['username'] ?? '');

$sessionMssv = is_array($userHienTai)
    ? chuoi($userHienTai['mssv'] ?? '')
    : chuoi($_SESSION['mssv'] ?? '');

$laChuTrang = false;
if (
    $thongTinChu &&
    $thongTinChu['nguoi_dung_id'] !== null &&
    $sessionUserId !== null &&
    is_scalar($sessionUserId) &&
    (int) $sessionUserId === (int) $thongTinChu['nguoi_dung_id']
) {
    $laChuTrang = true;
} elseif ($sessionUsername !== '' && $sessionUsername === $tenDangNhap) {
    $laChuTrang = true;
} elseif ($sessionMssv !== '' && $sessionMssv === $mssv) {
    $laChuTrang = true;
}

/* =========================
   XỬ LÝ CÁC YÊU CẦU POST (Tăng lượt xem, Thêm lưu bút, Xóa lưu bút)
   Đảm bảo không thay đổi dữ liệu CSDL bằng phương thức GET
========================= */
$khoaPhienXem = 'da_xem_' . $mssv . '_' . date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Bắt buộc kiểm tra CSRF cho mọi yêu cầu POST (trả về 403 nếu thiếu/sai)
    kiemTraCsrf();

    $hanhDong = chuoi($_POST['hanh_dong'] ?? 'them_luu_but');

    // 2. Ghi nhận lượt xem qua Fetch POST
    if ($hanhDong === 'tang_luot_xem') {
        if (!isset($_SESSION[$khoaPhienXem])) {
            $stmtTangLuot = $pdo->prepare('
                INSERT INTO tv_luot_xem (mssv, ngay, so_luot)
                VALUES (:mssv, CURDATE(), 1)
                ON DUPLICATE KEY UPDATE so_luot = so_luot + 1
            ');
            $stmtTangLuot->execute([':mssv' => $mssv]);
            $_SESSION[$khoaPhienXem] = true;
        }

        $stmtTong = $pdo->prepare('
            SELECT COALESCE(SUM(so_luot), 0)
            FROM tv_luot_xem
            WHERE mssv = :mssv
        ');
        $stmtTong->execute([':mssv' => $mssv]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'            => true,
            'tong_luot_xem' => (int) $stmtTong->fetchColumn(),
        ]);
        exit;
    }

    // 3. Xóa lời nhắn lưu bút (Kiểm tra chủ trang ở máy chủ + điều kiện AND mssv = :mssv)
    if ($hanhDong === 'xoa_luu_but') {
        if (!$laChuTrang) {
            http_response_code(403);
            $goc = '../../';
            $file403 = __DIR__ . '/../../403.php';
            if (file_exists($file403)) {
                require $file403;
            } else {
                echo '<h1>403 Forbidden</h1><p>Truy cập bị từ chối: Chỉ chủ trang mới được xóa lời nhắn.</p>';
            }
            exit;
        }

        $idXoaRaw = $_POST['id_xoa'] ?? $_POST['id'] ?? null;
        $idXoa = is_scalar($idXoaRaw)
            ? filter_var($idXoaRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($idXoa !== false) {
            $stmtXoa = $pdo->prepare('
                DELETE FROM tv_luu_but
                WHERE id = :id AND mssv = :mssv
            ');
            $stmtXoa->execute([
                ':id'   => $idXoa,
                ':mssv' => $mssv,
            ]);

            if ($stmtXoa->rowCount() > 0) {
                $_SESSION['tv_flash_success'] = 'Đã xóa lời nhắn khỏi sổ lưu bút.';
            } else {
                $_SESSION['tv_flash_error'] = 'Không thể xóa: Lời nhắn không tồn tại hoặc không thuộc trang của bạn.';
            }
        } else {
            $_SESSION['tv_flash_error'] = 'Mã lời nhắn cần xóa không hợp lệ.';
        }

        redirect('gioithieu.php#so-luu-but');
    }

    // 4. Thêm lời nhắn mới vào sổ lưu bút (Đọc qua chuoi() chống lỗi mảng ten[]=...)
    if ($hanhDong === 'them_luu_but') {
        $ten     = chuoi($_POST['ten'] ?? '');
        $loiNhan = chuoi($_POST['loiNhan'] ?? $_POST['loi_nhan'] ?? '');

        if ($ten === '' or $loiNhan === '') {
            $_SESSION['tv_flash_error'] = 'Vui lòng nhập đầy đủ họ tên và lời nhắn hợp lệ.';
        } elseif (mb_strlen($ten, 'UTF-8') > 100 or mb_strlen($loiNhan, 'UTF-8') > 1000) {
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

        redirect('gioithieu.php#so-luu-but');
    }
}

/* =========================
   CHỨC NĂNG 1: THỐNG KÊ LƯỢT XEM 7 NGÀY (tv_luot_xem)
========================= */
$stmtTongLuot = $pdo->prepare('
    SELECT COALESCE(SUM(so_luot), 0)
    FROM tv_luot_xem
    WHERE mssv = :mssv
');
$stmtTongLuot->execute([':mssv' => $mssv]);
$luotXem = (int) $stmtTongLuot->fetchColumn();

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
   Tìm kiếm + Lọc theo nhóm + Sắp xếp (Đọc qua chuoi() chống lỗi mảng)
========================= */
$tuKhoaKyNang = chuoi($_GET['q'] ?? '', 100);
$nhomLoc      = chuoi($_GET['nhom'] ?? '', 100);
$sapXep       = chuoi($_GET['sort'] ?? 'muc_do_desc', 30);

$stmtNhom = $pdo->prepare('
    SELECT DISTINCT nhom_ky_nang
    FROM tv_ky_nang
    WHERE mssv = :mssv
    ORDER BY nhom_ky_nang ASC
');
$stmtNhom->execute([':mssv' => $mssv]);
$cacNhomKyNang = $stmtNhom->fetchAll(PDO::FETCH_COLUMN);

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
   CHỨC NĂNG 3: SỔ LƯU BÚT (tv_luu_but) — HIỂN THỊ & PHÂN TRANG
   Đọc số trang qua filter_var (Hỗ trợ cả ?page= và ?trang=, an toàn với ?trang[]=1)
========================= */
$thongBaoLoi       = chuoi($_SESSION['tv_flash_error'] ?? '');
$thongBaoThanhCong = chuoi($_SESSION['tv_flash_success'] ?? '');
unset($_SESSION['tv_flash_error'], $_SESSION['tv_flash_success']);

$soMucMoiTrang = 5;
$thamSoTrang   = $_GET['page'] ?? $_GET['trang'] ?? 1;
$trangHienTai  = is_scalar($thamSoTrang)
    ? filter_var($thamSoTrang, FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]])
    : 1;

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
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title>Giới thiệu cá nhân - <?= e($hoTen) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<main class="container main-content">

    <!-- NÚT ĐIỀU HƯỚNG -->
    <nav class="navigation-back" aria-label="Điều hướng trang">
        <a href="../../index.php" class="btn-home">← Trang chủ</a>
        <a href="../../gioi-thieu.php" class="btn-intro">← Trang Giới thiệu</a>
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
        <h2 class="section-title">Tương tác cá nhân & Thống kê lượt xem</h2>

        <p>Tổng số lượt xem trang: <strong id="tongLuotXem"><?= e($luotXem) ?></strong></p>

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
                    <option value="ten_asc" <?= $sapXep === 'ten_asc' ? 'selected' : '' ?>>Tên A → Z</option>
                    <option value="ten_desc" <?= $sapXep === 'ten_desc' ? 'selected' : '' ?>>Tên Z → A</option>
                </select>
            </div>

            <button type="submit" class="btn-action">Lọc & Sắp xếp</button>
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
                                <td class="text-left"><?= e($kn['mo_ta']) ?></td>
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
            <?= csrfField() ?>
            <input type="hidden" name="hanh_dong" value="them_luu_but">

            <label for="ten">Họ tên:</label>
            <input type="text" id="ten" name="ten" maxlength="100" required>

            <label for="loiNhan">Lời nhắn:</label>
            <textarea id="loiNhan" name="loiNhan" maxlength="1000" required></textarea>

            <button type="submit">Gửi lời nhắn</button>
        </form>

        <h3 class="sub-title">Danh sách lời nhắn (Trang <?= e($trangHienTai) ?> / <?= e($tongSoTrang) ?>)</h3>

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
                        <form method="post" action="gioithieu.php" class="form-xoa-luu-but">
                            <?= csrfField() ?>
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
                        <a href="?page=<?= e($trangHienTai - 1) ?>#so-luu-but">« Trước</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $tongSoTrang; $i++): ?>
                        <?php if ($i === $trangHienTai): ?>
                            <span class="active"><?= e($i) ?></span>
                        <?php else: ?>
                            <a href="?page=<?= e($i) ?>#so-luu-but"><?= e($i) ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($trangHienTai < $tongSoTrang): ?>
                        <a href="?page=<?= e($trangHienTai + 1) ?>#so-luu-but">Sau »</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

<script src="js/canhan.js"></script>
</body>
</html>
