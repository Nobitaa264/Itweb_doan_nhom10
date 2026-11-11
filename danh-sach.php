<?php
/**
 * danh-sach.php
 * Hiển thị danh sách sản phẩm bằng PHP.
 * Tìm kiếm, lọc và sắp xếp được xử lý phía máy chủ.
 */

require_once __DIR__ . '/inc/config.php';

// Trang yêu thích giữ nguyên: cần toàn bộ sản phẩm để JS đọc localStorage.
$laTrangYeuThich = chuoi($_GET, 'yeuthich') === '1';

$tieuDe = $laTrangYeuThich ? 'Sản phẩm yêu thích - StyleHub' : 'Danh sách sản phẩm - StyleHub';
$trang = 'danh-sach';
$goc = './';
$cssBoSung = 'css/danh-sach-bo-sung.css';
$khoSanPham = new \App\Data\KhoSanPham();

// chuoi() chống Array Injection như ?q[]=abc, ?sx[]=abc.
$tuKhoa = chuoi($_GET, 'q', 200);
$danhMuc = chuoi($_GET, 'dm', 100) ?: 'tat-ca';
$sapXep = chuoi($_GET, 'sx', 30) ?: 'mac-dinh';
$danhMucChoPhep = $khoSanPham->danhMuc();
$sapXepChoPhep = ['mac-dinh', 'gia-tang', 'gia-giam', 'ten-a-z', 'ten-z-a'];

if ($danhMuc !== 'tat-ca' && !in_array($danhMuc, $danhMucChoPhep, true)) {
    $danhMuc = 'tat-ca';
}
if (!in_array($sapXep, $sapXepChoPhep, true)) {
    $sapXep = 'mac-dinh';
}

// Giá tối đa tùy chọn. Giá âm, mảng và chuỗi sai định dạng đều không gây lỗi 500.
$giaToiDaNhap = chuoi($_GET, 'gia_max', 20);
$giaToiDa = null;
$loiGia = '';
if ($giaToiDaNhap !== '') {
    $giaDuocKiemTra = filter_var(
        $giaToiDaNhap,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0]]
    );
    if ($giaDuocKiemTra === false) {
        $loiGia = 'Giá tối đa phải là số nguyên không âm.';
    } else {
        $giaToiDa = $giaDuocKiemTra;
    }
}

