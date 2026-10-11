<?php
/**
 * 404.php
 * Trang hiển thị khi tài nguyên không được tìm thấy.
 */

require_once __DIR__ . '/inc/config.php';

http_response_code(404);

$tieuDe = 'Không tìm thấy trang - StyleHub';
$trang = '404';
$goc = './';
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">

        <section class="khu-vuc-gioi-thieu">
            <h1>404 - Không tìm thấy trang</h1>

            <p>
                Trang bạn yêu cầu không tồn tại hoặc đã được di chuyển.
            </p>

            <p>
                <a class="nut" href="<?= e($goc) ?>index.php">
                    Về trang chủ
                </a>
            </p>
        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>