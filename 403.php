<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDe = '403 - Cấm truy cập';
$trang = '403';
$goc = './';

http_response_code(403);
?>
<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <section class="khu-vuc" style="text-align: center; padding: 50px 0;">
            <h1 style="font-size: 72px; margin-bottom: 10px;">403</h1>
            <h2>Trang bị cấm truy cập</h2>
            <p>Bạn không có quyền truy cập vào khu vực này.</p>
            <p style="margin-top: 20px;">
                <a class="nut" href="index.php">Quay lại trang chủ</a>
            </p>
        </section>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>