<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Config\Database;
use App\Data\KhoSanPham;
use App\Services\GioHang;

$tieuDe = 'Đặt hàng - StyleHub';
$trang = 'dat-hang';
$goc = './';

// Chỉ khách hàng đã đăng nhập mới được đặt hàng.
$nguoiDung = $_SESSION['nguoi_dung'] ?? null;

if (!is_array($nguoiDung) || empty($nguoiDung['id'])) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Vui lòng đăng nhập trước khi đặt hàng.',
    ];

    redirect('dang-nhap.php');
}

$khoSanPham = new KhoSanPham();
$gioHang = new GioHang();
$cacMuc = $gioHang->cacMuc($khoSanPham);

if ($cacMuc === []) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Giỏ hàng đang trống. Vui lòng chọn sản phẩm trước.',
    ];

    redirect('gio-hang.php');
}

$loi = [];
$duLieu = [
    'ho_ten_nhan' => trim((string) ($nguoiDung['ten'] ?? '')),
    'so_dien_thoai_nhan' => '',
    'dia_chi_nhan' => '',
    'ghi_chu' => '',
];

// Tạo token chống gửi biểu mẫu giả mạo.
if (empty($_SESSION['csrf_dat_hang'])) {
    $_SESSION['csrf_dat_hang'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($duLieu as $truong => $giaTriMacDinh) {
        $duLieu[$truong] = trim(
            (string) ($_POST[$truong] ?? $giaTriMacDinh)
        );
    }

    $csrf = (string) ($_POST['csrf_token'] ?? '');

    if (
        !hash_equals(
            (string) $_SESSION['csrf_dat_hang'],
            $csrf
        )
    ) {
        $loi[] = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
    }

    if (
        $duLieu['ho_ten_nhan'] === '' ||
        mb_strlen($duLieu['ho_ten_nhan']) > 100
    ) {
        $loi[] = 'Họ tên người nhận không được để trống và tối đa 100 ký tự.';
    }

    if (
        !preg_match('/^[0-9+\s().-]{9,20}$/', $duLieu['so_dien_thoai_nhan'])
    ) {
        $loi[] = 'Số điện thoại không hợp lệ.';
    }

    if (
        $duLieu['dia_chi_nhan'] === '' ||
        mb_strlen($duLieu['dia_chi_nhan']) > 255
    ) {
        $loi[] = 'Địa chỉ nhận hàng không được để trống và tối đa 255 ký tự.';
    }

    if (mb_strlen($duLieu['ghi_chu']) > 2000) {
        $loi[] = 'Ghi chú không được vượt quá 2000 ký tự.';
    }

    // Lấy lại sản phẩm và giá từ dữ liệu phía máy chủ.
    $cacMuc = $gioHang->cacMuc($khoSanPham);
    $soMucTrongSession = count($_SESSION['gio'] ?? []);

    if (
        $cacMuc === [] ||
        count($cacMuc) !== $soMucTrongSession
    ) {
        $loi[] = 'Giỏ hàng có sản phẩm không còn hợp lệ. Vui lòng kiểm tra lại.';
    }

    $tongTien = 0;

    foreach ($cacMuc as $muc) {
        $gia = (int) $muc['sanPham']->gia;
        $soLuong = (int) $muc['soLuong'];

        if ($gia < 0 || $soLuong < 1 || $soLuong > 99) {
            $loi[] = 'Thông tin sản phẩm trong giỏ hàng không hợp lệ.';
            break;
        }

        $tongTien += $gia * $soLuong;
    }

    if ($tongTien <= 0) {
        $loi[] = 'Tổng tiền đơn hàng không hợp lệ.';
    }

    if ($loi === []) {
        $pdo = null;

        try {
            $pdo = Database::connect();
            $pdo->beginTransaction();

            // Xác nhận tài khoản vẫn tồn tại.
            $stmtNguoiDung = $pdo->prepare(
                'SELECT id FROM nguoi_dung WHERE id = :id'
            );
            $stmtNguoiDung->execute([
                'id' => (int) $nguoiDung['id'],
            ]);

            $idNguoiDung = $stmtNguoiDung->fetchColumn();

            if ($idNguoiDung === false) {
                throw new RuntimeException(
                    'Tài khoản không còn tồn tại.'
                );
            }

            // Tạo đơn hàng.
            $stmtDonHang = $pdo->prepare(
                'INSERT INTO don_hang
                    (nguoi_dung_id, ho_ten_nhan, so_dien_thoai_nhan,
                     dia_chi_nhan, ghi_chu, tong_tien, trang_thai)
                 VALUES
                    (:nguoi_dung_id, :ho_ten_nhan, :so_dien_thoai_nhan,
                     :dia_chi_nhan, :ghi_chu, :tong_tien, :trang_thai)'
            );

            $stmtDonHang->execute([
                'nguoi_dung_id' => (int) $idNguoiDung,
                'ho_ten_nhan' => $duLieu['ho_ten_nhan'],
                'so_dien_thoai_nhan' => $duLieu['so_dien_thoai_nhan'],
                'dia_chi_nhan' => $duLieu['dia_chi_nhan'],
                'ghi_chu' => $duLieu['ghi_chu'] !== ''
                    ? $duLieu['ghi_chu']
                    : null,
                'tong_tien' => $tongTien,
                'trang_thai' => 'cho_xac_nhan',
            ]);

            $idDonHang = (int) $pdo->lastInsertId();

            // Lưu từng sản phẩm trong đơn hàng.
            $stmtChiTiet = $pdo->prepare(
                'INSERT INTO chi_tiet_don_hang
                    (don_hang_id, san_pham_id, ten_san_pham,
                     gia_don_vi, so_luong, thanh_tien)
                 VALUES
                    (:don_hang_id, :san_pham_id, :ten_san_pham,
                     :gia_don_vi, :so_luong, :thanh_tien)'
            );

            foreach ($cacMuc as $muc) {
                $sanPham = $muc['sanPham'];
                $soLuong = (int) $muc['soLuong'];
                $gia = (int) $sanPham->gia;

                $stmtChiTiet->execute([
                    'don_hang_id' => $idDonHang,
                    'san_pham_id' => $sanPham->id,
                    'ten_san_pham' => $sanPham->ten,
                    'gia_don_vi' => $gia,
                    'so_luong' => $soLuong,
                    'thanh_tien' => $gia * $soLuong,
                ]);
            }

            $pdo->commit();

            // Chỉ xóa giỏ hàng sau khi giao dịch thành công.
            $gioHang->xoaHet();
            unset($_SESSION['csrf_dat_hang']);

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đặt hàng thành công. Mã đơn hàng: #' . $idDonHang,
            ];

            redirect('don-hang.php');

        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                '[DAT_HANG] ' . $e->getMessage()
            );

            $loi[] = 'Không thể lưu đơn hàng lúc này. '
                . 'Giỏ hàng của bạn vẫn được giữ lại. Vui lòng thử lại.';
        }
    }
}

