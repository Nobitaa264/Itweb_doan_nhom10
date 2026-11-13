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
$lop         = '24CNTT2';$nganh       = 'Công nghệ thông tin';
$khoa        = 'Toán - Tin';
$truong      = 'Đại học Sư phạm - Đại học Đà Nẵng';
$email       = '3120224170@udn.edu.vn';$tenDangNhap = 'huutuan';

/* =========================
   CÁC HÀM BẢO MẬT DỰ PHÒNG (NẾU inc/ham.php CHƯA KHAI BÁO)
========================= */
if (!function_exists('e')) {
    function e(mixed $giaTri): string
    {
        if (is_array($giaTri) || is_object($giaTri) \vert{}\vert{}$giaTri === null) {
            return '';
        }
        return htmlspecialchars((string) $giaTri, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('chuoi')) {
    function chuoi(mixed $nguon, int|string $khoaHoacDoDai = 0, int$doDaiToiDa = 0): string
    {
        if (is_array($nguon) && is_string($khoaHoacDoDai)) {$giaTri = $nguon[$khoaHoacDoDai] ?? '';
            $maxLen =$doDaiToiDa;
        } else {
            $giaTri =$nguon;
            $maxLen = is_int($khoaHoacDoDai) ?$khoaHoacDoDai : 0;
        }

        if (!is_string($giaTri)) {
            return '';
        }

        $ketQua = trim($giaTri);
        if ($maxLen > 0) {$ketQua = mb_substr($ketQua, 0,$maxLen, 'UTF-8');
        }
        return $ketQua;
    }
}

if (!function_exists('csrfToken')) {
    function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        $postToken    =$_POST['csrf_token'] ?? $_POST['_csrf'] ?? $_POST['csrf'] ?? '';
        $headerToken  =$_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        $guiLen = is_string($postToken) &&$postToken !== ''
            ? $postToken
            : (is_string($headerToken) ?$headerToken : '');

        if (!is_string($sessionToken) || $sessionToken === '' \vert{}\vert{}$guiLen === '' || !hash_equals($sessionToken,$guiLen)) {
            http_response_code(403);
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
    $stmtApi =$pdo->prepare('
        SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
        FROM tv_ky_nang
        WHERE mssv = :mssv
        ORDER BY muc_do DESC, id ASC
    ');
    $stmtApi->execute([':mssv' =>$mssv]);
    $duLieuApi =$stmtApi->fetchAll(PDO::FETCH_ASSOC);

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
$stmtChuTrang =$pdo->prepare('
    SELECT tv.nguoi_dung_id, nd.ten_dang_nhap
    FROM thanh_vien tv
    LEFT JOIN nguoi_dung nd ON tv.nguoi_dung_id = nd.id
    WHERE tv.mssv = :mssv
    LIMIT 1
');
$stmtChuTrang->execute([':mssv' =>$mssv]);
$thongTinChu =$stmtChuTrang->fetch(PDO::FETCH_ASSOC);

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
    $thongTinChu['nguoi_dung_id'] !== null &&$sessionUserId !== null &&
    is_scalar($sessionUserId) &&
    (int) $sessionUserId === (int)$thongTinChu['nguoi_dung_id']
) {
    $laChuTrang = true;
} elseif ($sessionUsername !== '' &&$sessionUsername === $tenDangNhap) {$laChuTrang = true;
} elseif ($sessionMssv !== '' &&$sessionMssv === $mssv) {$laChuTrang = true;
}

/* =========================
   XỬ LÝ CÁC YÊU CẦU POST (Tăng lượt xem, Thêm lưu bút, Xóa lưu bút)
   Đảm bảo không thay đổi dữ liệu CSDL bằng phương thức GET
========================= */
$khoaPhienXem = 'da_xem_' .$mssv . '_' . date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Bắt buộc kiểm tra CSRF cho mọi yêu cầu POST (trả về 403 nếu thiếu/sai)
    kiemTraCsrf();

    $hanhDong = chuoi($_POST['hanh_dong'] ?? 'them_luu_but');

    // 2. Ghi nhận lượt xem qua Fetch POST
    if ($hanhDong === 'tang_luot_xem') {
        if (!isset($_SESSION[$khoaPhienXem])) {
            $stmtTangLuot =$pdo->prepare('
                INSERT INTO tv_luot_xem (mssv, ngay, so_luot)
                VALUES (:mssv, CURDATE(), 1)
                ON DUPLICATE KEY UPDATE so_luot = so_luot + 1
            ');
            $stmtTangLuot->execute([':mssv' =>$mssv]);
            $_SESSION[$khoaPhienXem] = true;
        }

        $stmtTong =$pdo->prepare('
            SELECT COALESCE(SUM(so_luot), 0)
            FROM tv_luot_xem
            WHERE mssv = :mssv
        ');
        $stmtTong->execute([':mssv' =>$mssv]);

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
            $file403 = __DIR__ . '/../../403.php';
            if (file_exists($file403)) {
                require $file403;
            } else {
                echo '<h1>403 Forbidden</h1><p>Truy cập bị từ chối: Chỉ chủ trang mới được xóa lời nhắn.</p>';
            }
            exit;
        }

        // Hỗ trợ cả tên tham số id_xoa hoặc id khi kiểm thử
        $idXoaRaw = $_POST['id_xoa'] ?? $_POST['id'] ?? null;
        $idXoa = is_scalar($idXoaRaw)
            ? filter_var($idXoaRaw,
