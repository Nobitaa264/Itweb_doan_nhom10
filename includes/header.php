<?php
/*
 * Tệp: header.php
 * Chức năng: Header và menu dùng chung cho các trang thành viên.
 * File được gọi bằng require từ trang PHP thành viên.
 * Cách kiểm tra: mở trang cá nhân và kiểm tra header, menu.
 */
?>

<!doctype html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= isset($tieuDe) ? e($tieuDe) : 'StyleHub' ?>
    </title>

    <link rel="icon" href="data:,">

    <link
        rel="stylesheet"
        href="<?= e($goc . 'css/style.css') ?>"
    >

    <?php if (!empty($cssCaNhan)): ?>

        <link
            rel="stylesheet"
            href="<?= e($cssCaNhan) ?>"
        >

    <?php endif; ?>

</head>

<body>

<div class="trang">

    <header class="dau-trang">

        <div class="bao">

            <p class="thuong-hieu">
                StyleHub
            </p>

            <p class="khau-hieu">
                Thời trang hiện đại
            </p>

        </div>

    </header>


    <nav aria-label="Điều hướng chính">

        <div class="bao">

            <ul>

                <li>
                    <a href="<?= e($goc) ?>index.php">
                        Trang chủ
                    </a>
                </li>

                <li>
                    <a href="<?= e($goc) ?>danh-sach.html">
                        Sản phẩm
                    </a>
                </li>

                <li>
                    <a href="<?= e($goc) ?>gioi-thieu.html">
                        Giới thiệu
                    </a>
                </li>

                <li>
                    <a href="<?= e($goc) ?>lien-he.html">
                        Liên hệ
                    </a>
                </li>

            </ul>

        </div>

    </nav>


    <main class="noi-dung">

        <div class="bao">