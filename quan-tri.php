<?php

declare(strict_types=1);

/**
 * quan-tri.php
 *
 * Trang quản trị:
 * - Chỉ người đã đăng nhập mới được truy cập.
 * - Lấy dữ liệu liên hệ thông qua KhoLienHe.
 * - Hiển thị liên hệ mới nhất trước.
 * - Hiển thị đầy đủ thông tin liên hệ và ảnh đính kèm.
 */
require_once __DIR__ . '/inc/config.php';

// Bắt buộc phải có quyền Admin để truy cập trang này
$nguoiDung = yeuCauQuyen('admin');

use App\Config\Database;
use App\Data\KhoLienHe;
use App\Data\KhoSanPham;

$tieuDe = 'Quản trị - StyleHub';
$trang = 'quan-tri';
$goc = './';

$khoLienHe = new KhoLienHe();

$trangThaiDonHang = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao hàng',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy',
];

// Xử lý cập nhật trạng thái đơn hàng.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['hanh_dong'] ?? '') === 'cap_nhat_don_hang'
) {
    $tokenGuiLen = (string) ($_POST['_token'] ?? '');
    $tokenTrongSession = (string) ($_SESSION['csrf_token'] ?? '');

    if (
        $tokenTrongSession === ''
        || !hash_equals($tokenTrongSession, $tokenGuiLen)
    ) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
    }

    $idDonHang = filter_input(
        INPUT_POST,
        'don_hang_id',
        FILTER_VALIDATE_INT
    );

    $trangThaiMoi = (string) ($_POST['trang_thai'] ?? '');

    if (
        !$idDonHang
        || $idDonHang < 1
        || !array_key_exists($trangThaiMoi, $trangThaiDonHang)
    ) {
        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => 'Thông tin cập nhật đơn hàng không hợp lệ.',
        ];
    } else {
        $pdo = Database::connect();

        $stmt = $pdo->prepare(
            'SELECT trang_thai
             FROM don_hang
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $idDonHang]);
        $donHangHienTai = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$donHangHienTai) {
            $_SESSION['flash'] = [
                'loai' => 'loi',
                'noiDung' => 'Không tìm thấy đơn hàng.',
            ];
        } else {
            $trangThaiCu = (string) $donHangHienTai['trang_thai'];

            // Không cho phép chuyển đơn đã hoàn thành hoặc đã hủy
            // sang một trạng thái khác.
            $trangThaiKetThuc = ['hoan_thanh', 'da_huy'];

            if (
                in_array($trangThaiCu, $trangThaiKetThuc, true)
                && $trangThaiMoi !== $trangThaiCu
            ) {
                $_SESSION['flash'] = [
                    'loai' => 'loi',
                    'noiDung' => 'Đơn hàng đã kết thúc, không thể đổi trạng thái.',
                ];
            } else {
                $update = $pdo->prepare(
                    'UPDATE don_hang
                     SET trang_thai = :trang_thai
                     WHERE id = :id'
                );

                $update->execute([
                    'trang_thai' => $trangThaiMoi,
                    'id' => $idDonHang,
                ]);

                $_SESSION['flash'] = [
                    'loai' => 'thanh-cong',
                    'noiDung' => 'Đã cập nhật trạng thái đơn hàng #'
                        . $idDonHang . ' thành '
                        . $trangThaiDonHang[$trangThaiMoi] . '.',
                ];
            }
        }
    }

    header('Location: quan-tri.php#quan-ly-don-hang');
    exit;
}

$khoSanPham = new KhoSanPham();

$danhSachLienHe = $khoLienHe->tatCa();
$danhSachSanPham = $khoSanPham->tatCa();

$pdo = Database::connect();

// Lấy đơn hàng mới nhất trước.
$stmtDonHang = $pdo->query(
    'SELECT id, nguoi_dung_id, ho_ten_nhan,
            so_dien_thoai_nhan, dia_chi_nhan,
            ghi_chu, tong_tien, trang_thai, created_at
     FROM don_hang
     ORDER BY created_at DESC, id DESC'
);

