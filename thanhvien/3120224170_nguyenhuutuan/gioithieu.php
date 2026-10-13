<?php

/*
File: gioithieu.php
Chức năng:
- Trang giới thiệu cá nhân Nguyễn Hữu Tuân
- Bộ đếm lượt xem bằng file txt
- Sổ lưu bút bằng file JSONL
*/


session_start();



/* =========================
   HÀM BẢO MẬT HIỂN THỊ
========================= */

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}




/* =========================
   BỘ ĐẾM LƯỢT XEM
========================= */


$viewFile = __DIR__ . "/storage/3120224170_view.txt";


if (!file_exists($viewFile)) {

    file_put_contents($viewFile, "0");

}



if (!isset($_SESSION["da_xem"])) {


    $luotXem = (int)file_get_contents($viewFile);


    $luotXem++;


    file_put_contents(
        $viewFile,
        $luotXem
    );


    $_SESSION["da_xem"] = true;

}



$luotXem = (int)file_get_contents($viewFile);







/* =========================
   XỬ LÝ SỔ LƯU BÚT
========================= */


$luuButFile = __DIR__ . "/storage/3120224170_luubut.jsonl";


$thongBao = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $ten = trim($_POST["ten"] ?? "");


    $loiNhan = trim($_POST["loiNhan"] ?? "");




    if ($ten == "" || $loiNhan == "") {


        $thongBao = "Vui lòng nhập đầy đủ thông tin.";


    } else {


        $duLieu = [

            "ten" => $ten,

            "loiNhan" => $loiNhan,

            "thoiGian" => date("d-m-Y H:i:s")

        ];



        file_put_contents(

            $luuButFile,

            json_encode(
                $duLieu,
                JSON_UNESCAPED_UNICODE
            )
            . PHP_EOL,

            FILE_APPEND

        );



        header("Location: gioithieu.php");

        exit;

    }

}






/* =========================
   ĐỌC LỜI NHẮN
========================= */


$danhSachLoiNhan = [];



if (file_exists($luuButFile)) {


    $dong = file($luuButFile);



    foreach(array_reverse($dong) as $item){


        $duLieu = json_decode($item,true);



        if(is_array($duLieu)){


            $danhSachLoiNhan[] = $duLieu;


        }



        if(count($danhSachLoiNhan)>=5){

            break;

        }

    }

}



?>



<!DOCTYPE html>

<html lang="vi">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Giới thiệu cá nhân - Nguyễn Hữu Tuân</title>



<link rel="stylesheet" href="css/style.css">


</head>




<body>



<main class="container main-content">






<!-- GIỚI THIỆU -->


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
Nguyễn Hữu Tuân
</p>


<p>
<strong>MSSV:</strong>
3120224170
</p>


<p>
<strong>Lớp:</strong>
24CNTT2
</p>


<p>
<strong>Khoa:</strong>
Toán - Tin
</p>


<p>
<strong>Trường:</strong>
Đại học Sư phạm - Đại học Đà Nẵng
</p>


<p>
<strong>Email:</strong>
3120224170@udn.edu.vn
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
Trong quá trình học tập tôi luôn cố gắng nâng cao
kiến thức về HTML, CSS, PHP, MySQL.

</p>




<p class="description">

Mục tiêu của tôi là trở thành một lập trình viên
có chuyên môn tốt, khả năng giải quyết vấn đề
và tạo ra những sản phẩm công nghệ hữu ích.

</p>



</section>








<!-- TƯƠNG TÁC -->


<section class="content-section">


<h2 class="section-title">
Tương tác cá nhân
</h2>



<p>

Số lượt xem trang:

<strong>
<?= e($luotXem) ?>
</strong>

</p>



<div class="clock-box">

🕒 Thời gian hiện tại:

<span id="clock">
00:00:00
</span>

</div>



<br>


<button id="btnCopyEmail">

📋 Sao chép Email

</button>


<p id="message"></p>



</section>









<!-- DỰ ÁN -->


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









<!-- SỔ LƯU BÚT -->


<section class="content-section">


<h2 class="section-title">
Sổ lưu bút
</h2>




<form method="post">


<label>
Họ tên:
</label>


<input type="text" name="ten">





<label>
Lời nhắn:
</label>



<textarea name="loiNhan"></textarea>




<button type="submit">

Gửi lời nhắn

</button>


</form>




<p>

<?= e($thongBao) ?>

</p>






<h3>
5 lời nhắn mới nhất
</h3>





<?php foreach($danhSachLoiNhan as $loiNhan): ?>



<p>


<strong>
<?= e($loiNhan["ten"]) ?>
</strong>


:

<?= e($loiNhan["loiNhan"]) ?>


<br>


<small>

<?= e($loiNhan["thoiGian"]) ?>

</small>


</p>



<?php endforeach; ?>





</section>






</main>




<script src="js/canhan.js"></script>



</body>

</html>
