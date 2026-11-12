<?php
/*
 * Tệp: gioithieu.php (cá nhân Nguyễn Đình Minh Đạt - MSSV: 3120224028)
 * Chức năng: 
 *   - Hiển thị thông tin cá nhân, máy tính điểm học phần (POST - PRG), thời khóa biểu.
 *   - Chức năng CSDL 1: Bộ đếm lượt xem trang theo ngày (INSERT ... ON DUPLICATE KEY UPDATE) & Thống kê 7 ngày gần nhất.
 *   - Chức năng CSDL 2: Danh sách dự án cá nhân tra cứu từ CSDL (tv_du_an) có Tìm kiếm từ khóa (LIKE) & Lọc phân loại.
 * Áp dụng: Prepared Statement chống SQL Injection, e() chống XSS, PRG pattern tránh resubmit form khi F5, 0 Warning/Notice.
 * Cách thử: Mở trang cá nhân để tăng lượt xem. Sử dụng form tìm kiếm/lọc dự án bên dưới.
 */

declare(strict_types=1);

// Nạp config để có kết nối CSDL db() và các hàm chung
require_once __DIR__ . '/../../inc/config.php';

$mssv = '3120224028';
$hoTen = 'Nguyễn Đình Minh Đạt';

// Xử lý ghi nhận lượt xem bằng POST có CSRF
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['tang_luot_xem'])
) {
    $pdo = db();

    $sqlTangLuotXem = "
        INSERT INTO tv_luot_xem (mssv, ngay, so_luot)
        VALUES (?, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE so_luot = so_luot + 1
    ";

    $stmtTang = $pdo->prepare($sqlTangLuotXem);
    $stmtTang->execute([$mssv]);

    // Đánh dấu đã ghi nhận lượt xem để tránh gửi POST lặp lại
    $_SESSION['vua_tang_luot_xem'] = true;

    // Chuyển hướng về trang cá nhân
    $duongDan = 'gioithieu.php';

    if (!empty($_GET)) {
        $duongDan .= '?' . http_build_query($_GET);
    }

    header('Location: ' . $duongDan);
    exit;
}

// Xử lý Form Tính điểm học phần (Giữ nguyên PRG cũ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_diem'])) {
    $a1 = filter_var(is_string($_POST['a1'] ?? null) ? $_POST['a1'] : null, FILTER_VALIDATE_FLOAT);
$a2 = filter_var(is_string($_POST['a2'] ?? null) ? $_POST['a2'] : null, FILTER_VALIDATE_FLOAT);
$a3 = filter_var(is_string($_POST['a3'] ?? null) ? $_POST['a3'] : null, FILTER_VALIDATE_FLOAT);

if (
    $a1 !== false && $a2 !== false && $a3 !== false &&
    $a1 >= 0 && $a1 <= 10 &&
    $a2 >= 0 && $a2 <= 10 &&
    $a3 >= 0 && $a3 <= 10
) {
    $diemTongKet = 0.2 * $a1 + 0.3 * $a2 + 0.5 * $a3;
    $_SESSION['ketQuaDiem'] = round($diemTongKet, 2);
} else {
    $_SESSION['loiDiem'] = "Vui lòng nhập điểm hợp lệ từ 0 đến 10.";
}
    
    header("Location: gioithieu.php");
    exit;
}

// Kiểm soát việc gửi POST tăng lượt xem
$hienThiFormTangLuotXem = empty($_SESSION['vua_tang_luot_xem']);

if (!$hienThiFormTangLuotXem) {
    unset($_SESSION['vua_tang_luot_xem']);
}

$ketQuaDiem = isset($_SESSION['ketQuaDiem']) ? $_SESSION['ketQuaDiem'] : "";
$loiDiem = isset($_SESSION['loiDiem']) ? $_SESSION['loiDiem'] : "";
unset($_SESSION['ketQuaDiem'], $_SESSION['loiDiem']);

// Danh sách kỹ năng mảng PHP cũ
$danhSachKyNang = [
    ['ten' => 'HTML', 'loai' => 'frontend'],
    ['ten' => 'CSS', 'loai' => 'frontend'],
    ['ten' => 'PHP', 'loai' => 'backend'],
    ['ten' => 'MySQL', 'loai' => 'backend'],
    ['ten' => 'Git và GitHub', 'loai' => 'khac']
];

$loaiLoc = chuoi($_GET, 'loc', 20);
$kyNangHienThi = [];

$cacLoaiKyNang = ['tatca', 'frontend', 'backend', 'khac'];

if (!in_array($loaiLoc, $cacLoaiKyNang, true)) {
    $loaiLoc = 'tatca';
}

foreach ($danhSachKyNang as $kn) {
    if ($loaiLoc === 'tatca' || $kn['loai'] === $loaiLoc) {
        $kyNangHienThi[] = $kn;
    }
}

