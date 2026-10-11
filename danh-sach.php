<?php
/**
 * danh-sach.php
 * Hiển thị danh sách sản phẩm bằng PHP.
 * Tìm kiếm, lọc và sắp xếp được xử lý phía máy chủ.
 */

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Danh sách sản phẩm - StyleHub';
$trang = 'danh-sach';
$goc = './';

$khoSanPham = new \App\Data\KhoSanPham();

$tuKhoa = trim(
    (string) ($_GET['q'] ?? '')
);

$danhMuc = trim(
    (string) ($_GET['dm'] ?? 'tat-ca')
);

$sapXep = trim(
    (string) ($_GET['sx'] ?? 'mac-dinh')
);


/*
 * Danh sách giá trị được phép.
 */

$danhMucChoPhep =
    $khoSanPham->danhMuc();

$sapXepChoPhep = [
    'mac-dinh',
    'gia-tang',
    'gia-giam',
    'ten-a-z',
    'ten-z-a',
];


/*
 * Kiểm tra danh mục.
 */

if (
    $danhMuc !== 'tat-ca' &&
    !in_array(
        $danhMuc,
        $danhMucChoPhep,
        true
    )
) {
    $danhMuc = 'tat-ca';
}


/*
 * Kiểm tra kiểu sắp xếp.
 */

if (
    !in_array(
        $sapXep,
        $sapXepChoPhep,
        true
    )
) {
    $sapXep = 'mac-dinh';
}


/*
 * Lấy kết quả từ lớp dữ liệu.
 */

$danhSachSanPham =
    $khoSanPham->timKiem(
        $tuKhoa,
        $danhMuc,
        $sapXep
    );
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">

    <div class="bao">

        <h1 class="tieu-de-trang">
            Danh sách sản phẩm
        </h1>


        <section
            class="khu-vuc khu-vuc--dau"
            aria-labelledby="tieu-de-cong-cu"
        >

            <h2 id="tieu-de-cong-cu">
                Tìm kiếm và lọc sản phẩm
            </h2>


            <form
                method="get"
                action="danh-sach.php"
            >

                <div class="cong-cu-san-pham">


                    <div class="nhom-cong-cu">

                        <label for="tim-kiem">
                            Tìm kiếm sản phẩm
                        </label>

                        <input
                            type="search"
                            id="tim-kiem"
                            name="q"
                            value="<?= e($tuKhoa) ?>"
                            placeholder="Nhập tên, mã hoặc danh mục..."
                            autocomplete="off"
                        >

                    </div>


                    <div class="nhom-cong-cu">

                        <label for="loc-danh-muc">
                            Lọc theo danh mục
                        </label>

                        <select
                            id="loc-danh-muc"
                            name="dm"
                        >

                            <option value="tat-ca">
                                Tất cả
                            </option>

                            <?php foreach (
                                $danhMucChoPhep
                                as $muc
                            ): ?>

                                <option
                                    value="<?= e($muc) ?>"
                                    <?= $danhMuc === $muc
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($muc) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="nhom-cong-cu">

                        <label for="sap-xep">
                            Sắp xếp
                        </label>

                        <select
                            id="sap-xep"
                            name="sx"
                        >

                            <option
                                value="mac-dinh"
                                <?= $sapXep === 'mac-dinh'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Mặc định
                            </option>

                            <option
                                value="gia-tang"
                                <?= $sapXep === 'gia-tang'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Giá thấp đến cao
                            </option>

                            <option
                                value="gia-giam"
                                <?= $sapXep === 'gia-giam'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Giá cao đến thấp
                            </option>

                            <option
                                value="ten-a-z"
                                <?= $sapXep === 'ten-a-z'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Tên A → Z
                            </option>

                            <option
                                value="ten-z-a"
                                <?= $sapXep === 'ten-z-a'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Tên Z → A
                            </option>

                        </select>

                    </div>


                    <div class="nhom-cong-cu">

                        <button
                            class="nut"
                            type="submit"
                        >
                            Tìm kiếm
                        </button>

                        <a
                            class="nut"
                            href="danh-sach.php"
                        >
                            Xóa lọc
                        </a>

                    </div>

                </div>

            </form>

        </section>


        <section
            class="khu-vuc"
            aria-labelledby="tieu-de-ket-qua"
        >

            <div class="dau-ket-qua">

                <h2 id="tieu-de-ket-qua">
                    Sản phẩm
                </h2>

                <p
                    id="so-luong-ket-qua"
                    class="so-luong-ket-qua"
                    aria-live="polite"
                >
                    <?= count($danhSachSanPham) ?>
                    sản phẩm
                </p>

            </div>


            <?php if ($danhSachSanPham === []): ?>

                <p
                    class="trang-thai-danh-sach"
                    role="status"
                    aria-live="polite"
                >
                    Không tìm thấy sản phẩm phù hợp
                    với điều kiện tìm kiếm.
                </p>


            <?php else: ?>

                <div
                    id="danh-sach-san-pham"
                    class="luoi-danh-sach"
                >

                    <?php foreach (
                        $danhSachSanPham
                        as $sanPham
                    ): ?>

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


                                <p>
                                    <?= e($sanPham->moTa) ?>
                                </p>


                                <p class="gia">
                                    <?= vnd($sanPham->gia) ?>
                                </p>


                                <a
                                    class="nut"
                                    href="chi-tiet.php?id=<?= urlencode($sanPham->id) ?>"
                                >
                                    Xem chi tiết
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <noscript>

                <p>
                    JavaScript đang bị tắt.
                    Chức năng tìm kiếm, lọc và sắp xếp
                    vẫn hoạt động bằng PHP.
                </p>

            </noscript>

        </section>


        <section class="khu-vuc khu-vuc--phu">

            <h2>
                Hướng dẫn
            </h2>

            <p>
                Bạn có thể nhập từ khóa để tìm kiếm,
                chọn danh mục để lọc hoặc chọn cách
                sắp xếp sản phẩm.
            </p>

            <p>
                Chọn
                <strong>Xem chi tiết</strong>
                để xem đầy đủ thông tin của từng sản phẩm.
            </p>

        </section>

    </div>

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