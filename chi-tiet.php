<?php
/**
 * chi-tiet.php
 * Hiển thị chi tiết sản phẩm theo ID.
 * Có cookie lưu 4 sản phẩm xem gần đây
 * và xử lý thêm sản phẩm vào giỏ hàng.
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$tieuDe = 'Chi tiết sản phẩm - StyleHub';
$trang = 'chi-tiet';
$goc = './';

$khoSanPham = new KhoSanPham();

$id = trim(
    (string) ($_GET['id'] ?? '')
);

/*
 * Kiểm tra ID.
 */
$sanPham = null;

if (idSanPhamHopLe($id)) {
    $sanPham = $khoSanPham->timTheoId($id);
}

/*
 * ID không tồn tại hoặc không hợp lệ.
 */
if ($sanPham === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

/*
 * Xử lý thêm vào giỏ trước khi có output.
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['hanh_dong'] ?? '') === 'them-gio'
) {
    $soLuong = filter_var(
        $_POST['so-luong'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $soLuong === false ||
        $soLuong < 1 ||
        $soLuong > 99
    ) {
        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => 'Số lượng phải từ 1 đến 99.',
        ];

        redirect(
            'chi-tiet.php?id=' . urlencode($sanPham->id)
        );
    }

    try {
        $gioHang = new GioHang();

        $gioHang->them(
            $sanPham->id,
            $khoSanPham,
            $soLuong
        );

        $_SESSION['flash'] = [
            'loai' => 'thanh-cong',
            'noiDung' => 'Đã thêm sản phẩm vào giỏ hàng.',
        ];

        redirect(
            'gio-hang.php'
        );
    } catch (DomainException $ngoaiLe) {
        $_SESSION['flash'] = [
            'loai' => 'loi',
            'noiDung' => $ngoaiLe->getMessage(),
        ];

        redirect(
            'chi-tiet.php?id=' . urlencode($sanPham->id)
        );
    }
}

/*
 * Cookie da_xem phải được cập nhật trước mọi output.
 */
$danhSachDaXemCu = layDanhSachDaXem();

capNhatDaXem(
    $sanPham->id,
    $danhSachDaXemCu
);

$tieuDe = $sanPham->ten . ' - StyleHub';

$danhSachDaXem = array_unique(
    array_merge(
        [$sanPham->id],
        $danhSachDaXemCu
    )
);

$danhSachDaXem = array_slice(
    $danhSachDaXem,
    0,
    4
);
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main
    class="trang"
    id="noi-dung-chinh"
>
    <section class="noi-dung">

        <h1>Chi tiết sản phẩm</h1>

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

        <article class="chi-tiet-san-pham">

            <div class="chi-tiet-san-pham__chinh">

                <h2>
                    <?= e($sanPham->ten) ?>
                </h2>

                <figure class="hinh-san-pham">

                    <img
                        src="<?= e($sanPham->hinhAnh) ?>"
                        alt="<?= e($sanPham->ten) ?>"
                        width="400"
                        height="400"
                    >

                    <figcaption>
                        <?= e($sanPham->ten) ?>
                        -
                        <?= e($sanPham->danhMuc) ?>
                    </figcaption>

                </figure>

                <section class="khoi-noi-dung">

                    <h3>Mô tả sản phẩm</h3>

                    <p>
                        <?= e($sanPham->moTa) ?>
                    </p>

                    <p class="gia">
                        <?= vnd($sanPham->gia) ?>
                    </p>

                    <p>
                        <strong>Tình trạng:</strong>
                        <?= e($sanPham->tinhTrang) ?>
                    </p>

                    <form
                        action="chi-tiet.php?id=<?= urlencode($sanPham->id) ?>"
                        method="post"
                    >
                        <input
                            type="hidden"
                            name="hanh_dong"
                            value="them-gio"
                        >

                        <div class="nhom-form">
                            <label for="so-luong">
                                Số lượng
                            </label>

                            <input
                                type="number"
                                id="so-luong"
                                name="so-luong"
                                value="1"
                                min="1"
                                max="99"
                                required
                            >
                        </div>

                        <button
                            class="nut"
                            type="submit"
                        >
                            Thêm vào giỏ
                        </button>
                    </form>

                    <p>
                        <a
                            class="nut"
                            href="danh-sach.php"
                        >
                            Quay lại danh sách
                        </a>
                    </p>

                </section>
            </div>

            <aside class="chi-tiet-san-pham__phu">

                <section class="khoi-noi-dung">

                    <h3>Thông số sản phẩm</h3>

                    <div class="bang-cuon">

                        <table class="bang-chi-tiet">

                            <caption>
                                Thông tin chi tiết
                                <?= e($sanPham->ten) ?>
                            </caption>

                            <tbody>

                                <tr>
                                    <th scope="row">
                                        Mã sản phẩm
                                    </th>
                                    <td>
                                        <?= e($sanPham->id) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Danh mục
                                    </th>
                                    <td>
                                        <?= e($sanPham->danhMuc) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Giá
                                    </th>
                                    <td>
                                        <?= vnd($sanPham->gia) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Chất liệu
                                    </th>
                                    <td>
                                        <?= e($sanPham->chatLieu) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Màu sắc
                                    </th>
                                    <td>
                                        <?= e($sanPham->mauSac) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Kích cỡ
                                    </th>
                                    <td>
                                        <?= e($sanPham->kichCo) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        Tình trạng
                                    </th>
                                    <td>
                                        <?= e($sanPham->tinhTrang) ?>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>

            </aside>

        </article>

    </section>

</main>

<script
    type="module"
    src="js/main.js"
></script>

<script
    type="module"
    src="js/yeu-thich.js"
></script>

<?php require __DIR__ . '/inc/footer.php'; ?>