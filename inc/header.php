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

$nguoiDung = $_SESSION['nguoi_dung'] ?? null;
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

    <link
        rel="icon"
        href="data:,"
    >

    <link
        rel="stylesheet"
        href="<?= e($goc) ?>css/style.css"
    >
</head>

<body>

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