<?php

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Giới thiệu - StyleHub';
$trang = 'gioi-thieu';
$goc = './';

$pageTitle = 'Giới thiệu - StyleHub';
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">

        <h1 class="tieu-de-trang">Giới thiệu Nhóm 10</h1>

        <section class="khu-vuc-gioi-thieu">
            <h2>Về website</h2>

            <div class="van-ban-dai">
                <p>
                    Nhóm 10 xây dựng website bán quần áo trong học phần
                    Thiết kế và Lập trình web.
                </p>

                <p>
                    Website hướng đến việc hỗ trợ người dùng xem sản phẩm
                    theo danh mục, tìm kiếm sản phẩm, xem chi tiết và
                    tương tác với các chức năng của website.
                </p>

                <p>
                    StyleHub được xây dựng với mục tiêu tạo ra một giao diện
                    đơn giản, dễ sử dụng và có khả năng hiển thị tốt trên
                    nhiều kích thước màn hình khác nhau.
                </p>
            </div>
        </section>

        <section class="khu-vuc-gioi-thieu">
            <h2>Chức năng chính</h2>

            <ul class="danh-sach-chuc-nang">
                <li>Xem danh sách sản phẩm</li>
                <li>Xem chi tiết sản phẩm</li>
                <li>Tìm kiếm sản phẩm</li>
                <li>Lọc theo danh mục</li>
                <li>Đăng ký và đăng nhập</li>
                <li>Thêm sản phẩm vào giỏ hàng</li>
                <li>Đặt hàng</li>
                <li>Xem lịch sử đơn hàng</li>
                <li>Quản trị sản phẩm</li>
                <li>Quản lý danh mục</li>
                <li>Quản lý đơn hàng</li>
            </ul>
        </section>

        <section class="khu-vuc-gioi-thieu">
            <h2>Thành viên thực hiện</h2>

            <ul class="danh-sach-thanh-vien">

                <li class="the-thanh-vien">
                    <h3>Nguyễn Đình Minh Đạt</h3>

                    <a
                        class="nut"
                        href="thanhvien/3120224028_dat/gioithieu.php"
                    >
                        Xem trang cá nhân
                    </a>
                </li>

                <li class="the-thanh-vien">
                    <h3>Nguyễn Hữu Tuấn</h3>

                    <a
                        class="nut"
                        href="thanhvien/3120224170_nguyenhuutuan/gioithieu.php"
                    >
                        Xem trang cá nhân
                    </a>
                </li>

                <li class="the-thanh-vien">
                    <h3>Phạm Huy Hoàng</h3>

                    <a
                        class="nut"
                        href="thanhvien/3120224062_phamhoang/gioithieu.php"
                    >
                        Xem trang cá nhân
                    </a>
                </li>

                <li class="the-thanh-vien">
                    <h3>Dương Quang Sơn</h3>

                    <a
                        class="nut"
                        href="thanhvien/3120224123_son/gioithieu.php"
                    >
                        Xem trang cá nhân
                    </a>
                </li>

            </ul>
        </section>

        <section class="khu-vuc-gioi-thieu">
            <h2>Liên hệ với chúng tôi</h2>
            <p>
                <a class="nut" href="lien-he.php">
                    Liên hệ với nhóm
                </a>
            </p>
        </section>

    </div>
</main>

<script type="module" src="js/main.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>