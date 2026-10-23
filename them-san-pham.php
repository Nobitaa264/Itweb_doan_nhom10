<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
// Yêu cầu quyền admin để chỉnh sửa sản phẩm
$user = yeuCauQuyen('admin');
use App\Config\Database;

$tieuDe = 'Thêm sản phẩm - StyleHub';
$trang = 'them-san-pham';
$goc = './';

$loi = [];
$duLieu = [
    'id' => '',
    'ten' => '',
    'danh_muc_id' => '',
    'gia' => '',
    'hinh_anh' => '',
    'mo_ta' => '',
    'chat_lieu' => '',
    'mau_sac' => '',
    'kich_co' => '',
    'tinh_trang' => 'Còn hàng',
];

$pdo = Database::connect();

$stmtDanhMuc = $pdo->query(
    'SELECT id, ten FROM danh_muc ORDER BY ten'
);
$danhMuc = $stmtDanhMuc->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($duLieu as $truong => $macDinh) {
        if (isset($_POST[$truong]) && is_string($_POST[$truong])) {
            $duLieu[$truong] = trim($_POST[$truong]);
        }
    }

    $token = (string) ($_POST['_token'] ?? '');
    if (
        empty($_SESSION['_token']) ||
        !is_string($_SESSION['_token']) ||
        !hash_equals($_SESSION['_token'], $token)
    ) {
        $loi[] = 'Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
    }

    if (!preg_match('/^[A-Z]{2}[0-9]{3}$/', $duLieu['id'])) {
        $loi[] = 'Mã sản phẩm phải có dạng SH001 hoặc JE001.';
    }

    if ($duLieu['ten'] === '' || mb_strlen($duLieu['ten']) > 255) {
        $loi[] = 'Tên sản phẩm không được để trống và tối đa 255 ký tự.';
    }

    $danhMucId = filter_var(
        $duLieu['danh_muc_id'],
        FILTER_VALIDATE_INT
    );

    if ($danhMucId === false || $danhMucId < 1) {
        $loi[] = 'Vui lòng chọn danh mục hợp lệ.';
    } else {
        $stmtKiemTraDanhMuc = $pdo->prepare(
            'SELECT COUNT(*) FROM danh_muc WHERE id = :id'
        );
        $stmtKiemTraDanhMuc->execute(['id' => $danhMucId]);

        if ((int) $stmtKiemTraDanhMuc->fetchColumn() === 0) {
            $loi[] = 'Danh mục được chọn không tồn tại.';
        }
    }

    $gia = filter_var($duLieu['gia'], FILTER_VALIDATE_INT);
    if ($gia === false || $gia < 0) {
        $loi[] = 'Giá sản phẩm phải là số nguyên không âm.';
    }

    if (
        $duLieu['hinh_anh'] === '' ||
        mb_strlen($duLieu['hinh_anh']) > 500
    ) {
        $loi[] = 'Đường dẫn hình ảnh không được để trống và tối đa 500 ký tự.';
    }

    if ($duLieu['mo_ta'] === '') {
        $loi[] = 'Vui lòng nhập mô tả sản phẩm.';
    }

    foreach (['chat_lieu', 'mau_sac', 'kich_co'] as $truongBatBuoc) {
        if ($duLieu[$truongBatBuoc] === '') {
            $loi[] = 'Vui lòng nhập đầy đủ chất liệu, màu sắc và kích cỡ.';
            break;
        }
    }

    if (
        !in_array(
            $duLieu['tinh_trang'],
            ['Còn hàng', 'Hết hàng'],
            true
        )
    ) {
        $loi[] = 'Tình trạng sản phẩm không hợp lệ.';
    }

    if ($loi === []) {
        try {
            $sql = '
                INSERT INTO san_pham (
                    id, ten, danh_muc_id, gia, hinh_anh,
                    mo_ta, chat_lieu, mau_sac, kich_co, tinh_trang
                ) VALUES (
                    :id, :ten, :danh_muc_id, :gia, :hinh_anh,
                    :mo_ta, :chat_lieu, :mau_sac, :kich_co, :tinh_trang
                )
            ';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $duLieu['id'],
                'ten' => $duLieu['ten'],
                'danh_muc_id' => $danhMucId,
                'gia' => $gia,
                'hinh_anh' => $duLieu['hinh_anh'],
                'mo_ta' => $duLieu['mo_ta'],
                'chat_lieu' => $duLieu['chat_lieu'],
                'mau_sac' => $duLieu['mau_sac'],
                'kich_co' => $duLieu['kich_co'],
                'tinh_trang' => $duLieu['tinh_trang'],
            ]);

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đã thêm sản phẩm thành công.',
            ];

            redirect('quan-tri.php');
        } catch (Throwable $e) {
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                $loi[] = 'Mã sản phẩm đã tồn tại hoặc dữ liệu vi phạm ràng buộc.';
            } else {
                error_log('[THEM_SAN_PHAM] ' . $e->getMessage());
                $loi[] = 'Không thể thêm sản phẩm. Vui lòng kiểm tra nhật ký lỗi.';
            }
        }
    }
}

