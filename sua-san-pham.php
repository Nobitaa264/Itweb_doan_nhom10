<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
// Yêu cầu quyền admin để chỉnh sửa sản phẩm
$user = yeuCauQuyen('admin');
use App\Config\Database;
use App\Data\KhoSanPham;

$tieuDe = 'Sửa sản phẩm - StyleHub';
$trang = 'sua-san-pham';
$goc = './';

$loi = '';

$id = trim((string) ($_GET['id'] ?? $_POST['id'] ?? ''));

if (!preg_match('/^[A-Z]{2}[0-9]{3}$/', $id)) {
    http_response_code(400);
    exit('Mã sản phẩm không hợp lệ.');
}

$khoSanPham = new KhoSanPham();
$sanPham = $khoSanPham->timTheoId($id);

if ($sanPham === null) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm.');
}

$pdo = Database::connect();

$stmt = $pdo->query('SELECT id, ten FROM danh_muc ORDER BY ten');
$danhMuc = $stmt->fetchAll();

$ten = $sanPham->ten;
$gia = (string) $sanPham->gia;
$danhMucHienTai = $sanPham->danhMuc;
$hinhAnh = $sanPham->hinhAnh;
$moTa = $sanPham->moTa;
$chatLieu = $sanPham->chatLieu;
$mauSac = $sanPham->mauSac;
$kichCo = $sanPham->kichCo;
$tinhTrang = $sanPham->tinhTrang;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_token'] ?? '');

    if (!hash_equals((string) $_SESSION['csrf_token'], $token)) {
        $loi = 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.';
    } else {
        $ten = trim((string) ($_POST['ten'] ?? ''));
        $giaNhap = trim((string) ($_POST['gia'] ?? ''));
        $danhMucHienTai = trim((string) ($_POST['danh_muc_id'] ?? ''));
        $hinhAnh = trim((string) ($_POST['hinh_anh'] ?? ''));
        $moTa = trim((string) ($_POST['mo_ta'] ?? ''));
        $chatLieu = trim((string) ($_POST['chat_lieu'] ?? ''));
        $mauSac = trim((string) ($_POST['mau_sac'] ?? ''));
        $kichCo = trim((string) ($_POST['kich_co'] ?? ''));
        $tinhTrang = trim((string) ($_POST['tinh_trang'] ?? ''));

        $gia = $giaNhap;

        $giaHopLe = $giaNhap !== ''
            && ctype_digit($giaNhap)
            && filter_var(
                $giaNhap,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 0]]
            ) !== false;

        $danhMucHopLe = false;

        foreach ($danhMuc as $muc) {
            if ((string) $muc['id'] === $danhMucHienTai) {
                $danhMucHopLe = true;
                break;
            }
        }

        $trangThaiHopLe = in_array(
            $tinhTrang,
            ['Còn hàng', 'Hết hàng'],
            true
        );

        if ($ten === '' || mb_strlen($ten) > 255) {
            $loi = 'Tên sản phẩm không được để trống và tối đa 255 ký tự.';
        } elseif (!$giaHopLe) {
            $loi = 'Giá phải là số nguyên không âm.';
        } elseif (!$danhMucHopLe) {
            $loi = 'Vui lòng chọn danh mục hợp lệ.';
        } elseif ($hinhAnh === '' || mb_strlen($hinhAnh) > 500) {
            $loi = 'Đường dẫn ảnh không được để trống và tối đa 500 ký tự.';
        } elseif ($moTa === '' || $chatLieu === '' || $mauSac === '' || $kichCo === '') {
            $loi = 'Vui lòng nhập đầy đủ mô tả, chất liệu, màu sắc và kích cỡ.';
        } elseif (!$trangThaiHopLe) {
            $loi = 'Tình trạng sản phẩm không hợp lệ.';
        } else {
            try {
                $sql = '
                    UPDATE san_pham
                    SET
                        ten = :ten,
                        gia = :gia,
                        danh_muc_id = :danh_muc_id,
                        hinh_anh = :hinh_anh,
                        mo_ta = :mo_ta,
                        chat_lieu = :chat_lieu,
                        mau_sac = :mau_sac,
                        kich_co = :kich_co,
                        tinh_trang = :tinh_trang
                    WHERE id = :id
                ';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'ten' => $ten,
                    'gia' => (int) $giaNhap,
                    'danh_muc_id' => (int) $danhMucHienTai,
                    'hinh_anh' => $hinhAnh,
                    'mo_ta' => $moTa,
                    'chat_lieu' => $chatLieu,
                    'mau_sac' => $mauSac,
                    'kich_co' => $kichCo,
                    'tinh_trang' => $tinhTrang,
                    'id' => $id,
                ]);

                $_SESSION['flash'] = [
                    'loai' => 'thanh-cong',
                    'noiDung' => 'Cập nhật sản phẩm thành công.',
                ];

                header('Location: quan-tri.php');
                exit;
            } catch (\PDOException $e) {
                error_log('[SUA_SAN_PHAM] ' . $e->getMessage());
                $loi = 'Không thể cập nhật sản phẩm. Vui lòng thử lại.';
            }
        }
    }
}
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <section class="khoi-noi-dung">
            <h1>Sửa sản phẩm</h1>
            <p>Mã sản phẩm: <strong><?= e($id) ?></strong></p>

            <?php if ($loi !== ''): ?>
                <p role="alert"><?= e($loi) ?></p>
            <?php endif; ?>

            <form method="post" action="sua-san-pham.php?id=<?= rawurlencode($id) ?>">
                <input type="hidden" name="id" value="<?= e($id) ?>">
                <input
                    type="hidden"
                    name="_token"
                    value="<?= e((string) $_SESSION['csrf_token']) ?>"
                >

                <p>
                    <label for="ten">Tên sản phẩm</label><br>
                    <input id="ten" name="ten" required maxlength="255"
                        value="<?= e($ten) ?>">
                </p>

                <p>
                    <label for="gia">Giá (VNĐ)</label><br>
                    <input id="gia" name="gia" type="number" min="0" step="1"
                        required value="<?= e($gia) ?>">
                </p>

                <p>
                    <label for="danh_muc_id">Danh mục</label><br>
                    <select id="danh_muc_id" name="danh_muc_id" required>
    <option value="">-- Chọn danh mục --</option>
    <?php foreach ($danhMuc as $muc): ?>
        <option
            value="<?= e((string) $muc['id']) ?>"
            <?= $danhMucHienTai === $muc['ten'] || $danhMucHienTai === (string) $muc['id'] ? 'selected' : '' ?>
        >
            <?= e($muc['ten']) ?>
        </option>
    <?php endforeach; ?>