$danhSachDonHang = $stmtDonHang->fetchAll(PDO::FETCH_ASSOC);

// Lấy các sản phẩm thuộc đơn hàng.
$stmtChiTiet = $pdo->query(
    'SELECT don_hang_id, ten_san_pham,
            gia_don_vi, so_luong, thanh_tien
     FROM chi_tiet_don_hang
     ORDER BY don_hang_id DESC'
);

$danhSachChiTiet = $stmtChiTiet->fetchAll(PDO::FETCH_ASSOC);

$chiTietTheoDonHang = [];



foreach ($danhSachChiTiet as $chiTiet) {
    $id = (int) $chiTiet['don_hang_id'];
    $chiTietTheoDonHang[$id][] = $chiTiet;
}


$nguoiDung = $_SESSION['nguoi_dung'] ?? null;

$flash = null;

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);
}
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">

        <h1 class="tieu-de-trang">
            Quản trị
        </h1>

        <p>
            Xin chào,
            <strong>
                <?= e($nguoiDung['ten'] ?? '') ?>
            </strong>.
        </p>

        <?php if ($flash !== null): ?>

            <p
                class="thong-bao"
                role="status"
                aria-live="polite"
            >
                <?= e($flash['noiDung'] ?? '') ?>
            </p>

        <?php endif; ?>

        <section class="khu-vuc">

            <h2>
                Danh sách liên hệ
            </h2>

            
<section class="khu-vuc" id="quan-ly-don-hang">
    <h2>Quản lý đơn hàng</h2>

    <?php if ($danhSachDonHang === []): ?>
        <p>Chưa có đơn hàng nào.</p>
    <?php else: ?>
        <div class="bang-cuon">
            <table>
                <caption>Danh sách đơn hàng mới nhất</caption>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Người nhận</th>
                        <th>Liên hệ</th>
                        <th>Địa chỉ</th>
                        <th>Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachDonHang as $donHang): ?>
                        <?php
                        $idDonHang = (int) $donHang['id'];
                        $trangThaiHienTai = (string) $donHang['trang_thai'];
                        $chiTietDon = $chiTietTheoDonHang[$idDonHang] ?? [];
                        ?>
                        <tr>
                            <td>#<?= $idDonHang ?></td>
                            <td><?= e($donHang['ho_ten_nhan']) ?></td>
                            <td><?= e($donHang['so_dien_thoai_nhan']) ?></td>
                            <td><?= e($donHang['dia_chi_nhan']) ?></td>
                            <td>
                                <?php if ($chiTietDon === []): ?>
                                    <span>Không có sản phẩm</span>
                                <?php else: ?>
                                    <ul>
                                        <?php foreach ($chiTietDon as $sp): ?>
                                            <li>
                                                <?= e($sp['ten_san_pham']) ?>
                                                × <?= (int) $sp['so_luong'] ?>
                                                — <?= number_format((float) $sp['thanh_tien'], 0, ',', '.') ?> ₫
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if (!empty($donHang['ghi_chu'])): ?>
                                    <p><strong>Ghi chú:</strong>
                                        <?= e($donHang['ghi_chu']) ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= number_format((float) $donHang['tong_tien'], 0, ',', '.') ?> ₫
                            </td>
                            <td><?= e($donHang['created_at']) ?></td>
                            <td>
                                <?= e(
                                    $trangThaiDonHang[$trangThaiHienTai]
                                    ?? $trangThaiHienTai
                                ) ?>
                            </td>
                            <td>
                                <?php
                                $donDaKetThuc = in_array(
                                    $trangThaiHienTai,
                                    ['hoan_thanh', 'da_huy'],
                                    true
                                );
                                ?>

                                <?php if ($donDaKetThuc): ?>
                                    <p>Đơn hàng đã kết thúc.</p>
                                <?php else: ?>
                                    <form method="post" action="quan-tri.php#quan-ly-don-hang">
                                        <input type="hidden" name="hanh_dong"
                                            value="cap_nhat_don_hang">
                                        <input type="hidden" name="don_hang_id"
                                            value="<?= $idDonHang ?>">
                                        <input type="hidden" name="_token"
                                            value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">

                                        <label for="trang-thai-<?= $idDonHang ?>">
                                            Trạng thái mới
                                        </label>
                                        <select
    id="trang-thai-<?= $idDonHang ?>"
    name="trang_thai"
