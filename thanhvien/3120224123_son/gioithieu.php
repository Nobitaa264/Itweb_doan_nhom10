<?php
declare(strict_types=1);

/**
 * Trang cá nhân Dương Quang Sơn - TH5 Phần B.
 * Chức năng 1: Đăng ký lịch hẹn, lưu JSONL.
 * Chức năng 2: Trắc nghiệm lập trình bằng PHP.
 * Kiểm thử: gửi đúng/sai, F5, tắt JavaScript.
 */

require_once __DIR__ . '/../../inc/config.php';

// Cấu hình trang cá nhân.
$goc = '../../';
$tieuDe = 'Giới thiệu cá nhân - Dương Quang Sơn';
$trang = '';

$tepLichHen = __DIR__
    . '/../../storage/3120224123_son_lichhen.jsonl';

$duongDanTrang = 'gioithieu.php';

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

            $thuMuc = dirname($tepLichHen);

            if (!is_dir($thuMuc)) {
                if (
                    !mkdir($thuMuc, 0775, true) &&
                    !is_dir($thuMuc)
                ) {
                    throw new RuntimeException(
                        'Không tạo được thư mục lưu dữ liệu.'
                    );
                }
            }

            $banGhi = [
                'ma' => bin2hex(random_bytes(8)),
                'hoTen' => $du['hoTen'],
                'ngay' => $du['ngay'],
                'gio' => $du['gio'],
                'noiDung' => $du['noiDung'],
                'taoLuc' => date('c')
            ];

            $json = json_encode(
                $banGhi,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            $ketQuaGhi = file_put_contents(
                $tepLichHen,
                $json . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );

            if ($ketQuaGhi === false) {
                throw new RuntimeException(
                    'Không lưu được lịch hẹn.'
                );
            }

            $_SESSION['son_thong_bao'] =
                'Đăng ký lịch hẹn thành công!';

            header(
                'Location: gioithieu.php#lich-hen',
                true,
                303
            );
            exit;
        }

    } elseif ($hanhDong === 'trac_nghiem') {

        // ==========================================
        // CHỨC NĂNG PHP 2 - CHẤM TRẮC NGHIỆM
        // ==========================================

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
        } else {
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

            <h3>Kỹ năng</h3>

            <ul class="danh-sach-ky-nang">
                <li>HTML5 cơ bản</li>
                <li>CSS3 cơ bản</li>
                <li>JavaScript</li>
                <li>PHP cơ bản</li>
                <li>Visual Studio Code</li>
                <li>Git và GitHub</li>
                <li>Làm việc nhóm</li>
            </ul>

        </article>

        <!-- CHỨC NĂNG PHP 1 -->

        <section class="khoi" id="lich-hen">

            <h2>Đăng ký lịch hẹn trao đổi học tập</h2>

            <p>
                Điền thông tin để đăng ký trao đổi
                về lập trình, học tập hoặc dự án.
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

        <!-- CHỨC NĂNG PHP 2 -->

        <section class="khoi" id="trac-nghiem">

            <h2>Trắc nghiệm kiến thức lập trình</h2>

            <p>
                Trả lời 5 câu hỏi HTML, CSS và PHP.
                Mỗi câu đúng được 2 điểm.
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
