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

date_default_timezone_set('Asia/Ho_Chi_Minh');

/* =========================
   KẾT NỐI CSDL TƯƠNG THÍCH VỚI DỰ ÁN NHÓM
========================= */
$pdo = null;
$loiKetNoiCsdl = '';

try {
    if (isset($db) && $db instanceof PDO) {
        $pdo = $db;
    } elseif (function_exists('db')) {
        $pdo = db();
    } elseif (class_exists('App\Config\Database')) {
        $pdo = \App\Config\Database::connect();
    } elseif (file_exists(__DIR__ . '/../../inc/db.php')) {
        require_once __DIR__ . '/../../inc/db.php';
        if (isset($pdo) && $pdo instanceof PDO) {
            // Đã có $pdo từ inc/db.php
        } elseif (isset($db) && $db instanceof PDO) {
            $pdo = $db;
        } elseif (function_exists('db')) {
            $pdo = db();
        }
    }
} catch (Throwable $exDb) {
    $loiKetNoiCsdl = $exDb->getMessage();
}

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
   CÁC HÀM BẢO MẬT DỰ PHÒNG & BỌC AN TOÀN KIỂU DỮ LIỆU
========================= */
if (!function_exists('e')) {
    function e(mixed $giaTri): string
    {
        if (is_array($giaTri) or is_object($giaTri) or $giaTri === null) {
            return '';
        }
        return htmlspecialchars((string) $giaTri, ENT_QUOTES + ENT_SUBSTITUTE, 'UTF-8');
    }
}