>
    <?php foreach ($trangThaiDonHang as $ma => $ten): ?>
        <option
            value="<?= e($ma) ?>"
            <?= $trangThaiHienTai === $ma ? 'selected' : '' ?>
        >
            <?= e($ten) ?>
        </option>
    <?php endforeach; ?>
</select>

                                        <button type="submit" class="nut">
                                            Cập nhật
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>


            <?php if ($danhSachLienHe === []): ?>

                <p>
                    Chưa có liên hệ nào.
                </p>

            <?php else: ?>

                <?php foreach (
                    $danhSachLienHe as $lienHe
                ): ?>

                    <article class="khoi-noi-dung">

                        <h3>
                            <?= e(
                                $lienHe['hoTen']
                                ?? 'Không có tên'
                            ) ?>
                        </h3>

                        <p>
                            <strong>
                                Số điện thoại:
                            </strong>

                            <?= e(
                                $lienHe['soDienThoai']
                                ?? ''
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Email:
                            </strong>

                            <?= e(
                                $lienHe['email']
                                ?? ''
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Xe quan tâm:
                            </strong>

                            <?= e(
                                $lienHe['sanPhamTen']
                                ?? $lienHe['sanPhamId']
                                ?? 'Không chọn'
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Nội dung:
                            </strong>
                        </p>

                        <p>
                            <?= nl2br(
                                e(
                                    $lienHe['noiDung']
                                    ?? ''
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Thời gian:
                            </strong>

                            <?= e(
                                $lienHe['thoiGian']
                                ?? ''
                            ) ?>
                        </p>

                        <?php
                        $tenAnh = (string) (
                            $lienHe['anh'] ?? ''
                        );

                        $tenAnhAnToan = basename($tenAnh);
                        ?>

                        <?php if ($tenAnhAnToan !== ''): ?>

                            <p>
                                <strong>
                                    Ảnh đính kèm:
                                </strong>
                            </p>

                            <img
                                src="uploads/<?= e($tenAnhAnToan) ?>"
                                alt="Ảnh đính kèm của liên hệ"
                                width="300"
                                loading="lazy"
                            >

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>
        
        <section class="khu-vuc">
            <h2>Danh sách sản phẩm</h2>

            <p>
                <a href="them-san-pham.php">+ Thêm sản phẩm</a>
            </p>

            <?php if ($danhSachSanPham === []): ?>
                <p>Chưa có sản phẩm nào.</p>
            <?php else: ?>
                <div class="bang-cuon">
                    <table class="bang-san-pham">
                        <caption>Danh sách sản phẩm trong hệ thống</caption>
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Tình trạng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachSanPham as $sanPham): ?>
                                <tr>
                                    <td><?= e($sanPham->id) ?></td>
                                    <td><?= e($sanPham->ten) ?></td>
                                    <td><?= e($sanPham->danhMuc) ?></td>
                                    <td><?= e(vnd($sanPham->gia)) ?></td>
                                    <td><?= e($sanPham->tinhTrang) ?></td>
                                    
<td>
    <a href="chi-tiet.php?id=<?= rawurlencode($sanPham->id) ?>">
        Xem
    </a>
    |
    <a href="sua-san-pham.php?id=<?= rawurlencode($sanPham->id) ?>">
        Sửa
    </a>
    |
    <form method="post" action="xoa-san-pham.php"
        class="form-xoa-san-pham">
        <input type="hidden" name="id"
            value="<?= e($sanPham->id) ?>">
        <input type="hidden" name="_token"
            value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
        <button type="submit" class="nut nut-phu">
            Xóa
        </button>
    </form>
</td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>