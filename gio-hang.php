<?php
/**
 * gio-hang.php
 * Hiển thị và xử lý giỏ hàng bằng session.
 *
 * Chức năng:
 * - Cập nhật số lượng
 * - Xóa một sản phẩm
 * - Xóa toàn bộ giỏ hàng
 * - Tính thành tiền và tổng tiền từ dữ liệu sản phẩm
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$tieuDe = 'Giỏ hàng - StyleHub';
$trang = 'gio-hang';
$goc = './';

$khoSanPham = new KhoSanPham();
$gioHang = new GioHang();

/*
 * Xử lý POST trước khi xuất HTML.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hanhDong = trim(
        (string) ($_POST['hanh-dong'] ?? '')
    );

    try {

        /*
         * Xóa toàn bộ giỏ hàng.
         */
        if ($hanhDong === 'xoa-het') {

            $gioHang->xoaHet();

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đã xóa toàn bộ giỏ hàng.',
            ];

            redirect('gio-hang.php');
        }


        /*
         * Xóa một sản phẩm.
         */
        if ($hanhDong === 'xoa') {

            $id = trim((string) ($_POST['id'] ?? ''));

            if ($id === '') {
                throw new DomainException(
                    'ID sản phẩm không hợp lệ.'
                );
            }

            $gioHang->xoa($id);

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            ];

            redirect('gio-hang.php');
        }


        /*
         * Cập nhật nhiều sản phẩm.
         */
        if ($hanhDong === 'cap-nhat') {

            $cacSoLuong = $_POST['so-luong'] ?? null;

            if (!is_array($cacSoLuong)) {
                throw new DomainException(
                    'Dữ liệu số lượng không hợp lệ.'
                );
            }

            $duLieuHopLe = [];

            foreach ($cacSoLuong as $id => $giaTriSoLuong) {

                $id = trim((string) $id);

                if ($id === '') {
                    throw new DomainException(
                        'ID sản phẩm không hợp lệ.'
                    );
                }

                $soLuong = filter_var(
                    $giaTriSoLuong,
                    FILTER_VALIDATE_INT
                );

                if (
                    $soLuong === false ||
                    $soLuong < 1 ||
                    $soLuong > 99
                ) {
                    throw new DomainException(
                        'Số lượng phải từ 1 đến 99.'
                    );
                }

                if ($khoSanPham->timTheoId($id) === null) {
                    throw new DomainException(
                        'Sản phẩm không tồn tại.'
                    );
                }

                $duLieuHopLe[$id] = $soLuong;
            }

            foreach ($duLieuHopLe as $id => $soLuong) {
                $gioHang->capNhat(
                    $id,
                    $soLuong,
                    $khoSanPham
                );
            }

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đã cập nhật giỏ hàng.',
            ];

            redirect('gio-hang.php');
        }

        throw new DomainException(
            'Thao tác giỏ hàng không hợp lệ.'
        );

    } catch (DomainException $ngoaiLe) {

        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => $ngoaiLe->getMessage(),
        ];

        redirect('gio-hang.php');
    }
}


/*
 * Lấy các sản phẩm hiện có trong giỏ.
 */
$cacMucGioHang = $gioHang->cacMuc(
    $khoSanPham
);

$tongTien = $gioHang->tongTien(
    $khoSanPham
);