// Phân trang phía CSDL: 10 sản phẩm/trang.
$soMoiTrang = 10;
$thamSoTrang = $_GET['trang'] ?? '1';
$trangHienTai = is_string($thamSoTrang)
    ? filter_var($thamSoTrang, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
$trangHienTai = $trangHienTai === false ? 1 : $trangHienTai;

if ($laTrangYeuThich) {
    $danhSachSanPham = $khoSanPham->timKiem();
    $tongSanPham = count($danhSachSanPham);
    $tongTrang = 1;
    $trangHienTai = 1;
} else {
    $tongSanPham = $khoSanPham->demTimKiem($tuKhoa, $danhMuc, $giaToiDa);
    $tongTrang = max(1, (int) ceil($tongSanPham / $soMoiTrang));
    $trangHienTai = min($trangHienTai, $tongTrang);
    $danhSachSanPham = $khoSanPham->timKiem(
        $tuKhoa, $danhMuc, $sapXep, $giaToiDa, $trangHienTai, $soMoiTrang
    );
}

// Giữ nguyên mọi bộ lọc khi nhấn chuyển trang.
$duongDanTrang = static function (int $soTrang) use (
    $tuKhoa, $danhMuc, $sapXep, $giaToiDaNhap
): string {
    $params = ['q' => $tuKhoa, 'dm' => $danhMuc, 'sx' => $sapXep];
    if ($giaToiDaNhap !== '') {
        $params['gia_max'] = $giaToiDaNhap;
    }
    $params['trang'] = $soTrang;
    return 'danh-sach.php?' . http_build_query($params);
};
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">

    <div class="bao">

        <h1 class="tieu-de-trang">
            <?= $laTrangYeuThich ? '♥ Sản phẩm yêu thích của bạn' : 'Danh sách sản phẩm' ?>
        </h1>

        <?php if (!$laTrangYeuThich): ?>
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

                        <label for="gia-toi-da">Giá tối đa (VNĐ)</label>
                        <input
                            type="number"
                            id="gia-toi-da"
                            name="gia_max"
                            min="0"
                            step="1"
                            value="<?= e($giaToiDaNhap) ?>"
                            placeholder="Ví dụ: 500000"
                            aria-describedby="goi-y-gia"
                        >
                        <small id="goi-y-gia">Để trống nếu không giới hạn giá.</small>
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
            <?php if ($loiGia !== ''): ?>
                <p class="th6-loc-loi" role="alert"><?= e($loiGia) ?></p>
            <?php endif; ?>

        </section>
        <?php endif; ?>

        <section
            class="khu-vuc"
            aria-labelledby="tieu-de-ket-qua"
        >

            <div class="dau-ket-qua">

                <h2 id="tieu-de-ket-qua">
                    <?= $laTrangYeuThich ? 'Danh sách yêu thích' : 'Sản phẩm' ?>
                </h2>

                <p
                    id="so-luong-ket-qua"
                    class="so-luong-ket-qua"
                    aria-live="polite"
                >
                    <?= $tongSanPham ?>
                    sản phẩm<?= !$laTrangYeuThich && $tongSanPham > 0 ? ' · Trang ' . $trangHienTai . '/' . $tongTrang : '' ?>
                </p>

            </div>

            <p
                id="thong-bao-rong-yeu-thich"
                class="trang-thai-danh-sach"
                hidden
                role="status"
            >
                Bạn chưa có sản phẩm nào trong danh sách yêu thích.
            </p>

            <!-- Khung kết quả luôn tồn tại, kể cả khi tìm kiếm ban đầu không ra sản phẩm. -->
            <p
                id="thong-bao-khong-co-san-pham"
                class="trang-thai-danh-sach"
                role="status"
                <?= $danhSachSanPham !== [] ? 'hidden' : '' ?>
            >
                Không tìm thấy sản phẩm phù hợp với điều kiện tìm kiếm.
            </p>

            <p id="trang-thai-fetch" class="trang-thai-danh-sach" role="status" aria-live="polite" hidden></p>

            <div id="danh-sach-san-pham" class="luoi-danh-sach">

                    <?php foreach (
                        $danhSachSanPham
                        as $sanPham
                    ): ?>

                        <article class="the-san-pham" data-id-san-pham="<?= e($sanPham->id) ?>">

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

                                <div class="nhom-hanh-dong-san-pham">
                                    <a
                                        class="nut"
                                        href="chi-tiet.php?id=<?= urlencode($sanPham->id) ?>"
                                    >
                                        Xem chi tiết
                                    </a>

                                    <button
                                        type="button"
                                        class="nut nut-yeu-thich"
                                        data-id-san-pham="<?= e($sanPham->id) ?>"
                                    >
                                        ♡ Yêu thích
                                    </button>
                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>



            <?php if (!$laTrangYeuThich && $tongTrang > 1): ?>
                <nav class="th6-phan-trang-danh-sach" aria-label="Phân trang sản phẩm">
                    <?php if ($trangHienTai > 1): ?>
                        <a href="<?= e($duongDanTrang($trangHienTai - 1)) ?>">← Trước</a>
                    <?php else: ?>
                        <span class="th6-trang-vo-hieu">← Trước</span>
                    <?php endif; ?>

                    <?php for ($soTrang = 1; $soTrang <= $tongTrang; $soTrang++): ?>
                        <?php if ($soTrang === $trangHienTai): ?>
                            <span class="th6-trang-hien-tai" aria-current="page"><?= $soTrang ?></span>
                        <?php else: ?>
                            <a href="<?= e($duongDanTrang($soTrang)) ?>"><?= $soTrang ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($trangHienTai < $tongTrang): ?>
                        <a href="<?= e($duongDanTrang($trangHienTai + 1)) ?>">Tiếp →</a>
                    <?php else: ?>
                        <span class="th6-trang-vo-hieu">Tiếp →</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

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

<?php if ($laTrangYeuThich): ?>
<script type="module" src="js/trang-yeu-thich.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/inc/footer.php'; ?>