// ============================================================================
// CHỨC NĂNG CSDL 1: BỘ ĐẾM VÀ THỐNG KÊ LƯỢT XEM 7 NGÀY GẦN NHẤT
// ============================================================================
$thongKeLuotXem = [];
try {
    $pdo = db();
    // Lấy thống kê 7 ngày gần nhất
    $sqlLuotXem = "
        SELECT ngay, so_luot 
        FROM tv_luot_xem 
        WHERE mssv = ? AND ngay >= CURDATE() - INTERVAL 6 DAY 
        ORDER BY ngay ASC
    ";
    $stmtXem = $pdo->prepare($sqlLuotXem);
    $stmtXem->execute([$mssv]);
    $thongKeLuotXem = $stmtXem->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $ex) {
    // Không bắn lỗi ra màn hình để đảm bảo trang luôn chạy
}

// ============================================================================
// CHỨC NĂNG CSDL 2: TRUY VẤN DỰ ÁN CÁ NHÂN (TÌM KIẾM & LỌC TRONG CSDL)
// ============================================================================
$danhSachDuAnCSDL = [];
$timKiemDuAn = chuoi($_GET, 'q_duan', 100);
$loaiDuAnFilter = chuoi($_GET, 'loai_duan', 50);
try {
    $pdo = db();
    $sqlDuAn = "SELECT id, ten_du_an, loai_du_an, mo_ta, link_demo, created_at FROM tv_du_an WHERE mssv = ?";
    $paramsDuAn = [$mssv];

    if ($loaiDuAnFilter !== '') {
        // Dùng LIKE để lọc chính xác hơn nếu tên loại dự án có ký tự đặc biệt
        $sqlDuAn .= " AND loai_du_an LIKE ?";
        $paramsDuAn[] = '%' . $loaiDuAnFilter . '%';
    }

    if ($timKiemDuAn !== '') {
        $sqlDuAn .= " AND (ten_du_an LIKE ? OR mo_ta LIKE ?)";
        $paramLike = '%' . addcslashes($timKiemDuAn, '%_\\') . '%';
        $paramsDuAn[] = $paramLike;
        $paramsDuAn[] = $paramLike;
    }

    $sqlDuAn .= " ORDER BY created_at DESC";
    $stmtDuAn = $pdo->prepare($sqlDuAn);
    $stmtDuAn->execute($paramsDuAn);
    $danhSachDuAnCSDL = $stmtDuAn->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $ex) {
    // Bỏ qua nếu chưa import bảng
}

$goc = '../../';
require_once __DIR__ . '/../../inc/header.php';
?>

<!-- Bỏ dấu / ở cuối thẻ link -->
<link rel="stylesheet" href="style.css">

<nav class="navigation-back">
    <a href="<?php echo $goc; ?>index.php">
        &larr; Trang chủ
    </a>

    <a href="<?php echo $goc; ?>gioi-thieu.php">
        &larr; Trang Giới thiệu
    </a>
</nav>

<section>
  <h1>Giới thiệu bản thân</h1>
  <button class="profile-image-button" type="button" aria-label="Mở ảnh chân dung của Nguyễn Đình Minh Đạt">
    <!-- Bỏ dấu / ở cuối thẻ img -->
    <img class="profile-image" src="image.jpg" alt="Ảnh chân dung Nguyễn Đình Minh Đạt">
  </button>
  <p><strong>Họ và tên:</strong> Nguyễn Đình Minh Đạt</p>
  <p><strong>MSSV:</strong> 3120224028</p>
  <p><strong>Lớp:</strong> 24CNTT2</p>
  <p><strong>Khoa:</strong> Toán - Tin</p>
  <p><strong>Trường:</strong> Đại học Sư phạm - Đại học Đà Nẵng</p>
  <p>Tôi là sinh viên ngành Công nghệ thông tin. Tôi yêu thích lập trình và muốn nâng cao kỹ năng phát triển website.</p>
</section>

<!-- ============================================================================ -->
<!-- CSDL BỔ SUNG 1: THỐNG KÊ LƯỢT XEM TRANG 7 NGÀY GẦN NHẤT -->
<!-- ============================================================================ -->
<section>
    <h2>Thống kê lượt truy cập cá nhân (CSDL)</h2>
    <div class="table-container">
        <table>
            <caption>Thống kê số lượt xem trang trong 7 ngày gần nhất</caption>
            <thead>
                <tr>
                    <th scope="col">Ngày</th>
                    <th scope="col">Số lượt xem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($thongKeLuotXem)): ?>
                    <tr><td colspan="2">Chưa có dữ liệu thống kê.</td></tr>
                <?php else: ?>
                    <?php foreach ($thongKeLuotXem as $tk): ?>
                        <tr>
                            <td><?php echo e($tk['ngay']); ?></td>
                            <td><strong><?php echo (int)$tk['so_luot']; ?></strong> lượt</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<article>
  <h2>Dự án và sở thích</h2>
  <p>Trong môn Thiết kế và Lập trình Web, tôi cùng các thành viên trong nhóm thực hiện dự án website bán hàng quần áo.</p>
  
  <h3>Kỹ năng (Lọc từ mảng PHP)</h3>
  <div class="skill-filter">
    <strong>Lọc theo: </strong>
    <a href="?loc=tatca">Tất cả</a> |
    <a href="?loc=frontend">Front-end</a> |
    <a href="?loc=backend">Back-end</a> |
    <a href="?loc=khac">Khác</a>