function eSafe(mixed $giaTri): string
{
    if (is_array($giaTri) or is_object($giaTri) or $giaTri === null) {
        return '';
    }
    return e((string) $giaTri);
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

function docChuoi(array $mangNguon, string $khoa, int $doDaiToiDa = 200): string
{
    $giaTriTho = $mangNguon[$khoa] ?? '';
    if (!is_string($giaTriTho)) {
        return '';
    }

    try {
        $rf = new ReflectionFunction('chuoi');
        $thamSoDau = $rf->getParameters()[0] ?? null;
        $kieuDau = $thamSoDau && $thamSoDau->getType() ? (string) $thamSoDau->getType() : '';
        $soThamSo = $rf->getNumberOfParameters();

        if ($kieuDau === 'array') {
            $kq = $soThamSo >= 3 ? chuoi($mangNguon, $khoa, $doDaiToiDa) : chuoi($mangNguon, $khoa);
        } elseif ($soThamSo >= 2) {
            $kq = chuoi($giaTriTho, $doDaiToiDa);
        } else {
            $kq = chuoi($giaTriTho);
        }
    } catch (Throwable $ex) {
        $kq = trim($giaTriTho);
    }

    if ($doDaiToiDa > 0 && mb_strlen($kq, 'UTF-8') > $doDaiToiDa) {
        $kq = mb_substr($kq, 0, $doDaiToiDa, 'UTF-8');
    }
    return $kq;
}

function layTokenCsrfHienTai(): string
{
    try {
        if (function_exists('csrfToken')) {
            $tk = csrfToken();
            if (is_string($tk) && $tk !== '') {
                return $tk;
            }
        }
        if (function_exists('taoCsrfToken')) {
            $tk = taoCsrfToken();
            if (is_string($tk) && $tk !== '') {
                return $tk;
            }
        }
    } catch (Throwable $ex) {
    }

    if (!empty($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
    }
    if (!empty($_SESSION['_csrf']) && is_string($_SESSION['_csrf'])) {
        return $_SESSION['_csrf'];
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

if (!function_exists('csrfField')) {
    function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . eSafe(layTokenCsrfHienTai()) . '">';
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
   KHỞI TẠO CÁC BẢNG CẦN THIẾT (TÁCH RIÊNG TỪNG LỆNH EXEC AN TOÀN)
========================= */
if ($pdo instanceof PDO) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tv_luot_xem (
                mssv VARCHAR(20) NOT NULL,
                ngay DATE NOT NULL,
                so_luot INT NOT NULL DEFAULT 1,
                PRIMARY KEY (mssv, ngay)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Throwable $ex) {
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tv_ky_nang (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mssv VARCHAR(20) NOT NULL,
                ten_ky_nang VARCHAR(100) NOT NULL,
                nhom_ky_nang VARCHAR(100) NOT NULL,
                muc_do INT NOT NULL DEFAULT 80,
                mo_ta TEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $stmtCheckKn = $pdo->prepare('SELECT COUNT(*) FROM tv_ky_nang WHERE mssv = :mssv');
        $stmtCheckKn->execute([':mssv' => $mssv]);
        if ((int) $stmtCheckKn->fetchColumn() === 0) {
            $stmtSeedKn = $pdo->prepare('
                INSERT INTO tv_ky_nang (mssv, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta)
                VALUES (:m, :t, :n, :d, :mt)
            ');
            $duLieuMau = [
                ['PHP & PDO', 'Backend', 85, 'Xây dựng ứng dụng web với PHP 8, PDO Prepared Statement và bảo mật CSRF/XSS.'],
                ['MySQL Database', 'Database', 80, 'Thiết kế lược đồ CSDL quan hệ, truy vấn thống kê và phân trang.'],
                ['HTML5 & CSS3', 'Frontend', 88, 'Thiết kế giao diện Responsive chuẩn Flexbox, Grid và tuân thủ CSP.'],
                ['JavaScript ES6+', 'Frontend', 78, 'Xử lý DOM với textContent, kết nối Fetch API kèm X-CSRF-Token.'],
            ];
            foreach ($duLieuMau as $knMau) {
                $stmtSeedKn->execute([
                    ':m'  => $mssv,
                    ':t'  => $knMau[0],
                    ':n'  => $knMau[1],
                    ':d'  => $knMau[2],
                    ':mt' => $knMau[3],
                ]);
            }
        }
    } catch (Throwable $ex) {
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tv_luu_but (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mssv VARCHAR(20) NOT NULL,
                ten_nguoi_gui VARCHAR(100) NOT NULL,
                noi_dung TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Throwable $ex) {
    }
}

/* =========================
   CHỨC NĂNG API JSON CHO KỸ NĂNG
========================= */
$apiParam = docChuoi($_GET, 'api', 30);
if ($apiParam === 'ky_nang') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        kiemTraCsrf();
    }
    header('Content-Type: application/json; charset=utf-8');
    $duLieuApi = [];
    if ($pdo instanceof PDO) {
        try {
            $stmtApi = $pdo->prepare('
                SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
                FROM tv_ky_nang
                WHERE mssv = :mssv
                ORDER BY muc_do DESC, id ASC
            ');
            $stmtApi->execute([':mssv' => $mssv]);
            $duLieuApi = $stmtApi->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $ex) {
            $duLieuApi = [];
        }
    }

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
========================= */
$thongTinChu = false;
if ($pdo instanceof PDO) {
    try {
        $stmtChuTrang = $pdo->prepare('
            SELECT tv.nguoi_dung_id, nd.ten_dang_nhap
            FROM thanh_vien tv
            LEFT JOIN nguoi_dung nd ON tv.nguoi_dung_id = nd.id
            WHERE tv.mssv = :mssv
            LIMIT 1
        ');
        $stmtChuTrang->execute([':mssv' => $mssv]);
        $thongTinChu = $stmtChuTrang->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $ex) {
        $thongTinChu = false;
    }
}

$userHienTai = null;
try {
    $userHienTai = function_exists('nguoiDungHienTai')
        ? nguoiDungHienTai()
        : ($_SESSION['nguoi_dung'] ?? $_SESSION['user'] ?? null);
} catch (Throwable $ex) {
    $userHienTai = $_SESSION['nguoi_dung'] ?? $_SESSION['user'] ?? null;
}

$sessionUserId = is_array($userHienTai)
    ? ($userHienTai['id'] ?? $userHienTai['nguoi_dung_id'] ?? null)
    : ($_SESSION['nguoi_dung_id'] ?? $_SESSION['user_id'] ?? null);

$sessionUsername = '';
if (is_array($userHienTai)) {
    $rawU = $userHienTai['ten_dang_nhap'] ?? $userHienTai['tenDangNhap'] ?? $userHienTai['username'] ?? $userHienTai['email'] ?? '';
    $sessionUsername = is_string($rawU) ? trim($rawU) : '';
} elseif (isset($_SESSION['ten_dang_nhap']) && is_string($_SESSION['ten_dang_nhap'])) {
    $sessionUsername = trim($_SESSION['ten_dang_nhap']);
}

$sessionMssv = '';
if (is_array($userHienTai) && isset($userHienTai['mssv']) && is_scalar($userHienTai['mssv'])) {
    $sessionMssv = trim((string) $userHienTai['mssv']);
} elseif (isset($_SESSION['mssv']) && is_scalar($_SESSION['mssv'])) {
    $sessionMssv = trim((string) $_SESSION['mssv']);
}

$laChuTrang = false;
if (
    is_array($thongTinChu) &&
    isset($thongTinChu['nguoi_dung_id']) &&
    $thongTinChu['nguoi_dung_id'] !== null &&
    $sessionUserId !== null &&
    is_scalar($sessionUserId) &&
    (int) $sessionUserId === (int) $thongTinChu['nguoi_dung_id']
) {
    $laChuTrang = true;
} elseif ($sessionUsername !== '' && ($sessionUsername === $tenDangNhap or $sessionUsername === $email)) {
    $laChuTrang = true;
} elseif ($sessionMssv !== '' && $sessionMssv === $mssv) {
    $laChuTrang = true;
}

/* =========================
   XỬ LÝ CÁC YÊU CẦU POST (Tăng lượt xem, Thêm lưu bút, Xóa lưu bút)
========================= */
$khoaPhienXem = 'da_xem_' . $mssv . '_' . date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kiemTraCsrf();

    $hanhDong = docChuoi($_POST, 'hanh_dong', 50);
    if ($hanhDong === '') {
        $hanhDong = 'them_luu_but';
    }

    // 1. Ghi nhận lượt xem qua Fetch POST
    if ($hanhDong === 'tang_luot_xem') {
        $tongMoi = 0;
        if ($pdo instanceof PDO) {
            try {
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
                $tongMoi = (int) $stmtTong->fetchColumn();
            } catch (Throwable $ex) {
                $tongMoi = 0;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'            => true,
            'tong_luot_xem' => $tongMoi,
        ]);
        exit;
    }

    // 2. Xóa lời nhắn lưu bút (Kiểm tra chủ trang ở máy chủ + điều kiện AND mssv = :mssv)
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

        if ($idXoa !== false && ($pdo instanceof PDO)) {
            try {
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
            } catch (Throwable $ex) {
                $_SESSION['tv_flash_error'] = 'Không thể xóa lời nhắn lúc này.';
            }
        } else {
            $_SESSION['tv_flash_error'] = 'Mã lời nhắn cần xóa không hợp lệ.';
        }

        header('Location: gioithieu.php#so-luu-but');
        exit;
    }

    // 3. Thêm lời nhắn mới vào sổ lưu bút
    if ($hanhDong === 'them_luu_but') {
        $ten     = docChuoi($_POST, 'ten', 150);
        $loiNhan = docChuoi($_POST, 'loiNhan', 1500);
        if ($loiNhan === '') {
            $loiNhan = docChuoi($_POST, 'loi_nhan', 1500);
        }

        if ($ten === '' or $loiNhan === '') {
            $_SESSION['tv_flash_error'] = 'Vui lòng nhập đầy đủ họ tên và lời nhắn hợp lệ.';
        } elseif (mb_strlen($ten, 'UTF-8') > 100 or mb_strlen($loiNhan, 'UTF-8') > 1000) {
            $_SESSION['tv_flash_error'] = 'Tên hoặc lời nhắn vượt quá độ dài cho phép.';
        } elseif ($pdo instanceof PDO) {
            try {
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
            } catch (Throwable $ex) {
                $_SESSION['tv_flash_error'] = 'Không thể lưu lời nhắn vào CSDL.';
            }
        }

        header('Location: gioithieu.php#so-luu-but');
        exit;
    }
}

/* =========================
   CHỨC NĂNG 1: THỐNG KÊ LƯỢT XEM 7 NGÀY (tv_luot_xem)
========================= */
$luotXem = 0;
$danhSachLuotXem = [];
if ($pdo instanceof PDO) {
    try {
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
    } catch (Throwable $ex) {
        $luotXem = 0;
        $danhSachLuotXem = [];
    }
}

/* =========================
   CHỨC NĂNG 2: DANH SÁCH KỸ NĂNG (tv_ky_nang)
========================= */
$tuKhoaKyNang = docChuoi($_GET, 'q', 100);
$nhomLoc      = docChuoi($_GET, 'nhom', 100);
$sapXep       = docChuoi($_GET, 'sort', 30);
if ($sapXep === '') {
    $sapXep = 'muc_do_desc';
}

$cacNhomKyNang = [];
$danhSachKyNang = [];

if ($pdo instanceof PDO) {
    try {
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
    } catch (Throwable $ex) {
        $cacNhomKyNang = [];
        $danhSachKyNang = [];
    }
}

/* =========================
   CHỨC NĂNG 3: SỔ LƯU BÚT (tv_luu_but) — HIỂN THỊ & PHÂN TRANG
========================= */
$thongBaoLoi       = docChuoi($_SESSION, 'tv_flash_error', 500);
$thongBaoThanhCong = docChuoi($_SESSION, 'tv_flash_success', 500);
unset($_SESSION['tv_flash_error'], $_SESSION['tv_flash_success']);

$soMucMoiTrang = 5;
$thamSoTrang   = $_GET['page'] ?? $_GET['trang'] ?? 1;
$trangHienTai  = is_scalar($thamSoTrang)
    ? filter_var($thamSoTrang, FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]])
    : 1;

$tongSoLoiNhan = 0;
$tongSoTrang = 1;
$danhSachLoiNhan = [];

if ($pdo instanceof PDO) {
    try {
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
    } catch (Throwable $ex) {
        $danhSachLoiNhan = [];
    }
}

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
    <meta name="csrf-token" content="<?= eSafe(layTokenCsrfHienTai()) ?>">
    <title>Giới thiệu cá nhân - <?= eSafe($hoTen) ?></title>
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
                <p><strong>Họ và tên:</strong> <?= eSafe($hoTen) ?></p>
                <p><strong>MSSV:</strong> <?= eSafe($mssv) ?></p>
                <p><strong>Lớp:</strong> <?= eSafe($lop) ?></p>
                <p><strong>Ngành:</strong> <?= eSafe($nganh) ?></p>
                <p><strong>Khoa:</strong> <?= eSafe($khoa) ?></p>
                <p><strong>Trường:</strong> <?= eSafe($truong) ?></p>
                <p><strong>Email:</strong> <?= eSafe($email) ?></p>
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

        <p>Tổng số lượt xem trang: <strong id="tongLuotXem"><?= eSafe($luotXem) ?></strong></p>

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
                                <td><?= eSafe(date('d/m/Y', strtotime((string) $dongXem['ngay']))) ?></td>
                                <td><strong><?= eSafe($dongXem['so_luot']) ?></strong></td>
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
            <strong>Ngày giờ trên server:</strong> <?= eSafe(date('d/m/Y H:i:s')) ?>
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
                <input type="text" id="q" name="q" value="<?= eSafe($tuKhoaKyNang) ?>" placeholder="VD: PHP, MySQL...">
            </div>

            <div class="filter-group">
                <label for="nhom">Nhóm kỹ năng:</label>
                <select id="nhom" name="nhom">
                    <option value="">-- Tất cả nhóm --</option>
                    <?php foreach ($cacNhomKyNang as $tenNhom): ?>
                        <option value="<?= eSafe($tenNhom) ?>" <?= $nhomLoc === (string) $tenNhom ? 'selected' : '' ?>>
                            <?= eSafe($tenNhom) ?>
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
                <caption>Danh sách kỹ năng của <?= eSafe($hoTen) ?> (<?= eSafe(count($danhSachKyNang)) ?> mục)</caption>
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
                                <td><strong><?= eSafe($kn['ten_ky_nang']) ?></strong></td>
                                <td><?= eSafe($kn['nhom_ky_nang']) ?></td>
                                <td><strong><?= eSafe($kn['muc_do']) ?>%</strong></td>
                                <td class="text-left"><?= eSafe($kn['mo_ta'] ?? '') ?></td>
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
                <caption>Thời khóa biểu tuần của <?= eSafe($hoTen) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">Thời gian</th>
                        <?php foreach ($thuTrongTuan as $thu): ?>
                            <th scope="col"><?= eSafe($thu) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($thoiKhoaBieu as $buoi => $lichHoc): ?>
                        <tr>
                            <th scope="row"><?= eSafe($buoi) ?></th>
                            <?php foreach ($lichHoc as $hoatDong): ?>
                                <td><?= eSafe($hoatDong) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SỔ LƯU BÚT (CSDL + PHÂN TRANG + XÓA KHI ĐĂNG NHẬP CHỦ TRANG) -->
    <section class="content-section" id="so-luu-but">
        <h2 class="section-title">Sổ lưu bút (<?= eSafe($tongSoLoiNhan) ?> lời nhắn)</h2>

        <?php if ($laChuTrang): ?>
            <p class="success-message">
                🔑 Bạn đang đăng nhập với quyền <strong>Chủ trang (<?= eSafe($hoTen) ?>)</strong> — có thể xóa lời nhắn bên dưới.
            </p>
        <?php endif; ?>

        <?php if ($thongBaoLoi !== ''): ?>
            <p class="error-message" role="alert"><?= eSafe($thongBaoLoi) ?></p>
        <?php endif; ?>

        <?php if ($thongBaoThanhCong !== ''): ?>
            <p class="success-message" role="status"><?= eSafe($thongBaoThanhCong) ?></p>
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

        <h3 class="sub-title">Danh sách lời nhắn (Trang <?= eSafe($trangHienTai) ?> / <?= eSafe($tongSoTrang) ?>)</h3>

        <?php if (empty($danhSachLoiNhan)): ?>
            <p>Chưa có lời nhắn nào.</p>
        <?php else: ?>
            <?php foreach ($danhSachLoiNhan as $muc): ?>
                <div class="guestbook-message">
                    <div>
                        <p>
                            <strong><?= eSafe($muc['ten_nguoi_gui']) ?></strong>:
                            <?= nl2br(eSafe($muc['noi_dung'])) ?>
                        </p>
                        <small><?= eSafe(date('d/m/Y H:i:s', strtotime((string) $muc['created_at']))) ?></small>
                    </div>

                    <?php if ($laChuTrang): ?>
                        <form method="post" action="gioithieu.php" class="form-xoa-luu-but">
                            <?= csrfField() ?>
                            <input type="hidden" name="hanh_dong" value="xoa_luu_but">
                            <input type="hidden" name="id_xoa" value="<?= eSafe($muc['id']) ?>">
                            <button type="submit" class="btn-delete">Xóa</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($tongSoTrang > 1): ?>
                <nav class="pagination" aria-label="Phân trang sổ lưu bút">
                    <?php if ($trangHienTai > 1): ?>
                        <a href="?page=<?= eSafe($trangHienTai - 1) ?>#so-luu-but">« Trước</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $tongSoTrang; $i++): ?>
                        <?php if ($i === $trangHienTai): ?>
                            <span class="active"><?= eSafe($i) ?></span>
                        <?php else: ?>
                            <a href="?page=<?= eSafe($i) ?>#so-luu-but"><?= eSafe($i) ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($trangHienTai < $tongSoTrang): ?>
                        <a href="?page=<?= eSafe($trangHienTai + 1) ?>#so-luu-but">Sau »</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>

<script src="js/canhan.js"></script>
</body>
</html>
