<?php
/**
 * index.php
 * Trang chủ StyleHub.
 * Dữ liệu sản phẩm được lấy thông qua App\Data\KhoSanPham.
 */

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Trang chủ - StyleHub';
$trang = 'index';
$goc = './';

$khoSanPham = new \App\Data\KhoSanPham();

$danhSachSanPham = $khoSanPham->tatCa();

$sanPhamNoiBat = array_slice($danhSachSanPham, 0, 2);

$danhMuc = $khoSanPham->danhMuc();

$danhSachDaXem = layDanhSachDaXem();

$sanPhamDaXem = [];

foreach ($danhSachDaXem as $idDaXem) {
    $sanPham = $khoSanPham->timTheoId($idDaXem);

    if ($sanPham !== null) {
        $sanPhamDaXem[] = $sanPham;
    }
}

?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="trang" id="noi-dung-chinh">

    <section class="noi-dung">

        <section class="hero-home" aria-labelledby="tieu-de-hero">
            <div class="hero-home__noi-dung">
                <p class="hero-home__nhan">STYLEHUB · PHONG CÁCH CỦA BẠN</p>
                <h1 id="tieu-de-hero">Thời trang cho <span>phong cách riêng</span> của bạn</h1>
                <p class="hero-home__mo-ta">
                    Chào mừng bạn đến với StyleHub. Khám phá những lựa chọn thời trang
                    đơn giản, hiện đại và dễ phối đồ cho mỗi ngày.
                </p>
                <div class="hero-home__hanh-dong">
                    <a class="nut nut--chinh" href="danh-sach.php">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
                    <a class="hero-home__lien-ket" href="gioi-thieu.php">Tìm hiểu về StyleHub</a>
                </div>
                <div class="hero-home__diem-noi-bat" aria-label="Điểm nổi bật">
                    <span><strong>01</strong> Dễ phối đồ</span>
                    <span><strong>02</strong> Phong cách hiện đại</span>
                    <span><strong>03</strong> Khám phá dễ dàng</span>
                </div>
            </div>
            <div class="hero-home__hinh-khoi" aria-hidden="true">
                <div class="hero-home__vong hero-home__vong--lon"></div>
                <div class="hero-home__vong hero-home__vong--nho"></div>
                <div class="hero-home__the-noi">
                    <span class="hero-home__the-noi-nhan">YOUR EVERYDAY EDIT</span>
                    <span class="hero-home__the-noi-chu">STYLE<br>YOUR<br>DAY.</span>
                    <span class="hero-home__the-noi-chan">SIMPLE · MODERN · YOU</span>
                </div>
                <span class="hero-home__cham hero-home__cham--mot"></span>
                <span class="hero-home__cham hero-home__cham--hai"></span>
            </div>
        </section>

        <?php if ($danhMuc !== []): ?>
            <nav class="danh-muc-home" aria-label="Danh mục sản phẩm">
                <p class="danh-muc-home__nhan">KHÁM PHÁ THEO DANH MỤC</p>
                <ul class="danh-muc-home__ds">
                    <?php foreach ($danhMuc as $muc): ?>
                        <li><?= e($muc) ?></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <section class="khoi-noi-dung">
            <h2>Sản phẩm nổi bật</h2>

            <div class="luoi-danh-sach">

                <?php foreach ($sanPhamNoiBat as $sanPham): ?>

                    <article class="the-san-pham">

                        <img
                            class="the-san-pham__anh"
                            src="<?= e($sanPham->hinhAnh) ?>"
                            alt="<?= e($sanPham->ten) ?>"
                            width="300"
                            height="300"
                            <?= $sanPham === $sanPhamNoiBat[0] ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                        >

                        <div class="the-san-pham__noi-dung">

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

            <p>
                <a class="nut" href="danh-sach.php">
                    Xem tất cả sản phẩm
                </a>
            </p>
        </section>


        <section
            class="khoi-noi-dung"
            aria-labelledby="tieu-de-tin"
        >

            <h2 id="tieu-de-tin">
                Tin mới từ cộng đồng
            </h2>

            <p>
                Nội dung dưới đây được tải từ một REST API công khai
                bằng Fetch API.
            </p>

            <p
                id="trang-thai-tin"
                class="trang-thai-tin"
                role="status"
                aria-live="polite"
            >
                Đang tải nội dung mới...
            </p>

            <div
                id="danh-sach-tin"
                class="danh-sach-tin"
            >
                <!-- JavaScript xử lý REST API -->
            </div>

            <noscript>
                <p>
                    JavaScript đang bị tắt nên nội dung từ REST API
                    không thể được tải.
                </p>

                <p>
                    Bạn vẫn có thể xem các sản phẩm nổi bật,
                    danh mục và những nội dung khác.
                </p>
            </noscript>

        </section>

<?php if ($sanPhamDaXem !== []): ?>

    <section
        class="khoi-noi-dung"
        aria-labelledby="tieu-de-da-xem"
    >

        <h2 id="tieu-de-da-xem">
            Đã xem gần đây
        </h2>

        <div class="luoi-danh-sach">

            <?php foreach ($sanPhamDaXem as $sanPham): ?>

                <article class="the-san-pham">

                    <img
                        class="the-san-pham__anh"
                        src="<?= e($sanPham->hinhAnh) ?>"
                        alt="<?= e($sanPham->ten) ?>"
                        width="300"
                        height="300"
                        loading="lazy"
                    >

                    <div class="the-san-pham__noi-dung">

                        <p class="ma-san-pham">
                            <?= e($sanPham->id) ?>
                        </p>

                        <h3>
                            <?= e($sanPham->ten) ?>
                        </h3>

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

    </section>

<?php endif; ?>

        <section
            class="khoi-noi-dung"
            aria-labelledby="tieu-de-video"
        >

            <h2 id="tieu-de-video">
                Video giới thiệu
            </h2>

            <p>
                Xem video giới thiệu về phong cách thời trang
                của StyleHub.
            </p>

            <button
                class="nut"
                id="nut-xem-video"
                type="button"
                aria-controls="khu-vuc-video"
            >
                Xem video giới thiệu
            </button>

            <div
                id="khu-vuc-video"
                class="video-lien-ket"
            >
                <noscript>
                    <p>
                        Trình duyệt đang tắt JavaScript.
                        Bạn vẫn có thể sử dụng các nội dung
                        và liên kết chính của StyleHub.
                    </p>
                </noscript>
            </div>

        </section>

    </section>


    <aside class="thanh-ben">

        <section class="khoi-noi-dung">

            <h2>
                Khám phá StyleHub
            </h2>

            <p>
                Tìm kiếm và lựa chọn sản phẩm phù hợp
                với phong cách của bạn.
            </p>

            <p>
                <a
                    class="nut"
                    href="danh-sach.php"
                >
                    Khám phá sản phẩm
                </a>
            </p>

        </section>


        <section class="khoi-noi-dung">

            <h2>
                Danh mục
            </h2>

            <ul>

                <?php foreach ($danhMuc as $muc): ?>

                    <li>
                        <?= e($muc) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </section>

    </aside>

</main>

<script
    type="module"
    src="js/main.js"
></script>

<script
    type="module"
    src="js/trang-chu.js"
></script>

<script
    type="module"
    src="js/yeu-thich.js"
></script>

<?php require __DIR__ . '/inc/footer.php'; ?>