</div>
  <ul class="skills" aria-label="Danh sách kỹ năng">
    <?php foreach ($kyNangHienThi as $kn): ?>
        <li><?php echo e($kn['ten']); ?></li>
    <?php endforeach; ?>
    <?php if (empty($kyNangHienThi)): ?>
        <li>Không có kỹ năng nào.</li>
    <?php endif; ?>
  </ul>
</article>

<!-- ============================================================================ -->
<!-- CSDL BỔ SUNG 2: DANH SÁCH DỰ ÁN CÁ NHÂN TỪ CSDL (TÌM KIẾM & LỌC) -->
<!-- ============================================================================ -->
<section>
    <h2>Quản lý dự án cá nhân (CSDL)</h2>
    <form method="GET" class="project-filter">
    <input
        type="text"
        name="q_duan"
        value="<?php echo e($timKiemDuAn); ?>"
        placeholder="Tìm tên dự án, từ khóa..."
    >

    <select name="loai_duan">
        <option value="">-- Tất cả loại dự án --</option>
        <option value="Web" <?php echo $loaiDuAnFilter === 'Web' ? 'selected' : ''; ?>>Web</option>
        <option value="App Mobile" <?php echo $loaiDuAnFilter === 'App Mobile' ? 'selected' : ''; ?>>App Mobile</option>
        <option value="AI / Trí tuệ nhân tạo" <?php echo $loaiDuAnFilter === 'AI / Trí tuệ nhân tạo' ? 'selected' : ''; ?>>AI / Trí tuệ nhân tạo</option>
    </select>

    <button type="submit">Tìm / Lọc</button>

    <a href="gioithieu.php" class="btn-reset">Làm mới</a>
</form>

    <?php if (empty($danhSachDuAnCSDL)): ?>
        <p>Không tìm thấy dự án nào trong CSDL.</p>
    <?php else: ?>
        <div class="project-list">
    <?php foreach ($danhSachDuAnCSDL as $da): ?>
        <div class="project-card">
            <h3>
                <?php echo e($da['ten_du_an']); ?>
                <small>(<?php echo e($da['loai_du_an']); ?>)</small>
            </h3>

            <p><?php echo nl2br(e($da['mo_ta'])); ?></p>
                    <?php
$linkDemo = filter_var($da['link_demo'] ?? '', FILTER_VALIDATE_URL);

if (
    $linkDemo &&
    in_array(parse_url($linkDemo, PHP_URL_SCHEME), ['http', 'https'], true)
):
?>
    <a href="<?php echo e($linkDemo); ?>" target="_blank" rel="noopener">
        Xem dự án
    </a>
<?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2>Máy tính điểm học phần</h2>
    <!-- Xóa thuộc tính action="" -->
    <form method="POST" class="form-luubut">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="a1">Điểm A1 (20%):</label>
            <input type="number" step="0.1" name="a1" id="a1" required min="0" max="10" placeholder="0 - 10">
        </div>
        <div class="form-group">
            <label for="a2">Điểm A2 (30%):</label>
            <input type="number" step="0.1" name="a2" id="a2" required min="0" max="10" placeholder="0 - 10">
        </div>
        <div class="form-group">
            <label for="a3">Điểm A3 (50%):</label>
            <input type="number" step="0.1" name="a3" id="a3" required min="0" max="10" placeholder="0 - 10">
        </div>
        <button type="submit" name="tinh_diem" class="btn-submit">Tính điểm</button>
    </form>

    <?php if ($loiDiem !== ""): ?>
        <p class="message-error">
    <strong>Lỗi:</strong> <?php echo e($loiDiem); ?>
</p>
    <?php endif; ?>
    <?php if ($ketQuaDiem !== ""): ?>
        <p class="message-success">
    <strong>Điểm tổng kết:</strong> <?php echo e($ketQuaDiem); ?>
</p>
    <?php endif; ?>
</section>

<section>
  <h2>Thời khóa biểu tuần</h2>
  <div class="table-container">
    <table>
      <caption>Thời khóa biểu tuần của Nguyễn Đình Minh Đạt</caption>
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


<?php if ($hienThiFormTangLuotXem): ?>
    <form id="form-tang-luot-xem" method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="tang_luot_xem" value="1">
    </form>
<?php endif; ?>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>