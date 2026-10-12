<?php
/*
 * Tệp: gioithieu.php (cá nhân Đạt)
 * Chức năng: Hiển thị trang cá nhân, lọc kỹ năng (GET) và tính điểm học phần (POST).
 * Áp dụng: Hàm e() chống XSS, mô hình PRG tránh resubmit form khi F5, không Warning/Notice.
 * Cách thử: Chạy php -S localhost:8000 ở thư mục gốc. Mở link thành viên Đạt. Nhập điểm rồi F5 để test PRG.
 */

session_start();

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_diem'])) {
    $a1 = isset($_POST['a1']) ? (float)$_POST['a1'] : -1;
    $a2 = isset($_POST['a2']) ? (float)$_POST['a2'] : -1;
    $a3 = isset($_POST['a3']) ? (float)$_POST['a3'] : -1;

    if ($a1 >= 0 && $a1 <= 10 && $a2 >= 0 && $a2 <= 10 && $a3 >= 0 && $a3 <= 10) {
        $diemTongKet = 0.2 * $a1 + 0.3 * $a2 + 0.5 * $a3;
        $_SESSION['ketQuaDiem'] = round($diemTongKet, 2);
    } else {
        $_SESSION['loiDiem'] = "Vui lòng nhập điểm hợp lệ từ 0 đến 10.";
    }
    
    header("Location: gioithieu.php");
    exit;
}

$ketQuaDiem = isset($_SESSION['ketQuaDiem']) ? $_SESSION['ketQuaDiem'] : "";
$loiDiem = isset($_SESSION['loiDiem']) ? $_SESSION['loiDiem'] : "";
unset($_SESSION['ketQuaDiem'], $_SESSION['loiDiem']);

$danhSachKyNang = [
    ['ten' => 'HTML', 'loai' => 'frontend'],
    ['ten' => 'CSS', 'loai' => 'frontend'],
    ['ten' => 'PHP', 'loai' => 'backend'],
    ['ten' => 'MySQL', 'loai' => 'backend'],
    ['ten' => 'Git và GitHub', 'loai' => 'khac']
];

$loaiLoc = isset($_GET['loc']) ? (string)$_GET['loc'] : 'tatca';
$kyNangHienThi = [];

foreach ($danhSachKyNang as $kn) {
    if ($loaiLoc === 'tatca' || $kn['loai'] === $loaiLoc) {
        $kyNangHienThi[] = $kn;
    }
}

$goc = '../../';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../inc/header.php';
?>

<!-- Bỏ dấu / ở cuối thẻ link -->
<link rel="stylesheet" href="style.css">

<nav class="navigation-back" style="margin: 20px 0; display: flex; gap: 12px; align-items: center;">
    <a href="<?php echo $goc; ?>index.php" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 14px; transition: background-color 0.2s ease;">
        &larr; Trang chủ
    </a>
    <a href="<?php echo $goc; ?>gioi-thieu.php" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background-color: #6c757d; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 14px; transition: background-color 0.2s ease;">
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
  <p><strong>Lớp:</strong> 24CNTT2</p>
  <p><strong>Khoa:</strong> Toán - Tin</p>
  <p><strong>Trường:</strong> Đại học Sư phạm - Đại học Đà Nẵng</p>
  <p>Tôi là sinh viên ngành Công nghệ thông tin. Tôi yêu thích lập trình và muốn nâng cao kỹ năng phát triển website.</p>
</section>

<article>
  <h2>Dự án và sở thích</h2>
  <p>Trong môn Thiết kế và Lập trình Web, tôi cùng các thành viên trong nhóm thực hiện dự án website bán hàng quần áo.</p>
  
  <h3>Kỹ năng</h3>
  <div style="margin-bottom: 15px;">
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

<section>
    <h2>Máy tính điểm học phần</h2>
    <!-- Xóa thuộc tính action="" -->
    <form method="POST" class="form-luubut">
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
        <p style="color: red; margin-top: 15px;"><strong>Lỗi:</strong> <?php echo e($loiDiem); ?></p>
    <?php endif; ?>
    <?php if ($ketQuaDiem !== ""): ?>
        <p style="color: green; margin-top: 15px; font-size: 1.2em;"><strong>Điểm tổng kết:</strong> <?php echo e($ketQuaDiem); ?></p>
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

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>