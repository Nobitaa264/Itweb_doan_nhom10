<?php
declare(strict_types=1);

/**
 * Trang cá nhân Dương Quang Sơn – TH6 Phần B, MSSV 3120224123.
 * Chức năng 1: Đăng ký lịch hẹn (TH5 JSONL -> MySQL, kiểm tra trùng).
 * Chức năng 2: Trắc nghiệm lập trình, lưu và xem lịch sử điểm MySQL.
 * Thử: gửi lịch hợp lệ / trùng, làm trắc nghiệm, xem lịch sử, F5 và tắt JS.
 */

require_once __DIR__ . '/../../inc/config.php';
if (is_file(__DIR__ . '/../../inc/db.php')) {
    require_once __DIR__ . '/../../inc/db.php';
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

const SON_MSSV = '3120224123';

// Tương thích với hàm db() của nhóm; dự phòng cho môi trường XAMPP.
function sonPdo(): PDO
{
    static $ketNoi = null;
    if ($ketNoi instanceof PDO) {
        return $ketNoi;
    }
    if (function_exists('db')) {
        $ketNoi = db();
        if (!$ketNoi instanceof PDO) {
            throw new RuntimeException('Hàm db() phải trả về đối tượng PDO.');
        }
        return $ketNoi;
    }
    $ketNoi = new PDO(
        'mysql:host=127.0.0.1;dbname=ltweb_nhom10;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $ketNoi;
}

function sonH(mixed $giaTri): string
{
    return htmlspecialchars((string) $giaTri, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Cấu hình trang cá nhân.
$goc = '../../';
$tieuDe = 'Giới thiệu cá nhân - Dương Quang Sơn';
$trang = '';


$khungGio = [
    '08:00' => '08:00 - 09:00',
    '09:30' => '09:30 - 10:30',
    '14:00' => '14:00 - 15:00',
    '15:30' => '15:30 - 16:30'
];

// ==================================================
// CHỨC NĂNG 2 - DỮ LIỆU TRẮC NGHIỆM
// ==================================================

$cauHoi = [
    1 => [
        'hoi' => 'HTML được sử dụng chủ yếu để làm gì?',
        'luaChon' => [
            'a' => 'Tạo cấu trúc nội dung website',
            'b' => 'Quản lý cơ sở dữ liệu',
            'c' => 'Chạy máy chủ',
            'd' => 'Thiết kế phần cứng'
        ],
        'dung' => 'a',
        'giaiThich' => 'HTML định nghĩa cấu trúc của trang web.'
    ],
    2 => [
        'hoi' => 'Thuộc tính CSS nào đổi màu chữ?',
        'luaChon' => [
            'a' => 'background',
            'b' => 'color',
            'c' => 'padding',
            'd' => 'display'
        ],
        'dung' => 'b',
        'giaiThich' => 'Thuộc tính color dùng để thiết lập màu chữ.'
    ],
    3 => [
        'hoi' => 'PHP thường được dùng để làm gì?',
        'luaChon' => [
            'a' => 'Thiết kế phần cứng',
            'b' => 'Chỉ chỉnh màu sắc',
            'c' => 'Xử lý chương trình phía máy chủ',
            'd' => 'Thay thế hệ điều hành'
        ],
        'dung' => 'c',
        'giaiThich' => 'PHP xử lý yêu cầu và dữ liệu ở máy chủ.'
    ],
    4 => [
        'hoi' => 'Phương thức nào thường dùng để gửi biểu mẫu cần lưu dữ liệu?',
        'luaChon' => [
            'a' => 'POST',
            'b' => 'HEAD',
            'c' => 'TRACE',
            'd' => 'OPTIONS'
        ],
        'dung' => 'a',
        'giaiThich' => 'POST thường được dùng để gửi dữ liệu cần xử lý.'
    ],
    5 => [
        'hoi' => 'Biến phiên làm việc trong PHP dùng mảng nào?',
        'luaChon' => [
            'a' => '$_GET',
            'b' => '$_FILES',
            'c' => '$_COOKIE',
            'd' => '$_SESSION'
        ],
        'dung' => 'd',
        'giaiThich' => '$_SESSION lưu dữ liệu của phiên người dùng.'
    ]
];

if (!isset($_SESSION['son_csrf'])) {
    $_SESSION['son_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['son_csrf'];

$du = [
    'hoTen' => '',
    'ngay' => '',
    'gio' => '',
    'noiDung' => ''
];

$loiLich = [];
$loiTracNghiem = '';

// Giá trị ô Bí danh khi chưa POST hoặc gửi sai kiểu phải là chuỗi rỗng.
// Đọc một lần để không truy cập khóa $_POST['bi_danh'] chưa tồn tại.
$biDanhDaNhap = $_POST['bi_danh'] ?? '';
if (!is_string($biDanhDaNhap)) {
    $biDanhDaNhap = '';
}

$thongBao = $_SESSION['son_thong_bao'] ?? '';
$ketQua = $_SESSION['son_ket_qua'] ?? null;

unset(
    $_SESSION['son_thong_bao'],
    $_SESSION['son_ket_qua']
);

$muiGio = new DateTimeZone('Asia/Ho_Chi_Minh');
$homNay = new DateTimeImmutable('today', $muiGio);

$ngayMin = $homNay->format('Y-m-d');
$ngayMax = $homNay->modify('+90 days')->format('Y-m-d');

// ==================================================
// XỬ LÝ POST PHÍA MÁY CHỦ
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hanhDong = $_POST['hanh_dong'] ?? '';
    $token = $_POST['csrf'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($csrf, $token)
    ) {
        http_response_code(400);
        $thongBao = 'Phiên gửi không hợp lệ. Hãy tải lại trang.';
    } elseif ($hanhDong === 'lich_hen') {

        // ==========================================
        // CHỨC NĂNG PHP 1 - ĐĂNG KÝ LỊCH HẸN
        // ==========================================

        foreach (array_keys($du) as $truong) {
            $giaTri = $_POST[$truong] ?? '';

            $du[$truong] = is_string($giaTri)
                ? trim($giaTri)
                : '';
        }

        if (
            mb_strlen($du['hoTen']) < 2 ||
            mb_strlen($du['hoTen']) > 60 ||
            !preg_match(
                "/^[\p{L}\p{M}\s'.-]+$/u",
                $du['hoTen']
            )
        ) {
            $loiLich['hoTen'] =
                'Họ tên từ 2 đến 60 ký tự, không chứa mã HTML.';
        }

        if (
            mb_strlen($du['noiDung']) < 10 ||
            mb_strlen($du['noiDung']) > 500
        ) {
            $loiLich['noiDung'] =
                'Nội dung phải từ 10 đến 500 ký tự.';
        }

        $ngayHen = false;

        if (
            preg_match(
                '/^\d{4}-\d{2}-\d{2}$/D',
                $du['ngay']
            )
        ) {
            $ngayHen = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $du['ngay'],
                $muiGio
            );
        }

        if (
            $ngayHen === false ||
            $ngayHen->format('Y-m-d') !== $du['ngay'] ||
            $du['ngay'] < $ngayMin ||
            $du['ngay'] > $ngayMax
        ) {
            $loiLich['ngay'] =
                'Ngày hẹn phải từ hôm nay đến 90 ngày tiếp theo.';
        }

        if (!array_key_exists($du['gio'], $khungGio)) {
            $loiLich['gio'] =
                'Vui lòng chọn khung giờ hợp lệ.';
        }

        if ($loiLich === []) {

            // Lưu trực tiếp MySQL, không còn ghi tệp JSONL như TH5.
            // Khóa UNIQUE (mssv, ngay_hen, gio_hen) tránh trùng giờ hẹn.
            try {
                $luuHen = sonPdo()->prepare(
                    'INSERT INTO tv_son_lich_hen
                     (mssv, ho_ten, ngay_hen, gio_hen, noi_dung)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $luuHen->execute([
                    SON_MSSV,
                    $du['hoTen'],
                    $du['ngay'],
                    $du['gio'],
                    $du['noiDung']
                ]);
            } catch (PDOException $ex) {
                if ((string) $ex->getCode() === '23000' &&
                    (int) ($ex->errorInfo[1] ?? 0) === 1062) {
                    $loiLich['gio'] = 'Khung giờ này đã có người đặt. Hãy chọn giờ khác.';
                } else {
                    throw $ex;
                }
            }

            if ($loiLich === []) {
            $_SESSION['son_thong_bao'] =
                'Đăng ký lịch hẹn thành công!';

            header(
                'Location: gioithieu.php#lich-hen',
                true,
                303
            );
            exit;
            }
        }

    } elseif ($hanhDong === 'trac_nghiem') {

        // ==========================================
        // CHỨC NĂNG PHP 2 - CHẤM TRẮC NGHIỆM
        // ==========================================

        // Bí danh dùng để hiển thị lịch sử, không yêu cầu thông tin thật.
        $biDanhNhap = $_POST['bi_danh'] ?? '';
        $biDanh = is_string($biDanhNhap) ? trim($biDanhNhap) : '';
        if ($biDanh === '') {
            $biDanh = 'Khách';
        }
        if (mb_strlen($biDanh) > 40) {
            $loiTracNghiem = 'Bí danh không quá 40 ký tự.';
        }

        $dapAnNhap = $_POST['dap_an'] ?? [];

        if (!is_array($dapAnNhap)) {
            $dapAnNhap = [];
        }

        $hopLe = count($dapAnNhap) === count($cauHoi);

        foreach ($cauHoi as $so => $cau) {
            $chon = $dapAnNhap[$so] ?? null;

            if (
                !is_string($chon) ||
                !array_key_exists($chon, $cau['luaChon'])
            ) {
                $hopLe = false;
            }
        }

        if (!$hopLe) {
            $loiTracNghiem =
                'Vui lòng chọn đủ 5 đáp án hợp lệ.';
        } elseif ($loiTracNghiem === '') {
            $soCauDung = 0;
            $chiTiet = [];

            foreach ($cauHoi as $so => $cau) {
                $chon = $dapAnNhap[$so];
                $dung = $chon === $cau['dung'];

                if ($dung) {
                    $soCauDung++;
                }

                $chiTiet[] = [
                    'cau' => $cau['hoi'],
                    'daChon' => $cau['luaChon'][$chon],
                    'dapAnDung' =>
                        $cau['luaChon'][$cau['dung']],
                    'dung' => $dung,
                    'giaiThich' => $cau['giaiThich']
                ];
            }

            $_SESSION['son_ket_qua'] = [
                'soCauDung' => $soCauDung,
                'tongCau' => count($cauHoi),
                'diem' => $soCauDung * 2,
                'chiTiet' => $chiTiet
            ];

            // TH6: ghi lịch sử điểm vào MySQL. PDO prepared statement.
            $luuBai = sonPdo()->prepare(
                'INSERT INTO tv_son_trac_nghiem
                 (mssv, bi_danh, so_cau_dung, tong_cau, diem, chi_tiet)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $luuBai->execute([
                SON_MSSV, $biDanh, $soCauDung, count($cauHoi),
                $soCauDung * 2,
                json_encode($chiTiet, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            ]);

            header(
                'Location: gioithieu.php#ket-qua',
                true,
                303
            );
            exit;
        }
    } else {
        http_response_code(400);
        $thongBao = 'Yêu cầu không hợp lệ.';
    }
}

// ==================================================
// TH6: LỊCH SỬ TRẮC NGHIỆM BẰNG PDO, 10 BÀI MỚI NHẤT
// ==================================================
$diemTuNhap = $_GET['diem_tu'] ?? '0';
$diemChoPhep = ['0', '2', '4', '6', '8', '10'];
$diemTu = is_string($diemTuNhap) && in_array($diemTuNhap, $diemChoPhep, true)
    ? (int) $diemTuNhap : 0;

$docLichSu = sonPdo()->prepare(
    'SELECT id, bi_danh, so_cau_dung, tong_cau, diem, created_at
     FROM tv_son_trac_nghiem
     WHERE mssv = ? AND diem >= ?
     ORDER BY id DESC LIMIT 10'
);
$docLichSu->bindValue(1, SON_MSSV, PDO::PARAM_STR);
$docLichSu->bindValue(2, $diemTu, PDO::PARAM_INT);
$docLichSu->execute();
$lichSuTracNghiem = $docLichSu->fetchAll(PDO::FETCH_ASSOC);

// ==================================================
// ==================================================
// SỬ DỤNG HEADER CHUNG CỦA NHÓM
// ==================================================

// Header chung đã chứa <html>, <head> và <body>.
// Nạp CSS riêng của Sơn vào trước </head>
// mà không cần sửa file inc/header.php của nhóm.

ob_start();
require __DIR__ . '/../../inc/header.php';
$htmlHeader = ob_get_clean();

if ($htmlHeader === false) {
    throw new RuntimeException('Không tải được header.');
}

$cssVaJs = '
<link rel="stylesheet" href="css/style.css">
<script src="js/canhan.js" defer></script>
';

if (strpos($htmlHeader, '</head>') === false) {
    throw new RuntimeException(
        'Header nhóm không có thẻ đóng head.'
    );
}

echo str_replace(
    '</head>',
    $cssVaJs . '</head>',
    $htmlHeader
);
?>

<!-- MENU ĐIỀU HƯỚNG RIÊNG CỦA SƠN -->

<nav class="menu-son" aria-label="Điều hướng trang cá nhân">
    <div class="bao">
        <a href="../../index.php">Trang chủ</a>
        <a href="../../danh-sach.php">Sản phẩm</a>
        <a href="../../gioi-thieu.php">Giới thiệu nhóm</a>
        <a href="../../lien-he.php">Liên hệ</a>
        <a href="../../gio-hang.php">Giỏ hàng</a>
        <a href="../../dang-nhap.php">Đăng nhập</a>
    </div>
</nav>

<main class="noi-dung">
    <div class="bao">

        <h1>Trang giới thiệu thành viên - Sơn</h1>

        <?php if ($thongBao !== ''): ?>
            <section class="khoi" role="status">
                <p><?= e($thongBao) ?></p>
            </section>
        <?php endif; ?>

        <!-- CHẾ ĐỘ SÁNG TỐI -->

        <section class="khoi dieu-khien-giao-dien">

            <h2>Tùy chỉnh giao diện</h2>

            <p>
                Bạn có thể chuyển đổi giữa giao diện
                sáng và tối.
            </p>

            <button
                class="nut nut-giao-dien"
                id="nut-giao-dien"
                type="button"
                aria-pressed="false">
                🌙 Chế độ tối
            </button>

            <p
                id="thong-bao-giao-dien"
                class="thong-bao"
                aria-live="polite"></p>

        </section>

        <!-- GIỚI THIỆU -->

        <section class="khoi gioi-thieu">

            <div class="gioi-thieu__anh">
                <img
                    src="images/son.jpg"
                    alt="Ảnh chân dung của Sơn"
                    width="200"
                    height="200">
            </div>

            <div class="gioi-thieu__noi-dung">

                <h2>Giới thiệu bản thân</h2>

                <p>
                    <strong>Họ và tên:</strong>
                    Dương Quang Sơn
                </p>

                <p>
                    <strong>Mã sinh viên:</strong>
                    3120224123
                </p>

                <p>
                    <strong>Lớp:</strong>
                    24CNTT2
                </p>

                <p>
                    <strong>Khoa:</strong>
                    Toán - Tin
                </p>

                <p>
                    <strong>Trường:</strong>
                    Đại học Sư phạm - Đại học Đà Nẵng
                </p>

                <p>
                    Tôi là sinh viên Công nghệ thông tin.
                    Tôi yêu thích lập trình, công nghệ
                    và phát triển website.
                </p>

            </div>
        </section>

        <!-- DỰ ÁN VÀ KỸ NĂNG -->

        <article class="khoi">

            <h2>Dự án và sở thích</h2>

            <p>
                Trong môn Thiết kế và Lập trình Web,
                tôi cùng nhóm thực hiện website bán
                quần áo StyleHub.
            </p>

            <p>
                Tôi cũng quan tâm đến dự án xây dựng
                website tìm việc dành cho sinh viên
                tích hợp trí tuệ nhân tạo.
            </p>

            <p>
                Ngoài học tập, tôi yêu thích công nghệ
                và các ứng dụng của AI.
            </p>

        </article>

        <!-- CHỨC NĂNG PHP 1 - LỊCH HẸN LƯU MYSQL -->

        <section class="khoi" id="lich-hen">

            <h2>Đăng ký lịch hẹn trao đổi học tập</h2>

            <p>
                Điền thông tin để đăng ký trao đổi
                về lập trình, học tập hoặc dự án. Lịch hẹn được lưu vào MySQL.
            </p>

            <form method="post" action="gioithieu.php#lich-hen">

                <input
                    type="hidden"
                    name="hanh_dong"
                    value="lich_hen">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e($csrf) ?>">

                <p>
                    <label for="hoTen">Họ và tên</label>

                    <input
                        type="text"
                        id="hoTen"
                        name="hoTen"
                        minlength="2"
                        maxlength="60"
                        required
                        value="<?= e($du['hoTen']) ?>">

                    <?php if (isset($loiLich['hoTen'])): ?>
                        <span class="loi-bieu-mau">
                            <?= e($loiLich['hoTen']) ?>
                        </span>
                    <?php endif; ?>
                </p>

                <p>
                    <label for="ngay">Ngày hẹn</label>

                    <input
                        type="date"
                        id="ngay"
                        name="ngay"
                        min="<?= e($ngayMin) ?>"
                        max="<?= e($ngayMax) ?>"
                        required
                        value="<?= e($du['ngay']) ?>">

                    <?php if (isset($loiLich['ngay'])): ?>
                        <span class="loi-bieu-mau">
                            <?= e($loiLich['ngay']) ?>
                        </span>
                    <?php endif; ?>
                </p>

                <p>
                    <label for="gio">Khung giờ</label>

                    <select id="gio" name="gio" required>

                        <option value="">-- Chọn giờ --</option>

                        <?php foreach ($khungGio as $ma => $nhan): ?>
                            <option
                                value="<?= e($ma) ?>"
                                <?= $du['gio'] === $ma
                                    ? 'selected'
                                    : '' ?>>
                                <?= e($nhan) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <?php if (isset($loiLich['gio'])): ?>
                        <span class="loi-bieu-mau">
                            <?= e($loiLich['gio']) ?>
                        </span>
                    <?php endif; ?>
                </p>

                <p>
                    <label for="noiDung">
                        Nội dung trao đổi
                    </label>

                    <textarea
                        id="noiDung"
                        name="noiDung"
                        rows="4"
                        minlength="10"
                        maxlength="500"
                        required><?= e($du['noiDung']) ?></textarea>

                    <?php if (isset($loiLich['noiDung'])): ?>
                        <span class="loi-bieu-mau">
                            <?= e($loiLich['noiDung']) ?>
                        </span>
                    <?php endif; ?>
                </p>

                <button class="nut" type="submit">
                    Gửi đăng ký lịch hẹn
                </button>

            </form>

        </section>

        <!-- CHỨC NĂNG 2 - TRẮC NGHIỆM VÀ LỊCH SỬ MYSQL -->

        <section class="khoi" id="trac-nghiem">

            <h2>Trắc nghiệm kiến thức lập trình</h2>

            <p>
                Trả lời 5 câu hỏi HTML, CSS và PHP.
                Mỗi câu đúng được 2 điểm. Kết quả được lưu vào MySQL.
            </p>

            <?php if ($loiTracNghiem !== ''): ?>
                <p class="loi-bieu-mau" role="alert">
                    <?= e($loiTracNghiem) ?>
                </p>
            <?php endif; ?>

            <form
                method="post"
                action="gioithieu.php#trac-nghiem">

                <input
                    type="hidden"
                    name="hanh_dong"
                    value="trac_nghiem">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e($csrf) ?>">

                <p>
                    <label for="son-bi-danh">Bí danh (không bắt buộc)</label>
                    <input id="son-bi-danh" name="bi_danh" type="text"
                        maxlength="40" placeholder="Ví dụ: Bạn học IT"
                        value="<?= sonH($biDanhDaNhap) ?>">
                </p>

                <?php foreach ($cauHoi as $so => $cau): ?>

                    <fieldset class="cau-hoi">

                        <legend>
                            Câu <?= e($so) ?>:
                            <?= e($cau['hoi']) ?>
                        </legend>

                        <?php foreach (
                            $cau['luaChon'] as $ma => $dapAn
                        ): ?>

                            <label class="lua-chon">

                                <input
                                    type="radio"
                                    name="dap_an[<?= e($so) ?>]"
                                    value="<?= e($ma) ?>"
                                    required
                                    <?= (
                                        isset($_POST['dap_an']) &&
                                        is_array($_POST['dap_an']) &&
                                        ($_POST['dap_an'][$so] ?? null) === $ma
                                    ) ? 'checked' : '' ?>>

                                <?= e($dapAn) ?>

                            </label>

                        <?php endforeach; ?>

                    </fieldset>

                <?php endforeach; ?>

                <button class="nut" type="submit">
                    Nộp bài và xem điểm
                </button>

            </form>

        </section>

        <!-- KẾT QUẢ TRẮC NGHIỆM -->

        <?php if (is_array($ketQua)): ?>

            <section class="khoi" id="ket-qua">

                <h2>Kết quả bài trắc nghiệm</h2>

                <p>
                    <strong>Số câu đúng:</strong>
                    <?= e($ketQua['soCauDung']) ?>
                    /
                    <?= e($ketQua['tongCau']) ?>
                </p>

                <p>
                    <strong>Điểm:</strong>
                    <?= e($ketQua['diem']) ?>/10
                </p>

                <?php foreach ($ketQua['chiTiet'] as $chiTiet): ?>

                    <div class="ket-qua-cau">

                        <h3><?= e($chiTiet['cau']) ?></h3>

                        <p>
                            Bạn chọn:
                            <?= e($chiTiet['daChon']) ?>
                        </p>

                        <p>
                            <strong>
                                <?= $chiTiet['dung']
                                    ? 'Đúng'
                                    : 'Sai' ?>
                            </strong>
                        </p>

                        <p>
                            Đáp án đúng:
                            <?= e($chiTiet['dapAnDung']) ?>
                        </p>

                        <p>
                            <?= e($chiTiet['giaiThich']) ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

        <!-- TH6 - CHỨC NĂNG 2: LỊCH SỬ KẾT QUẢ MYSQL -->

        <section class="khoi" id="lich-su-trac-nghiem">
            <h2>Lịch sử điểm trắc nghiệm (MySQL)</h2>
            <p>Hiển thị 10 lượt làm bài gần nhất. Lọc theo mức điểm tối thiểu.</p>
            <form method="get" action="gioithieu.php#lich-su-trac-nghiem">
                <label for="diem_tu">Điểm tối thiểu</label>
                <select id="diem_tu" name="diem_tu">
                    <?php foreach ([0, 2, 4, 6, 8, 10] as $mucDiem): ?>
                        <option value="<?= sonH($mucDiem) ?>"
                            <?= $diemTu === $mucDiem ? 'selected' : '' ?>>
                            <?= sonH($mucDiem) ?>/10
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="nut" type="submit">Xem lịch sử</button>
            </form>

            <?php if ($lichSuTracNghiem === []): ?>
                <p>Chưa có bài làm phù hợp.</p>
            <?php else: ?>
                <div class="bang-cuon">
                    <table>
                        <caption>Lịch sử điểm trắc nghiệm của trang Sơn</caption>
                        <thead>
                            <tr>
                                <th scope="col">Bí danh</th>
                                <th scope="col">Số câu đúng</th>
                                <th scope="col">Điểm</th>
                                <th scope="col">Ngày làm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lichSuTracNghiem as $lan): ?>
                                <tr>
                                    <td><?= sonH($lan['bi_danh']) ?></td>
                                    <td><?= sonH($lan['so_cau_dung']) ?>/<?= sonH($lan['tong_cau']) ?></td>
                                    <td><?= sonH($lan['diem']) ?>/10</td>
                                    <td><?= sonH($lan['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ACCORDION -->

        <section class="khoi">

            <h2>Thông tin mở rộng</h2>

            <div class="accordion">

                <button
                    class="nut-accordion"
                    id="nut-mo-rong"
                    type="button"
                    aria-expanded="false"
                    aria-controls="noi-dung-mo-rong">
                    Xem thêm mục tiêu học tập
                </button>

                <div
                    id="noi-dung-mo-rong"
                    class="noi-dung-accordion"
                    hidden>

                    <p>
                        Tôi muốn nâng cao kiến thức
                        HTML, CSS, JavaScript và PHP.
                    </p>

                    <p>
                        Tôi cũng mong muốn sử dụng tốt
                        Git và GitHub để làm việc nhóm.
                    </p>

                </div>

            </div>

        </section>

        <!-- THỜI KHÓA BIỂU -->

        <section class="khoi">

            <h2>Thời khóa biểu</h2>

            <div class="bang-cuon">

                <table>

                    <caption>
                        Thời khóa biểu trong tuần
                    </caption>

                    <thead>
                        <tr>
                            <th scope="col">Thứ</th>
                            <th scope="col">Môn học</th>
                            <th scope="col">Thời gian</th>
                            <th scope="col">Phòng học</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row">Thứ 2</th>
                            <td>Thiết kế và Lập trình Web</td>
                            <td>07:00 - 09:30</td>
                            <td>A101</td>
                        </tr>

                        <tr>
                            <th scope="row">Thứ 3</th>
                            <td>Trí tuệ nhân tạo</td>
                            <td>09:30 - 12:00</td>
                            <td>A203</td>
                        </tr>

                        <tr>
                            <th scope="row">Thứ 4</th>
                            <td>Cơ sở dữ liệu</td>
                            <td>13:00 - 15:30</td>
                            <td>B202</td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </section>

        <!-- LIÊN KẾT NHÓM -->

        <section class="khoi">

            <h2>Liên kết đến website nhóm</h2>

            <div class="nhom-lien-ket">

                <a class="nut" href="../../index.php">
                    Trang chủ
                </a>

                <a class="nut" href="../../danh-sach.php">
                    Danh sách sản phẩm
                </a>

                <a class="nut" href="../../gioi-thieu.php">
                    Giới thiệu nhóm
                </a>

                <a class="nut" href="../../lien-he.php">
                    Liên hệ
                </a>

                <a class="nut" href="../../gio-hang.php">
                    Giỏ hàng
                </a>

                <a class="nut" href="../../dang-nhap.php">
                    Đăng nhập
                </a>

            </div>

        </section>

    </div>
</main>

<?php
require __DIR__ . '/../../inc/footer.php';
?>
