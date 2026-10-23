<?php
/*
 * Tệp: gioithieu.php
 * Chức năng 1: Đếm lượt xem, mỗi session chỉ tính một lần.
 * Chức năng 2: Lọc danh sách kỹ năng bằng phương thức GET.
 * Chức năng 3: Sổ lưu bút có phân trang và xóa lời nhắn (chủ trang đăng nhập).
 */

session_start();

$goc = '../../';
$cssCaNhan = 'style.css';
$jsCaNhan = 'js/canhan.js';

/**
 * Chuyển dữ liệu thành chuỗi an toàn khi xuất HTML.
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ===== CHỨC NĂNG 1: ĐẾM LƯỢT XEM ===== */

$fileLuotXem = __DIR__ . '/storage/3120224062_views.txt';
$thuMucStorage = dirname($fileLuotXem);

$luotXem = 0;

if (!is_dir($thuMucStorage)) {
    mkdir($thuMucStorage, 0755, true);
}

if (is_file($fileLuotXem)) {
    $noiDung = trim((string) file_get_contents($fileLuotXem));

    if (ctype_digit($noiDung)) {
        $luotXem = (int) $noiDung;
    }
}

if (!isset($_SESSION['da_tinh_luot_xem'])) {
    $luotXem++;

    $ghiThanhCong = file_put_contents(
        $fileLuotXem,
        (string) $luotXem,
        LOCK_EX
    );

    if ($ghiThanhCong !== false) {
        $_SESSION['da_tinh_luot_xem'] = true;
    }
}

/* ===== CHỨC NĂNG 2: DANH SÁCH VÀ LỌC KỸ NĂNG BẰNG GET ===== */

$kyNang = [
    'HTML',
    'CSS',
    'PHP',
    'MySQL',
    'Git và GitHub',
    'Làm việc nhóm'
];

$kyNangChon = '';

if (isset($_GET['ky_nang']) && is_string($_GET['ky_nang'])) {
    $kyNangChon = trim($_GET['ky_nang']);
}

$boLocKhongHopLe = (
    $kyNangChon !== ''
    && !in_array($kyNangChon, $kyNang, true)
);

if ($boLocKhongHopLe) {
    $kyNangChon = '';
}

if ($kyNangChon !== '') {
    $kyNangHienThi = array_values(
        array_filter(
            $kyNang,
            static function ($item) use ($kyNangChon) {
                return $item === $kyNangChon;
            }
        )
    );
} else {
    $kyNangHienThi = $kyNang;
}

/* ===== CHỨC NĂNG 3: SỔ LƯU BÚT, PHÂN TRANG & XÓA KHI ĐĂNG NHẬP ===== */

$mssvHienTai = '3120224062';
$thongBaoLuuBut = '';
$loiLuuBut = '';
$pdo = null; // Khởi tạo an toàn tránh lỗi undefined

// Kết nối CSDL bằng PDO
$host = 'localhost';
$dbname = 'ltweb_nhom10';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    $loiLuuBut = "Lỗi kết nối CSDL: " . $e->getMessage();
}

$danhSachLuuBut = [];
$tongSoTrang = 1;
$trangHienTai = 1;

// Chỉ thực thi logic lưu bút khi kết nối CSDL thành công
if ($pdo) {
    // Kiểm tra quyền chủ trang
    $laChuTrang = isset($_SESSION['user']) && $_SESSION['user'] === $mssvHienTai;

    // Xử lý Xóa lời nhắn
    if (isset($_GET['action']) && $_GET['action'] === 'xoa_luu_but' && isset($_GET['id'])) {
        if ($laChuTrang) {
            $idXoa = (int) $_GET['id'];
            $stmtXoa = $pdo->prepare("DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?");
            $stmtXoa->execute([$idXoa, $mssvHienTai]);
            header("Location: gioithieu.php");
            exit;
        } else {
            $loiLuuBut = "Bạn cần đăng nhập với tư cách chủ trang mới có quyền xóa lời nhắn.";
        }
    }

    // Xử lý Gửi lời nhắn mới (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_luu_but'])) {
        $tenNguoiGui = trim($_POST['ten_nguoi_gui'] ?? '');
        $noiDungLuuBut = trim($_POST['noi_dung_luu_but'] ?? '');

        if ($tenNguoiGui !== '' && $noiDungLuuBut !== '') {
            $stmtThem = $pdo->prepare("INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung) VALUES (?, ?, ?)");
            $stmtThem->execute([$mssvHienTai, $tenNguoiGui, $noiDungLuuBut]);
            header("Location: gioithieu.php");
            exit;
        } else {
            $loiLuuBut = "Vui lòng điền đầy đủ họ tên và nội dung lời nhắn!";
        }
    }

    // Phân trang sổ lưu bút
    $soTinMoiTrang = 3; 
    $trangHienTai = isset($_GET['trang']) && ctype_digit($_GET['trang']) ? (int) $_GET['trang'] : 1;
    if ($trangHienTai < 1) $trangHienTai = 1;

    $offset = ($trangHienTai - 1) * $soTinMoiTrang;

    $stmtTong = $pdo->prepare("SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?");
    $stmtTong->execute([$mssvHienTai]);
    $tongSoTin = $stmtTong->fetchColumn();
    $tongSoTrang = ceil($tongSoTin / $soTinMoiTrang);
    if ($tongSoTrang < 1) $tongSoTrang = 1;
    if ($trangHienTai > $tongSoTrang) $trangHienTai = $tongSoTrang;

    $stmtLay = $pdo->prepare("SELECT * FROM tv_luu_but WHERE mssv = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmtLay->bindValue(1, $mssvHienTai, PDO::PARAM_STR);
    $stmtLay->bindValue(2, $soTinMoiTrang, PDO::PARAM_INT);
    $stmtLay->bindValue(3, $offset, PDO::PARAM_INT);
    $stmtLay->execute();
    $danhSachLuuBut = $stmtLay->fetchAll();
}

