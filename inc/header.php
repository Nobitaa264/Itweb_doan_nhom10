<?php
/**
 * inc/header.php
 * Phần đầu HTML dùng chung cho toàn bộ website.
 */

use App\Services\GioHang;

$goc = $goc ?? './';
$tieuDe = $tieuDe ?? 'StyleHub - Nhóm 10';
$trang = $trang ?? '';

$gioHang = new GioHang();
$soMonGio = $gioHang->soMon();

$nguoiDung = nguoiDungHienTai();
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="StyleHub - Website thời trang Nhóm 10."
    >

    <title><?= e($tieuDe) ?></title>

    <!-- Mã CSRF dùng cho các yêu cầu fetch POST cùng nguồn. -->
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">

    <link
        rel="icon"
        href="data:,"
    >

    <link
        rel="stylesheet"
        href="<?= e($goc) ?>css/style.css"
    >
    <link rel="stylesheet" href="<?= e($goc) ?>css/dang-xuat.css">
    <link rel="stylesheet" href="<?= e($goc) ?>css/csp-no-inline.css">
<?php if (!empty($cssBoSung)): ?>
    <link rel="stylesheet" href="<?= e($goc . $cssBoSung) ?>">
<?php endif; ?>
</head>

<body class="<?= e($trang) ?>">
<header class="dau-trang">

    <div class="bao">

        <p class="thuong-hieu">
            StyleHub
        </p>

        <p class="khau-hieu">
            Thời trang cho phong cách riêng của bạn
        </p>

    </div>

</header>