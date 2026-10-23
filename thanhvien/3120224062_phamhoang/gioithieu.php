
<?php
/*
 * Tệp: gioithieu.php
 * Chức năng 1: Đếm lượt xem, mỗi session chỉ tính một lần.
 * Chức năng 2: Lọc danh sách kỹ năng bằng phương thức GET.
 * Chức năng 3: Hiển thị thông tin cá nhân, thời khóa biểu và sở thích.
 * Cách kiểm tra: tải lại trang, lọc kỹ năng và kiểm tra tham số GET.
 */

session_start();

$goc = '../../';
$cssCaNhan = 'style.css';
$jsCaNhan = 'js/canhan.js';

/**
 * Chuyển dữ liệu thành chuỗi an toàn khi xuất HTML.
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ===== CHỨC NĂNG 1: ĐẾM LƯỢT XEM ===== */

$fileLuotXem = __DIR__ . '/storage/3120224062_views.txt';
$thuMucStorage = dirname($fileLuotXem);

$luotXem = 0;

if (!is_dir($thuMucStorage)) {
    mkdir($thuMucStorage, 0755, true);
}

if (is_file($fileLuotXem)) {
    $noiDung = trim((string) file_get_contents($fileLuotXem));

    if (ctype_digit($noiDung)) {
        $luotXem = (int) $noiDung;
    }
}

if (!isset($_SESSION['da_tinh_luot_xem'])) {
    $luotXem++;

    $ghiThanhCong = file_put_contents(
        $fileLuotXem,
        (string) $luotXem,
        LOCK_EX
    );

    if ($ghiThanhCong !== false) {
        $_SESSION['da_tinh_luot_xem'] = true;
    }
}

/* ===== CHỨC NĂNG 2: DANH SÁCH VÀ LỌC KỸ NĂNG BẰNG GET ===== */

$kyNang = [
    'HTML',
    'CSS',
    'PHP',
    'MySQL',
    'Git và GitHub',
    'Làm việc nhóm'
];

$kyNangChon = '';

if (isset($_GET['ky_nang']) && is_string($_GET['ky_nang'])) {
    $kyNangChon = trim($_GET['ky_nang']);
}

/*
 * Chỉ chấp nhận kỹ năng có trong danh sách cho phép.
 * Giá trị rỗng có nghĩa là hiển thị tất cả kỹ năng.
 */
$boLocKhongHopLe = (
    $kyNangChon !== ''
    && !in_array($kyNangChon, $kyNang, true)
);

if ($boLocKhongHopLe) {
    $kyNangChon = '';
}

if ($kyNangChon !== '') {
    $kyNangHienThi = array_values(
        array_filter(
            $kyNang,
            static function ($item) use ($kyNangChon) {
                return $item === $kyNangChon;
            }
        )
    );
} else {
    $kyNangHienThi = $kyNang;
}

/* ===== THÔNG TIN TRANG ===== */

$tieuDe = 'Giới thiệu Phạm Huy Hoàng';

require __DIR__ . '/../../includes/header.php';
?>