</select>
                </p>

                <p>
                    <label for="hinh_anh">Đường dẫn ảnh</label><br>
                    <input id="hinh_anh" name="hinh_anh" required maxlength="500"
                        value="<?= e($hinhAnh) ?>">
                </p>

                <p>
                    <label for="mo_ta">Mô tả</label><br>
                    <textarea id="mo_ta" name="mo_ta" required><?= e($moTa) ?></textarea>
                </p>

                <p>
                    <label for="chat_lieu">Chất liệu</label><br>
                    <input id="chat_lieu" name="chat_lieu" required
                        value="<?= e($chatLieu) ?>">
                </p>

                <p>
                    <label for="mau_sac">Màu sắc</label><br>
                    <input id="mau_sac" name="mau_sac" required
                        value="<?= e($mauSac) ?>">
                </p>

                <p>
                    <label for="kich_co">Kích cỡ</label><br>
                    <input id="kich_co" name="kich_co" required
                        value="<?= e($kichCo) ?>">
                </p>

                <p>
                    <label for="tinh_trang">Tình trạng</label><br>
                    <select id="tinh_trang" name="tinh_trang">
    <option value="Còn hàng" <?= $tinhTrang === 'Còn hàng' ? 'selected' : '' ?>>Còn hàng</option>
    <option value="Hết hàng" <?= $tinhTrang === 'Hết hàng' ? 'selected' : '' ?>>Hết hàng</option>
</select>
                </p>

                <button type="submit" class="nut nut-chinh">Lưu thay đổi</button>
                <a href="quan-tri.php" class="nut nut-phu">Hủy</a>
            </form>
        </section>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
