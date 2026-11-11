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
$cssBoSung = 'css/chi-tiet-lien-quan.css';

$khoSanPham = new KhoSanPham();

// Chỉ chấp nhận ID dạng chuỗi, tránh Warning khi id[]=...
$idNhap = $_GET['id'] ?? '';
$id = is_string($idNhap) ? trim($idNhap) : '';

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

// Bốn sản phẩm khác trong cùng danh mục, lấy trực tiếp từ CSDL.
$sanPhamLienQuan = $khoSanPham->cungDanhMuc($sanPham->id, 4);

// Trạng thái tồn kho lấy trực tiếp từ CSDL, không chỉ dựa trên nhãn tình trạng.
$thongTinKho = $khoSanPham->trangThaiTonKho($sanPham->id);
$tonKho = $thongTinKho['ton_kho'] ?? 0;
$tinhTrangHienTai = $thongTinKho['tinh_trang'] ?? 'Hết hàng';
$coTheMua = $tinhTrangHienTai === 'Còn hàng' && $tonKho > 0;

/*
 * Xử lý thêm vào giỏ trước khi có output.
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['hanh_dong'] ?? '') === 'them-gio'
) {
    // Form thêm giỏ cũng cần mã CSRF; sẽ tích hợp kiểm tra tập trung ở TH7.
    kiemTraCsrf();

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
                        <?= e($tinhTrangHienTai) ?>
                    </p>

                    <?php if (!$coTheMua): ?>
                        <p class="thong-bao-het-hang" role="status">
                            Sản phẩm hiện đã hết hàng, không thể thêm vào giỏ.
                        </p>
                    <?php else: ?>
                        <p class="so-luong-ton">Còn <?= e($tonKho) ?> sản phẩm trong kho.</p>
                    <?php endif; ?>

                    <form
                        class="form-them-gio-hang"
                        action="chi-tiet.php?id=<?= urlencode($sanPham->id) ?>"
                        method="post"
                    >
                        <?= csrfField() ?>
                        <input
                            type="hidden"
                            name="san_pham_id"
                            value="<?= e($sanPham->id) ?>"
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
                                max="<?= e(min(99, max(1, $tonKho))) ?>"
                                required
                                <?= $coTheMua ? '' : 'disabled' ?>
                            >
                        </div>

                        <!-- CỤM NÚT HÀNH ĐỘNG CÓ NÚT YÊU THÍCH -->
                        <div class="nhom-nut-hanh-dong">
                            <button
                                class="nut"
                                type="submit"
                                <?= $coTheMua ? '' : 'disabled aria-disabled="true"' ?>
                            >
                                <?= $coTheMua ? 'Thêm vào giỏ' : 'Hết hàng' ?>
                            </button>

                            <button
                                type="button"
                                class="nut nut-yeu-thich"
                                data-id-san-pham="<?= e($sanPham->id) ?>"
                            >
                                ♡ Yêu thích
                            </button>
                        </div>
                    </form>

                    <p
                        id="thong-bao-gio-hang"
                        class="thong-bao-gio-hang"
                        role="status"
                        aria-live="polite"
                        hidden
                    ></p>

                    <p class="quay-lai-danh-sach">
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
                                        <?= e($tinhTrangHienTai) ?>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>

            </aside>

        </article>

        <section class="san-pham-lien-quan" aria-labelledby="tieu-de-san-pham-lien-quan">
            <h2 id="tieu-de-san-pham-lien-quan">Sản phẩm cùng danh mục</h2>
            <p>Khám phá thêm các sản phẩm thuộc danh mục <?= e($sanPham->danhMuc) ?>.</p>

            <?php if ($sanPhamLienQuan === []): ?>
                <p role="status">Hiện chưa có sản phẩm khác trong danh mục này.</p>
            <?php else: ?>
                <div class="luoi-san-pham-lien-quan">
                    <?php foreach ($sanPhamLienQuan as $sanPhamKhac): ?>
                        <article class="the-san-pham">
                            <a class="lien-ket-anh-lien-quan"
                               href="chi-tiet.php?id=<?= urlencode($sanPhamKhac->id) ?>"
                               aria-label="Xem chi tiết <?= e($sanPhamKhac->ten) ?>">
                                <img class="the-san-pham__anh"
                                     src="<?= e($sanPhamKhac->hinhAnh) ?>"
                                     alt="<?= e($sanPhamKhac->ten) ?>"
                                     width="300" height="300" loading="lazy">
                            </a>
                            <div class="the-san-pham__noi-dung">
                                <p class="ma-san-pham"><?= e($sanPhamKhac->id) ?></p>
                                <h3><?= e($sanPhamKhac->ten) ?></h3>
                                <p class="gia"><?= vnd($sanPhamKhac->gia) ?></p>
                                <a class="nut" href="chi-tiet.php?id=<?= urlencode($sanPhamKhac->id) ?>">
                                    Xem chi tiết
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

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