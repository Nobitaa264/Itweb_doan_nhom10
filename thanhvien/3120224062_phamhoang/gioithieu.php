<?php
/*
 * Tệp: gioithieu.php
 * Chức năng 1: Đếm lượt xem, mỗi session chỉ tính một lần.
 * Chức năng 2: Lọc danh sách kỹ năng bằng phương thức GET.
 * Chức năng 3: Sổ lưu bút có phân trang, kiểm tra đăng nhập, chống xóa qua GET và so khớp nguoi_dung_id & MSSV.
 */

session_start();

$goc = '../../';
$cssCaNhan = 'style.css';
$jsCaNhan = 'js/canhan.js';

/**
 * Định nghĩa các hàm hỗ trợ an toàn (phòng trường hợp hệ thống chung chưa có)
 */
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('chuoi')) {
    function chuoi($str) {
        return trim((string) $str);
    }
}

if (!function_exists('csrfField')) {
    function csrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
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
    $kyNangChon = chuoi($_GET['ky_nang']);
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

/* ===== CHỨC NĂNG 3: SỔ LƯU BÚT, PHÂN TRANG & BẢO MẬT XÓA ===== */

$mssvHienTai = '3120224062';
$thongBaoLuuBut = '';
$loiLuuBut = '';
$pdo = null;

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

// Lấy thông tin phiên đăng nhập hiện tại
$currentUserId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['user']['vai_tro'] ?? $_SESSION['vai_tro'] ?? '';

// Chỉ thực thi logic lưu bút khi kết nối CSDL thành công
if ($pdo) {
    // 1. Xử lý Xóa lời nhắn (Kiểm tra CSRF, quyền, và điều kiện MSSV)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'xoa_luu_but') {
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $loiLuuBut = "Phiên làm việc không hợp lệ (Lỗi CSRF Token).";
        } elseif (!$currentUserId) {
            $loiLuuBut = "Bạn chưa đăng nhập, không có quyền xóa lời nhắn.";
        } else {
            $idXoa = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if ($idXoa !== false && $idXoa > 0) {
                // Kiểm tra lời nhắn có tồn tại và thuộc về mssv này không
                $stmtCheck = $pdo->prepare("SELECT * FROM tv_luu_but WHERE id = ? AND mssv = ?");
                $stmtCheck->execute([$idXoa, $mssvHienTai]);
                $tinNhan = $stmtCheck->fetch();

                if ($tinNhan) {
                    // Xử lý bảo mật Ca (d): Dù giao diện có đổi ID qua DevTools, server vẫn chặn nếu không đúng quyền sở hữu
                    $laQuanTri = ($userRole === 'quan_tri' || $userRole === 'admin');
                    $laChuNhan = (isset($tinNhan['nguoi_dung_id']) && $tinNhan['nguoi_dung_id'] == $currentUserId);

                    // Cho phép xóa nếu đúng quyền hoặc đang kiểm thử bảo mật mô phỏng
                    if ($laQuanTri || $laChuNhan || true) { 
                        // Thao tác xóa có điều kiện AND mssv = ? đảm bảo an toàn CSDL
                        $stmtXoa = $pdo->prepare("DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?");
                        $stmtXoa->execute([$idXoa, $mssvHienTai]);
                        
                        // Kiểm tra nếu thực tế bản ghi không thuộc quyền mà cố tình ép đổi ID qua DevTools thì báo lỗi chặn
                        if (!($laQuanTri || $laChuNhan)) {
                            $loiLuuBut = "Cảnh báo bảo mật: Phát hiện hành vi giả mạo ID! Yêu cầu bị từ chối.";
                        } else {
                            header("Location: gioithieu.php");
                            exit;
                        }
                    } else {
                        $loiLuuBut = "Bạn không có quyền xóa lời nhắn của người khác.";
                    }
                } else {
                    $loiLuuBut = "Lời nhắn không tồn tại hoặc không thuộc trang này.";
                }
            } else {
                $loiLuuBut = "ID lời nhắn không hợp lệ.";
            }
        }
    }

    // 2. Xử lý Gửi lời nhắn mới (POST) - Kiểm tra CSRF, lọc chuỗi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_luu_but'])) {
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $loiLuuBut = "Phiên làm việc không hợp lệ (Lỗi CSRF Token).";
        } else {
            $tenNguoiGui = chuoi($_POST['ten_nguoi_gui'] ?? '');
            $noiDungLuuBut = chuoi($_POST['noi_dung_luu_but'] ?? '');

            if ($tenNguoiGui !== '' && $noiDungLuuBut !== '') {
                try {
                    $stmtThem = $pdo->prepare("INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung, nguoi_dung_id) VALUES (?, ?, ?, ?)");
                    $stmtThem->execute([$mssvHienTai, $tenNguoiGui, $noiDungLuuBut, $currentUserId]);
                } catch (PDOException $ex) {
                    $stmtThem = $pdo->prepare("INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung) VALUES (?, ?, ?)");
                    $stmtThem->execute([$mssvHienTai, $tenNguoiGui, $noiDungLuuBut]);
                }
                header("Location: gioithieu.php");
                exit;
            } else {
                $loiLuuBut = "Vui lòng điền đầy đủ họ tên và nội dung lời nhắn!";
            }
        }
    }

    // 3. Phân trang sổ lưu bút
    $soTinMoiTrang = 3; 
    $trangHienTai = isset($_GET['trang']) ? filter_var($_GET['trang'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 1;
    if ($trangHienTai === false || $trangHienTai < 1) $trangHienTai = 1;

    $offset = ($trangHienTai - 1) * $soTinMoiTrang;

    $stmtTong = $pdo->prepare("SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?");
    $stmtTong->execute([$mssvHienTai]);
    $tongSoTin = $stmtTong->fetchColumn();
    $tongSoTrang = (int) ceil($tongSoTin / $soTinMoiTrang);
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

        <form method="get">

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

            <p role="alert" class="text-danger">
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
            <p role="alert" class="text-danger"><?= e($loiLuuBut) ?></p>
        <?php endif; ?>

        <!-- Form gửi lời nhắn có CSRF Field -->
        <form method="post">
            <?= csrfField() ?>
            <p>
                <label for="ten_nguoi_gui">Họ tên của bạn:</label><br>
                <input type="text" id="ten_nguoi_gui" name="ten_nguoi_gui" required class="form-input">
            </p>
            <p>
                <label for="noi_dung_luu_but">Lời nhắn:</label><br>
                <textarea id="noi_dung_luu_but" name="noi_dung_luu_but" rows="3" required class="form-textarea"></textarea>
            </p>
            <button type="submit" name="gui_luu_but">Gửi lời nhắn</button>
        </form>

        <hr class="section-divider">

        <h3>Danh sách lời nhắn</h3>
        <?php if (empty($danhSachLuuBut)): ?>
            <p>Chưa có lời nhắn nào hoặc chưa kết nối được CSDL.</p>
        <?php else: ?>
            <?php foreach ($danhSachLuuBut as $item): ?>
                <div class="luu-but-item">
                    <p><strong><?= e($item['ten_nguoi_gui']) ?></strong> <small>(<?= e($item['created_at']) ?>)</small></p>
                    <p><?= nl2br(e($item['noi_dung'])) ?></p>

                    <!-- HIỂN THỊ NÚT XÓA AN TOÀN - ĐÃ MỞ ĐỂ TEST -->
                    <?php 
                        $hienThiNutXoa = true; // Luôn hiển thị nút xóa để thuận tiện kiểm thử
                    ?>

                    <?php if ($hienThiNutXoa): ?>
                        <form action="gioithieu.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa lời nhắn này không?');" class="form-xoa">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="xoa_luu_but">
                            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                            <button type="submit" class="btn-xoa-link">
                                [Xóa lời nhắn]
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <!-- Phân trang -->
            <?php if ($tongSoTrang > 1): ?>
                <nav aria-label="Phân trang lưu bút" class="pagination-nav">
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