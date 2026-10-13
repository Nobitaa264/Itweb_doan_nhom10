
<?php
/*
File: gioithieu.php
Sinh viên: Nguyễn Hữu Tuân
MSSV: 3120224170

Chức năng:
- Giới thiệu thông tin cá nhân
- Quay lại Trang chủ và Trang Giới thiệu
- Hiển thị ngày giờ server
- Đếm lượt xem bằng file TXT
- Đồng hồ và sao chép email bằng JavaScript
- Hiển thị dự án và kỹ năng
- Hiển thị thời khóa biểu tuần
- Sổ lưu bút lưu bằng file JSONL
*/

session_start();

date_default_timezone_set("Asia/Ho_Chi_Minh");


/* =========================
   THÔNG TIN CÁ NHÂN
========================= */

$hoTen = "Nguyễn Hữu Tuân";
$mssv = "3120224170";
$lop = "24CNTT2";
$nganh = "Công nghệ thông tin";
$khoa = "Toán - Tin";
$truong = "Đại học Sư phạm - Đại học Đà Nẵng";
$email = "3120224170@udn.edu.vn";


/* =========================
   HÀM BẢO MẬT HIỂN THỊ
========================= */

function e($value)
{
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================
   BỘ ĐẾM LƯỢT XEM
========================= */

$storageDir = __DIR__ . "/storage";

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$viewFile = $storageDir . "/3120224170_view.txt";

$luotXem = 0;

$fp = fopen($viewFile, "c+");

if ($fp !== false) {

    if (flock($fp, LOCK_EX)) {

        rewind($fp);

        $luotXem = (int)stream_get_contents($fp);

        if (!isset($_SESSION["da_xem"])) {

            $luotXem++;

            rewind($fp);

            ftruncate($fp, 0);

            fwrite($fp, (string)$luotXem);

            fflush($fp);

            $_SESSION["da_xem"] = true;
        }

        flock($fp, LOCK_UN);
    }

    fclose($fp);
}


/* =========================
   XỬ LÝ SỔ LƯU BÚT
========================= */

$luuButFile = $storageDir . "/3120224170_luubut.jsonl";

$thongBao = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ten = trim((string)($_POST["ten"] ?? ""));

    $loiNhan = trim((string)($_POST["loiNhan"] ?? ""));

    if ($ten === "" || $loiNhan === "") {

        $thongBao = "Vui lòng nhập đầy đủ thông tin.";

    } elseif (
        mb_strlen($ten, "UTF-8") > 100 ||
        mb_strlen($loiNhan, "UTF-8") > 1000
    ) {

        $thongBao = "Tên hoặc lời nhắn quá dài.";

    } else {

        $duLieu = [
            "ten" => $ten,
            "loiNhan" => $loiNhan,
            "thoiGian" => date("d-m-Y H:i:s")
        ];

        $json = json_encode(
            $duLieu,
            JSON_UNESCAPED_UNICODE
        );

        $ketQua = file_put_contents(
            $luuButFile,
            $json . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($ketQua !== false) {

            // PRG: Tránh gửi lại form khi F5
            header("Location: gioithieu.php#so-luu-but");
            exit;

        } else {

            $thongBao = "Không thể lưu lời nhắn.";
        }
    }
}


/* =========================
   ĐỌC 5 LỜI NHẮN MỚI NHẤT
========================= */

$danhSachLoiNhan = [];

if (file_exists($luuButFile)) {

    $dong = file(
        $luuButFile,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    if ($dong !== false) {

        foreach (array_reverse($dong) as $item) {

            $duLieu = json_decode($item, true);

            if (
                is_array($duLieu) &&
                isset(
                    $duLieu["ten"],
                    $duLieu["loiNhan"],
                    $duLieu["thoiGian"]
                )
            ) {

                $danhSachLoiNhan[] = $duLieu;
            }

            if (count($danhSachLoiNhan) >= 5) {
                break;
            }
        }
    }
}


/* =========================
   THỜI KHÓA BIỂU TUẦN
========================= */

// Thời khóa biểu mẫu, có thể sửa theo lịch thực tế.

$thuTrongTuan = [
    "Thứ 2",
    "Thứ 3",
    "Thứ 4",
    "Thứ 5",
    "Thứ 6",
    "Thứ 7",
    "Chủ nhật"
];

$thoiKhoaBieu = [

    "Sáng" => [
        "Học",
        "Học",
        "Học",
        "Học",
        "Học",
        "Tự học",
        "Nghỉ"
    ],

    "Chiều" => [
        "Tự học",
        "Học",
        "Tự học",
        "Học",
        "Tự học",
        "Làm bài tập",
        "Nghỉ"
    ],

    "Tối" => [
        "Tự học",
        "Làm bài tập",
        "Tự học",
        "Làm bài nhóm",
        "Nghỉ",
        "Giải trí",
        "Chuẩn bị tuần mới"
    ]
];

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Giới thiệu cá nhân - <?= e($hoTen) ?>
    </title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* Hai nút điều hướng */

        .navigation-back {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .navigation-back a {
            display: inline-block;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .navigation-back a:hover {
            opacity: 0.85;
        }

        .btn-home {
            background-color: #007bff;
        }

        .btn-intro {
            background-color: #6c757d;
        }


        /* Bảng thời khóa biểu */

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        .schedule-table {
            width: 100%;
            min-width: 750px;
            border-collapse: collapse;
            text-align: center;
        }

        .schedule-table caption {
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        .schedule-table thead {
            background-color: #007bff;
            color: white;
        }

        .schedule-table tbody tr:nth-child(even) {
            background-color: #f2f6fc;
        }

        .schedule-table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .schedule-table tbody th {
            font-weight: bold;
        }


        /* Hiển thị lời nhắn */

        .guestbook-message {
            margin: 15px 0;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .guestbook-message small {
            color: #666;
        }

        .error-message {
            color: red;
        }

    </style>

</head>

<body>

<main class="container main-content">


<!-- =========================
     NÚT ĐIỀU HƯỚNG
========================= -->

<nav class="navigation-back"
     aria-label="Điều hướng trang">

    <a href="../../index.php"
       class="btn-home">
        &larr; Trang chủ
    </a>

    <a href="../../gioi-thieu.php"
       class="btn-intro">
        &larr; Trang Giới thiệu
    </a>

</nav>


<!-- =========================
     GIỚI THIỆU BẢN THÂN
========================= -->

<section class="content-section">

    <h2 class="section-title">
        Giới thiệu bản thân
    </h2>

    <div class="profile">

        <div class="profile-image">

            <img src="image.jpg"
                 alt="Ảnh Nguyễn Hữu Tuân"
                 width="150"
                 height="200">

        </div>

        <div class="profile-info">

            <p>
                <strong>Họ và tên:</strong>
                <?= e($hoTen) ?>
            </p>

            <p>
                <strong>MSSV:</strong>
                <?= e($mssv) ?>
            </p>

            <p>
                <strong>Lớp:</strong>
                <?= e($lop) ?>
            </p>

            <p>
                <strong>Ngành:</strong>
                <?= e($nganh) ?>
            </p>

            <p>
                <strong>Khoa:</strong>
                <?= e($khoa) ?>
            </p>

            <p>
                <strong>Trường:</strong>
                <?= e($truong) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= e($email) ?>
            </p>

        </div>

    </div>

    <p class="description">
        Xin chào, tôi là Nguyễn Hữu Tuân,
        hiện đang là sinh viên ngành Công nghệ thông tin
        thuộc Khoa Toán - Tin, Trường Đại học Sư phạm
        - Đại học Đà Nẵng.
    </p>

    <p class="description">
        Tôi yêu thích lập trình web, phát triển phần mềm
        và tìm hiểu những công nghệ mới.
        Trong quá trình học tập tôi luôn cố gắng
        nâng cao kiến thức về HTML, CSS, PHP, MySQL.
    </p>

    <p class="description">
        Mục tiêu của tôi là trở thành một lập trình viên
        có chuyên môn tốt, khả năng giải quyết vấn đề
        và tạo ra những sản phẩm công nghệ hữu ích.
    </p>

</section>


<!-- =========================
     TƯƠNG TÁC CÁ NHÂN
========================= -->

<section class="content-section">

    <h2 class="section-title">
        Tương tác cá nhân
    </h2>

    <p>
        Số lượt xem trang:
        <strong><?= e($luotXem) ?></strong>
    </p>

    <div class="clock-box">

        🕒 Thời gian hiện tại:

        <span id="clock">
            00:00:00
        </span>

    </div>

    <p>
        <strong>Ngày giờ trên server:</strong>
        <?= date("d/m/Y H:i:s") ?>
    </p>

    <br>

    <button id="btnCopyEmail"
            type="button">
        📋 Sao chép Email
    </button>

    <p id="message"></p>

</section>


<!-- =========================
     DỰ ÁN VÀ SỞ THÍCH
========================= -->

<section class="content-section">

    <h2 class="section-title">
        Dự án và sở thích
    </h2>

    <p>
        Trong môn Thiết kế và Lập trình Web,
        tôi cùng nhóm thực hiện dự án xây dựng
        website bán hàng quần áo.
    </p>

    <p>
        Ngoài học tập, tôi thích nghe nhạc,
        xem phim, chơi game và tìm hiểu
        các công nghệ mới.
    </p>

    <h3 class="sub-title">
        Kỹ năng
    </h3>

    <ul class="skills-list">

        <li>HTML</li>
        <li>CSS</li>
        <li>PHP</li>
        <li>MySQL</li>
        <li>Git/GitHub</li>
        <li>Làm việc nhóm</li>

    </ul>

</section>


<!-- =========================
     THỜI KHÓA BIỂU TUẦN
========================= -->

<section class="content-section">

    <h2 class="section-title">
        Thời khóa biểu tuần
    </h2>

    <div class="table-container">

        <table class="schedule-table">

            <caption>
                Thời khóa biểu tuần của
                <?= e($hoTen) ?>
            </caption>

            <thead>

                <tr>

                    <th scope="col">
                        Thời gian
                    </th>

                    <?php foreach ($thuTrongTuan as $thu): ?>

                        <th scope="col">
                            <?= e($thu) ?>
                        </th>

                    <?php endforeach; ?>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($thoiKhoaBieu as $buoi => $lichHoc): ?>

                    <tr>

                        <th scope="row">
                            <?= e($buoi) ?>
                        </th>

                        <?php foreach ($lichHoc as $hoatDong): ?>

                            <td>
                                <?= e($hoatDong) ?>
                            </td>

                        <?php endforeach; ?>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</section>


<!-- =========================
     SỔ LƯU BÚT
========================= -->

<section class="content-section"
         id="so-luu-but">

    <h2 class="section-title">
        Sổ lưu bút
    </h2>

    <form method="post"
          action="gioithieu.php">

        <label for="ten">
            Họ tên:
        </label>

        <input type="text"
               id="ten"
               name="ten"
               maxlength="100"
               required>

        <label for="loiNhan">
            Lời nhắn:
        </label>

        <textarea id="loiNhan"
                  name="loiNhan"
                  maxlength="1000"
                  required></textarea>

        <button type="submit">
            Gửi lời nhắn
        </button>

    </form>

    <?php if ($thongBao !== ""): ?>

        <p class="error-message">
            <?= e($thongBao) ?>
        </p>

    <?php endif; ?>


    <h3>
        5 lời nhắn mới nhất
    </h3>

    <?php if (empty($danhSachLoiNhan)): ?>

        <p>
            Chưa có lời nhắn nào.
        </p>

    <?php else: ?>

        <?php foreach ($danhSachLoiNhan as $loiNhan): ?>

            <div class="guestbook-message">

                <p>
                    <strong>
                        <?= e($loiNhan["ten"]) ?>
                    </strong>
                    :
                    <?= nl2br(e($loiNhan["loiNhan"])) ?>
                </p>

                <small>
                    <?= e($loiNhan["thoiGian"]) ?>
                </small>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>


</main>

<script src="js/canhan.js"></script>

</body>
</html>