if (empty($_SESSION['_token'])) {
    $_SESSION['_token'] = bin2hex(random_bytes(32));
}
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <h1 class="tieu-de-trang">Thêm sản phẩm</h1>

        <p><a href="quan-tri.php">← Quay lại trang quản trị</a></p>

        <?php if ($loi !== []): ?>
            <div role="alert">
                <ul>
                    <?php foreach ($loi as $thongBao): ?>
                        <li><?= e($thongBao) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="khu-vuc-form">
            <form method="post" action="them-san-pham.php">
                <input type="hidden" name="_token" value="<?= e($_SESSION['_token']) ?>">

                <div class="nhom-form">
                    <label for="id">Mã sản phẩm</label>
                    <input id="id" name="id" maxlength="20"
                           placeholder="VD: SH013"
                           value="<?= e($duLieu['id']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="ten">Tên sản phẩm</label>
                    <input id="ten" name="ten" maxlength="255"
                           value="<?= e($duLieu['ten']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="danh_muc_id">Danh mục</label>
                    <select id="danh_muc_id" name="danh_muc_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($danhMuc as $muc): ?>
                            <option value="<?= e($muc['id']) ?>"
                                <?= $duLieu['danh_muc_id'] === (string) $muc['id'] ? 'selected' : '' ?>>
                                <?= e($muc['ten']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="nhom-form">
                    <label for="gia">Giá (VNĐ)</label>
                    <input type="number" id="gia" name="gia" min="0"
                           value="<?= e($duLieu['gia']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="hinh_anh">Đường dẫn hình ảnh</label>
                    <input id="hinh_anh" name="hinh_anh" maxlength="500"
                           placeholder="images/ten-anh.jpg"
                           value="<?= e($duLieu['hinh_anh']) ?>" required>
                </div>

                <div class="nhom-form">
                    
                    <label for="mo_ta">Mô tả sản phẩm</label>
                    <textarea id="mo_ta" name="mo_ta" rows="4" required><?= e($duLieu['mo_ta']) ?></textarea>
                </div>

                <div class="nhom-form">
                    <label for="chat_lieu">Chất liệu</label>
                    <input id="chat_lieu" name="chat_lieu" maxlength="100"
                           value="<?= e($duLieu['chat_lieu']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="mau_sac">Màu sắc</label>
                    <input id="mau_sac" name="mau_sac" maxlength="255"
                           value="<?= e($duLieu['mau_sac']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="kich_co">Kích cỡ</label>
                    <input id="kich_co" name="kich_co" maxlength="255"
                           placeholder="Ví dụ: S, M, L, XL"
                           value="<?= e($duLieu['kich_co']) ?>" required>
                </div>

                <div class="nhom-form">
                    <label for="tinh_trang">Tình trạng</label>
                    <select id="tinh_trang" name="tinh_trang" required>
                        <option value="Còn hàng"
                            <?= $duLieu['tinh_trang'] === 'Còn hàng' ? 'selected' : '' ?>>
                            Còn hàng
                        </option>
                        <option value="Hết hàng"
                            <?= $duLieu['tinh_trang'] === 'Hết hàng' ? 'selected' : '' ?>>
                            Hết hàng
                        </option>
                    </select>
                </div>

                <div class="nhom-nut">
                    <button type="submit" class="nut nut-chinh">
                        Thêm sản phẩm
                    </button>
                    <a href="quan-tri.php" class="nut nut-phu">Hủy</a>
                </div>
            </form>
        </section>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