/* ===== THÔNG TIN TRANG ===== */

$tieuDe = 'Giới thiệu Phạm Huy Hoàng';

require __DIR__ . '/../../includes/header.php';
?>

<article>

    <h1>Giới thiệu Phạm Huy Hoàng</h1>

    <section aria-labelledby="gioi-thieu-ban-than">

        <h2 id="gioi-thieu-ban-than">
            Giới thiệu bản thân
        </h2>

        <img
            src="image.jpg"
            alt="Ảnh chân dung Phạm Huy Hoàng"
        >

        <p>
            <strong>Họ và tên:</strong>
            Phạm Huy Hoàng
        </p>

        <p>
            <strong>Lớp:</strong>
            24CNTT2
        </p>

        <p>
            <strong>Ngành:</strong>
            Công nghệ thông tin
        </p>

        <p>
            Tôi là sinh viên lớp 24CNTT2.
            Tôi đang học tập và tìm hiểu về lập trình web,
            cơ sở dữ liệu và các công nghệ phần mềm.
        </p>

        <h3>Kỹ năng</h3>

        <p>
            Bạn có thể chọn một kỹ năng để lọc danh sách.
        </p>

        <form method="get" action="">

            <label for="ky_nang">
                Lọc theo kỹ năng:
            </label>

            <select id="ky_nang" name="ky_nang">

                <option
                    value=""
                    <?= $kyNangChon === '' ? 'selected' : '' ?>
                >
                    Tất cả kỹ năng
                </option>

                <?php foreach ($kyNang as $kyNangItem): ?>

                    <option
                        value="<?= e($kyNangItem) ?>"
                        <?= $kyNangChon === $kyNangItem ? 'selected' : '' ?>
                    >
                        <?= e($kyNangItem) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit">
                Lọc kỹ năng
            </button>

            <a href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">
                Xem tất cả
            </a>

        </form>

        <?php if ($boLocKhongHopLe): ?>

            <p role="alert">
                Kỹ năng được chọn không hợp lệ.
                Vui lòng chọn lại.
            </p>

        <?php endif; ?>

        <?php if (!empty($kyNangHienThi)): ?>

            <p>
                <?php if ($kyNangChon !== ''): ?>
                    Kết quả lọc cho kỹ năng:
                    <strong><?= e($kyNangChon) ?></strong>
                <?php else: ?>
                    Danh sách tất cả kỹ năng:
                <?php endif; ?>
            </p>

            <ul>
                <?php foreach ($kyNangHienThi as $kyNangItem): ?>
                    <li><?= e($kyNangItem) ?></li>
                <?php endforeach; ?>
            </ul>

        <?php else: ?>

            <p>Không tìm thấy kỹ năng phù hợp.</p>

        <?php endif; ?>

    </section>

    <!-- ===== PHẦN SỔ LƯU BÚT ===== -->
    <section aria-labelledby="luu-but-title">
        <h2 id="luu-but-title">Sổ lưu bút</h2>

        <?php if ($loiLuuBut !== ''): ?>
            <p role="alert" style="color: red;"><?= e($loiLuuBut) ?></p>
        <?php endif; ?>

        <!-- Form gửi lời nhắn -->
        <form method="post" action="">
            <p>
                <label for="ten_nguoi_gui">Họ tên của bạn:</label><br>
                <input type="text" id="ten_nguoi_gui" name="ten_nguoi_gui" required style="width: 100%; padding: 8px;">
            </p>
            <p>
                <label for="noi_dung_luu_but">Lời nhắn:</label><br>
                <textarea id="noi_dung_luu_but" name="noi_dung_luu_but" rows="3" required style="width: 100%; padding: 8px;"></textarea>
            </p>
            <button type="submit" name="gui_luu_but">Gửi lời nhắn</button>
        </form>

        <hr style="margin: 20px 0;">

        <h3>Danh sách lời nhắn</h3>
        <?php if (empty($danhSachLuuBut)): ?>
            <p>Chưa có lời nhắn nào hoặc chưa kết nối được CSDL.</p>
        <?php else: ?>
            <?php foreach ($danhSachLuuBut as $item): ?>
                <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                    <p><strong><?= e($item['ten_nguoi_gui']) ?></strong> <small>(<?= e($item['created_at']) ?>)</small></p>
                    <p><?= nl2br(e($item['noi_dung'])) ?></p>

                    <!-- Nút xóa: Chỉ hiển thị khi đúng chủ trang đã đăng nhập -->
                    <?php if (isset($laChuTrang) && $laChuTrang): ?>
                        <p>
                            <a href="?action=xoa_luu_but&id=<?= $item['id'] ?>" 
                               onclick="return confirm('Bạn có chắc muốn xóa lời nhắn này không?');" 
                               style="color: red; font-size: 0.9em;">
                                [Xóa lời nhắn]
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Phân trang -->
            <?php if ($tongSoTrang > 1): ?>
                <nav aria-label="Phân trang lưu bút" style="margin-top: 15px;">
                    <p>Trang: 
                        <?php for ($i = 1; $i <= $tongSoTrang; $i++): ?>
                            <?php if ($i === $trangHienTai): ?>
                                <strong>[<?= $i ?>]</strong>
                            <?php else: ?>
                                <a href="?trang=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </p>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section aria-labelledby="luot-xem-title">

        <h2 id="luot-xem-title">
            Lượt xem trang
        </h2>

        <p>
            Trang đã được xem
            <strong><?= e($luotXem) ?></strong>
            lượt.
        </p>

        <p>
            Mỗi phiên truy cập chỉ được tính một lượt xem.
        </p>

    </section>

    <section aria-labelledby="countdown-title">

        <h2 id="countdown-title">
            Đếm ngược đến ngày thi cuối kỳ
        </h2>

        <p id="countdown" aria-live="polite">
            Đang tính thời gian...
        </p>

    </section>

    <section aria-labelledby="du-an-title">

        <h2 id="du-an-title">
            Dự án và sở thích
        </h2>

        <p>
            Trong quá trình học tập, tôi đang tham gia các dự án
            phát triển website cùng các thành viên trong nhóm.
        </p>

        <p>
            Tôi quan tâm đến việc xây dựng website bán hàng,
            thiết kế giao diện và kết nối website với cơ sở dữ liệu MySQL.
        </p>

        <p>
            Ngoài học tập, tôi thích nghe nhạc, tìm hiểu công nghệ mới,
            sử dụng máy tính và dành thời gian cho bạn bè.
        </p>

    </section>

    <section aria-labelledby="thong-tin-title">

        <h2 id="thong-tin-title">
            Thông tin cá nhân
        </h2>

        <details>
            <summary>Kỹ năng</summary>

            <p>
                HTML, CSS, PHP, MySQL, Git và GitHub,
                Làm việc nhóm.
            </p>
        </details>

        <details>
            <summary>Học vấn</summary>

            <p>
                Sinh viên lớp 24CNTT2,
                ngành Công nghệ thông tin.
            </p>
        </details>

        <details>
            <summary>Sở thích</summary>

            <p>
                Nghe nhạc, tìm hiểu công nghệ,
                sử dụng máy tính và giao lưu với bạn bè.
            </p>
        </details>

    </section>

    <section aria-labelledby="thoi-khoa-bieu-title">

        <h2 id="thoi-khoa-bieu-title">
            Thời khóa biểu tuần
        </h2>

        <div class="bang-cuon">

            <table>

                <caption>
                    Thời khóa biểu tuần của Phạm Huy Hoàng
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Thời gian</th>
                        <th scope="col">Thứ 2</th>
                        <th scope="col">Thứ 3</th>
                        <th scope="col">Thứ 4</th>
                        <th scope="col">Thứ 5</th>
                        <th scope="col">Thứ 6</th>
                        <th scope="col">Thứ 7</th>
                        <th scope="col">Chủ nhật</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <th scope="row">Sáng</th>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Nghỉ</td>
                    </tr>

                    <tr>
                        <th scope="row">Chiều</th>
                        <td>Tự học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Làm bài tập</td>
                        <td>Nghỉ</td>
                    </tr>

                    <tr>
                        <th scope="row">Tối</th>
                        <td>Tự học</td>
                        <td>Làm bài tập</td>
                        <td>Tự học</td>
                        <td>Làm bài nhóm</td>
                        <td>Nghỉ</td>
                        <td>Giải trí</td>
                        <td>Chuẩn bị tuần mới</td>
                    </tr>

                </tbody>

            </table>

        </div>

    </section>

    <section aria-labelledby="lien-ket-title">

        <h2 id="lien-ket-title">
            Liên kết
        </h2>

        <p>
            <a href="<?= e($goc) ?>gioi-thieu.php">
                Quay về trang giới thiệu nhóm
            </a>
        </p>

        <p>
            <a href="<?= e($goc) ?>index.php">
                Quay về trang chủ
            </a>
        </p>

    </section>

</article>

<?php
require __DIR__ . '/../../includes/footer.php';
?>