<article>

    <h1>Giới thiệu Phạm Huy Hoàng</h1>

    <section aria-labelledby="gioi-thieu-ban-than">

        <h2 id="gioi-thieu-ban-than">
            Giới thiệu bản thân
        </h2>

        <img
            src="image.jpg"
            alt="Ảnh chân dung Phạm Huy Hoàng"
        >

        <p>
            <strong>Họ và tên:</strong>
            Phạm Huy Hoàng
        </p>

        <p>
            <strong>Lớp:</strong>
            24CNTT2
        </p>

        <p>
            <strong>Ngành:</strong>
            Công nghệ thông tin
        </p>

        <p>
            Tôi là sinh viên lớp 24CNTT2.
            Tôi đang học tập và tìm hiểu về lập trình web,
            cơ sở dữ liệu và các công nghệ phần mềm.
        </p>

        <h3>Kỹ năng</h3>

        <p>
            Bạn có thể chọn một kỹ năng để lọc danh sách.
        </p>

        <form method="get" action="">

            <label for="ky_nang">
                Lọc theo kỹ năng:
            </label>

            <select id="ky_nang" name="ky_nang">

                <option
                    value=""
                    <?= $kyNangChon === '' ? 'selected' : '' ?>
                >
                    Tất cả kỹ năng
                </option>

                <?php foreach ($kyNang as $kyNangItem): ?>

                    <option
                        value="<?= e($kyNangItem) ?>"
                        <?= $kyNangChon === $kyNangItem ? 'selected' : '' ?>
                    >
                        <?= e($kyNangItem) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit">
                Lọc kỹ năng
            </button>

            <a href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">
                Xem tất cả
            </a>

        </form>

        <?php if ($boLocKhongHopLe): ?>

            <p role="alert">
                Kỹ năng được chọn không hợp lệ.
                Vui lòng chọn lại.
            </p>

        <?php endif; ?>

        <?php if (!empty($kyNangHienThi)): ?>

            <p>
                <?php if ($kyNangChon !== ''): ?>
                    Kết quả lọc cho kỹ năng:
                    <strong><?= e($kyNangChon) ?></strong>
                <?php else: ?>
                    Danh sách tất cả kỹ năng:
                <?php endif; ?>
            </p>

            <ul>
                <?php foreach ($kyNangHienThi as $kyNangItem): ?>
                    <li><?= e($kyNangItem) ?></li>
                <?php endforeach; ?>
            </ul>

        <?php else: ?>

            <p>Không tìm thấy kỹ năng phù hợp.</p>

        <?php endif; ?>

    </section>

    <section aria-labelledby="luot-xem-title">

        <h2 id="luot-xem-title">
            Lượt xem trang
        </h2>

        <p>
            Trang đã được xem
            <strong><?= e($luotXem) ?></strong>
            lượt.
        </p>

        <p>
            Mỗi phiên truy cập chỉ được tính một lượt xem.
        </p>

    </section>

    <section aria-labelledby="countdown-title">

        <h2 id="countdown-title">
            Đếm ngược đến ngày thi cuối kỳ
        </h2>

        <p id="countdown" aria-live="polite">
            Đang tính thời gian...
        </p>

    </section>

    <section aria-labelledby="du-an-title">

        <h2 id="du-an-title">
            Dự án và sở thích
        </h2>

        <p>
            Trong quá trình học tập, tôi đang tham gia các dự án
            phát triển website cùng các thành viên trong nhóm.
        </p>

        <p>
            Tôi quan tâm đến việc xây dựng website bán hàng,
            thiết kế giao diện và kết nối website với cơ sở dữ liệu MySQL.
        </p>

        <p>
            Ngoài học tập, tôi thích nghe nhạc, tìm hiểu công nghệ mới,
            sử dụng máy tính và dành thời gian cho bạn bè.
        </p>

    </section>

    <section aria-labelledby="thong-tin-title">

        <h2 id="thong-tin-title">
            Thông tin cá nhân
        </h2>

        <details>
            <summary>Kỹ năng</summary>

            <p>
                HTML, CSS, PHP, MySQL, Git và GitHub,
                Làm việc nhóm.
            </p>
        </details>

        <details>
            <summary>Học vấn</summary>

            <p>
                Sinh viên lớp 24CNTT2,
                ngành Công nghệ thông tin.
            </p>
        </details>

        <details>
            <summary>Sở thích</summary>

            <p>
                Nghe nhạc, tìm hiểu công nghệ,
                sử dụng máy tính và giao lưu với bạn bè.
            </p>
        </details>

    </section>

    <section aria-labelledby="thoi-khoa-bieu-title">

        <h2 id="thoi-khoa-bieu-title">
            Thời khóa biểu tuần
        </h2>

        <div class="bang-cuon">

            <table>

                <caption>
                    Thời khóa biểu tuần của Phạm Huy Hoàng
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Thời gian</th>
                        <th scope="col">Thứ 2</th>
                        <th scope="col">Thứ 3</th>
                        <th scope="col">Thứ 4</th>
                        <th scope="col">Thứ 5</th>
                        <th scope="col">Thứ 6</th>
                        <th scope="col">Thứ 7</th>
                        <th scope="col">Chủ nhật</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <th scope="row">Sáng</th>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Nghỉ</td>
                    </tr>

                    <tr>
                        <th scope="row">Chiều</th>
                        <td>Tự học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Học</td>
                        <td>Tự học</td>
                        <td>Làm bài tập</td>
                        <td>Nghỉ</td>
                    </tr>

                    <tr>
                        <th scope="row">Tối</th>
                        <td>Tự học</td>
                        <td>Làm bài tập</td>
                        <td>Tự học</td>
                        <td>Làm bài nhóm</td>
                        <td>Nghỉ</td>
                        <td>Giải trí</td>
                        <td>Chuẩn bị tuần mới</td>
                    </tr>

                </tbody>

            </table>

        </div>

    </section>

    <section aria-labelledby="lien-ket-title">

        <h2 id="lien-ket-title">
            Liên kết
        </h2>

        <p>
            <a href="<?= e($goc) ?>gioi-thieu.php">
                Quay về trang giới thiệu nhóm
            </a>
        </p>

        <p>
            <a href="<?= e($goc) ?>index.php">
                Quay về trang chủ
            </a>
        </p>

    </section>

</article>

<?php
require __DIR__ . '/../../includes/footer.php';
?>