$soMon = $gioHang->soMon();
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">

    <div class="bao">

        <h1 class="tieu-de-trang">
            Giỏ hàng
        </h1>

        <?php if (isset($_SESSION['flash'])): ?>

            <?php
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            ?>

            <p
                role="status"
                aria-live="polite"
            >
                <?= e($flash['noiDung'] ?? '') ?>
            </p>

        <?php endif; ?>


        <?php if ($cacMucGioHang === []): ?>

            <section class="khu-vuc">

                <h2>
                    Giỏ hàng đang trống
                </h2>

                <p>
                    Bạn chưa có sản phẩm nào trong giỏ hàng.
                </p>

                <p>
                    <a
                        class="nut"
                        href="danh-sach.php"
                    >
                        Xem sản phẩm
                    </a>
                </p>

            </section>


        <?php else: ?>

            <form
                method="post"
                action="gio-hang.php"
            >

                <input
                    type="hidden"
                    name="hanh-dong"
                    value="cap-nhat"
                >

                <div class="khu-vuc">

                    <h2>
                        Sản phẩm trong giỏ
                    </h2>

                    <div class="luoi-danh-sach">

                        <?php foreach (
                            $cacMucGioHang
                            as $muc
                        ): ?>

                            <?php
                            $sanPham = $muc['sanPham'];
                            $soLuong = $muc['soLuong'];
                            $thanhTien = $muc['thanhTien'];
                            ?>

                            <article class="the-san-pham">

                                <img
                                    class="the-san-pham__anh"
                                    src="<?= e($sanPham->hinhAnh) ?>"
                                    alt="<?= e($sanPham->ten) ?>"
                                    width="300"
                                    height="300"
                                >

                                <div
                                    class="the-san-pham__noi-dung"
                                >

                                    <p class="ma-san-pham">
                                        <?= e($sanPham->id) ?>
                                    </p>

                                    <h3>
                                        <?= e($sanPham->ten) ?>
                                    </h3>

                                    <p class="gia">
                                        Đơn giá:
                                        <?= vnd($sanPham->gia) ?>
                                    </p>

                                    <div class="nhom-form">

                                        <label
                                            for="so-luong-<?= e($sanPham->id) ?>"
                                        >
                                            Số lượng
                                        </label>

                                        <input
                                            type="number"
                                            id="so-luong-<?= e($sanPham->id) ?>"
                                            name="so-luong[<?= e($sanPham->id) ?>]"
                                            value="<?= e($soLuong) ?>"
                                            min="1"
                                            max="99"
                                            required
                                        >

                                    </div>

                                    <p class="gia">
                                        Thành tiền:
                                        <?= vnd($thanhTien) ?>
                                    </p>

                                    <button
                                        class="nut"
                                        type="submit"
                                        form="form-xoa-<?= e($sanPham->id) ?>"
                                    >
                                        Xóa
                                    </button>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                    <p>
                        <strong>
                            Tổng số món:
                        </strong>

                        <?= e($soMon) ?>
                    </p>

                    <p class="gia">
                        <strong>
                            Tổng tiền:
                        </strong>

                        <?= vnd($tongTien) ?>
                    </p>

                    <button
                        class="nut"
                        type="submit"
                    >
                        Cập nhật giỏ hàng
                    </button>

                </div>

            </form>


            <!-- Form xóa từng sản phẩm -->
            <?php foreach (
                $cacMucGioHang
                as $muc
            ): ?>

                <?php
                $sanPham = $muc['sanPham'];
                ?>

                <form
                    id="form-xoa-<?= e($sanPham->id) ?>"
                    method="post"
                    action="gio-hang.php"
                >

                    <input
                        type="hidden"
                        name="hanh-dong"
                        value="xoa"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= e($sanPham->id) ?>"
                    >

                </form>

            <?php endforeach; ?>


            <section class="khu-vuc">
                <h2>Giỏ hàng của bạn</h2>
                <form
                    method="post"
                    action="gio-hang.php"
                >

                    <input
                        type="hidden"
                        name="hanh-dong"
                        value="xoa-het"
                    >

                    <button
                        class="nut"
                        type="submit"
                    >
                        Xóa toàn bộ giỏ hàng
                    </button>
                    
                    <?php if ($cacMucGioHang !== []): ?>
                        <p>
                            <a class="nut" href="dat-hang.php">
                                Tiến hành đặt hàng
                            </a>
                        </p>
                    <?php endif; ?>

                </form>

            </section>

        <?php endif; ?>

    </div>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>