$tongTien = $gioHang->tongTien($khoSanPham);
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <h1 class="tieu-de-trang">Thông tin đặt hàng</h1>

        <?php if ($loi !== []): ?>
            <div role="alert" aria-live="polite">
                <ul>
                    <?php foreach ($loi as $thongBao): ?>
                        <li><?= e($thongBao) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="khu-vuc">
            <h2>Thông tin nhận hàng</h2>

            <form method="post" action="dat-hang.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_dat_hang']) ?>"
                >

                <div class="nhom-form">
                    <label for="ho_ten_nhan">Họ tên người nhận *</label>
                    <input
                        type="text"
                        id="ho_ten_nhan"
                        name="ho_ten_nhan"
                        maxlength="100"
                        required
                        value="<?= e($duLieu['ho_ten_nhan']) ?>"
                    >
                </div>

                <div class="nhom-form">
                    <label for="so_dien_thoai_nhan">Số điện thoại *</label>
                    <input
                        type="tel"
                        id="so_dien_thoai_nhan"
                        name="so_dien_thoai_nhan"
                        maxlength="20"
                        required
                        value="<?= e($duLieu['so_dien_thoai_nhan']) ?>"
                    >
                </div>

                <div class="nhom-form">
                    <label for="dia_chi_nhan">Địa chỉ nhận hàng *</label>
                    <textarea
                        id="dia_chi_nhan"
                        name="dia_chi_nhan"
                        maxlength="255"
                        required
                        rows="3"
                    ><?= e($duLieu['dia_chi_nhan']) ?></textarea>
                </div>

                <div class="nhom-form">
                    <label for="ghi_chu">Ghi chú</label>
                    <textarea
                        id="ghi_chu"
                        name="ghi_chu"
                        maxlength="2000"
                        rows="3"
                    ><?= e($duLieu['ghi_chu']) ?></textarea>
                </div>

                <h2>Đơn hàng của bạn</h2>

                <ul>
                    <?php foreach ($cacMuc as $muc): ?>
                        <li>
                            <?= e($muc['sanPham']->ten) ?>
                            — <?= e($muc['soLuong']) ?> ×
                            <?= vnd($muc['sanPham']->gia) ?>
                            = <?= vnd($muc['thanhTien']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p class="gia">
                    <strong>Tổng thanh toán: <?= vnd($tongTien) ?></strong>
                </p>

                <button class="nut" type="submit">
                    Xác nhận đặt hàng
                </button>

                <a class="nut" href="gio-hang.php">Quay lại giỏ hàng</a>
            </form>
        </section>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
