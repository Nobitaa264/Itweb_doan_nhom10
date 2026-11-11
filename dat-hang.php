<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Config\Database;
use App\Data\KhoSanPham;
use App\Services\GioHang;

$tieuDe = 'Đặt hàng - StyleHub';
$trang = 'dat-hang';
$goc = './';

// TH7: Kiểm tra CSRF trước mọi xử lý dữ liệu, đặt hàng hay trừ tồn kho.
// POST thiếu/sai mã nhận HTTP 403 và không thể tạo đơn hàng.
csrfKiemTraPost();

// Chỉ khách hàng đã đăng nhập mới được đặt hàng.
$nguoiDung = nguoiDungHienTai();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dùng chuoi() để tham số dạng mảng không gây TypeError.
    foreach ($duLieu as $truong => $giaTriMacDinh) {
        $duLieu[$truong] = chuoi($_POST[$truong] ?? $giaTriMacDinh);
    }

    if ($duLieu['ho_ten_nhan'] === '' || mb_strlen($duLieu['ho_ten_nhan']) > 100) {
        $loi[] = 'Họ tên người nhận không được để trống và tối đa 100 ký tự.';
    }

    if (!preg_match('/^[0-9+\s().-]{9,20}$/', $duLieu['so_dien_thoai_nhan'])) {
        $loi[] = 'Số điện thoại không hợp lệ.';
    }

    if ($duLieu['dia_chi_nhan'] === '' || mb_strlen($duLieu['dia_chi_nhan']) > 255) {
        $loi[] = 'Địa chỉ nhận hàng không được để trống và tối đa 255 ký tự.';
    }

    if (mb_strlen($duLieu['ghi_chu']) > 2000) {
        $loi[] = 'Ghi chú không được vượt quá 2000 ký tự.';
    }

    // Kiểm tra đầy đủ giỏ hàng trong session: không dùng giá/số lượng từ form.
    $cacMuc = $gioHang->cacMuc($khoSanPham);
    $gioTrongSession = $_SESSION['gio'] ?? [];
    $soLuongDat = [];

    if (!is_array($gioTrongSession) || $gioTrongSession === [] ||
        count($cacMuc) !== count($gioTrongSession)) {
        $loi[] = 'Giỏ hàng có sản phẩm không hợp lệ. Vui lòng kiểm tra lại.';
    } else {
        foreach ($gioTrongSession as $id => $sl) {
            $soLuong = filter_var(
                $sl,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 99]]
            );
            if (!is_string($id) || trim($id) === '' || $soLuong === false) {
                $loi[] = 'Số lượng hoặc mã sản phẩm trong giỏ hàng không hợp lệ.';
                break;
            }
            $soLuongDat[$id] = (int) $soLuong;
        }
    }

    if ($loi === []) {
        $pdo = null;

        try {
            $pdo = Database::connect();
            $pdo->beginTransaction();

            // Tài khoản phải còn tồn tại trong CSDL.
            $stmtNguoiDung = $pdo->prepare(
                'SELECT id FROM nguoi_dung WHERE id = :id'
            );
            $stmtNguoiDung->execute(['id' => (int) $nguoiDung['id']]);
            $idNguoiDung = $stmtNguoiDung->fetchColumn();
            if ($idNguoiDung === false) {
                throw new DomainException('Tài khoản không còn tồn tại.');
            }

            // Khóa các sản phẩm theo thứ tự mã cố định để giảm nguy cơ deadlock.
            ksort($soLuongDat, SORT_STRING);
            $stmtKhoaSanPham = $pdo->prepare(
                'SELECT id, ten, gia, ton_kho, tinh_trang
                 FROM san_pham WHERE id = :id FOR UPDATE'
            );
            $cacDongDonHang = [];
            $tongTien = 0;

            foreach ($soLuongDat as $id => $soLuong) {
                $stmtKhoaSanPham->execute(['id' => $id]);
                $sanPham = $stmtKhoaSanPham->fetch(PDO::FETCH_ASSOC);

                if ($sanPham === false) {
                    throw new DomainException(
                        'Sản phẩm mã ' . $id . ' không còn tồn tại.'
                    );
                }

                $ten = (string) $sanPham['ten'];
                $tonKho = (int) $sanPham['ton_kho'];
                $gia = (int) $sanPham['gia'];
                $tinhTrang = (string) $sanPham['tinh_trang'];

                if ($tinhTrang !== 'Còn hàng' || $tonKho === 0) {
                    throw new DomainException(
                        'Sản phẩm "' . $ten . '" đã hết hàng, không thể đặt.'
                    );
                }
                if ($tonKho < $soLuong) {
                    throw new DomainException(
                        'Sản phẩm "' . $ten . '" chỉ còn ' . $tonKho .
                        ' sản phẩm, không đủ số lượng yêu cầu (' . $soLuong . ').'
                    );
                }
                if ($gia <= 0) {
                    throw new DomainException(
                        'Giá sản phẩm "' . $ten . '" không hợp lệ.'
                    );
                }

                // Lấy giá và tên mới nhất trực tiếp từ CSDL đã khóa.
                $thanhTien = $gia * $soLuong;
                $tongTien += $thanhTien;
                $cacDongDonHang[] = [
                    'id' => $id,
                    'ten' => $ten,
                    'gia' => $gia,
                    'so_luong' => $soLuong,
                    'ton_kho' => $tonKho,
                    'thanh_tien' => $thanhTien,
                ];
            }

            if ($tongTien <= 0 || $cacDongDonHang === []) {
                throw new DomainException('Tổng tiền đơn hàng không hợp lệ.');
            }

            // Tạo đơn hàng: vẫn ở trong cùng giao dịch với kiểm tra/trừ kho.
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
                'ghi_chu' => $duLieu['ghi_chu'] !== '' ? $duLieu['ghi_chu'] : null,
                'tong_tien' => $tongTien,
                'trang_thai' => 'cho_xac_nhan',
            ]);
            $idDonHang = (int) $pdo->lastInsertId();

            $stmtTruKho = $pdo->prepare(
                'UPDATE san_pham
                 SET ton_kho = ton_kho - :sl_tru
                 WHERE id = :id AND tinh_trang = :tinh_trang
                   AND ton_kho >= :sl_kiem'
            );
            $stmtHetHang = $pdo->prepare(
                'UPDATE san_pham SET tinh_trang = :het_hang WHERE id = :id'
            );
            $stmtChiTiet = $pdo->prepare(
                'INSERT INTO chi_tiet_don_hang
                    (don_hang_id, san_pham_id, ten_san_pham,
                     gia_don_vi, so_luong, thanh_tien)
                 VALUES
                    (:don_hang_id, :san_pham_id, :ten_san_pham,
                     :gia_don_vi, :so_luong, :thanh_tien)'
            );

            foreach ($cacDongDonHang as $muc) {
                $stmtTruKho->execute([
                    'sl_tru' => $muc['so_luong'],
                    'id' => $muc['id'],
                    'tinh_trang' => 'Còn hàng',
                    'sl_kiem' => $muc['so_luong'],
                ]);
                if ($stmtTruKho->rowCount() !== 1) {
                    throw new DomainException(
                        'Số lượng tồn kho vừa thay đổi. Vui lòng thử lại.'
                    );
                }

                // Khi vừa bán hết, cập nhật cả trạng thái hiển thị.
                if ($muc['ton_kho'] === $muc['so_luong']) {
                    $stmtHetHang->execute([
                        'het_hang' => 'Hết hàng',
                        'id' => $muc['id'],
                    ]);
                }

                $stmtChiTiet->execute([
                    'don_hang_id' => $idDonHang,
                    'san_pham_id' => $muc['id'],
                    'ten_san_pham' => $muc['ten'],
                    'gia_don_vi' => $muc['gia'],
                    'so_luong' => $muc['so_luong'],
                    'thanh_tien' => $muc['thanh_tien'],
                ]);
            }

            // Cả đơn hàng, chi tiết và tồn kho thành công hoặc cùng rollback.
            $pdo->commit();
            $gioHang->xoaHet();

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đặt hàng thành công. Mã đơn hàng: #' . $idDonHang,
            ];
            redirect('don-hang.php');

        } catch (DomainException $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Lỗi nghiệp vụ: hiển thị rõ nguyên nhân, giữ nguyên giỏ hàng.
            $loi[] = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[DAT_HANG] ' . $e->getMessage());
            $loi[] = 'Không thể lưu đơn hàng lúc này. '
                . 'Giỏ hàng vẫn được giữ lại. Vui lòng thử lại.';
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
                <?= csrfField() ?>

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
