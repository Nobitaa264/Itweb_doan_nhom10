<?php
/**
 * 500.php
 * Trang hiển thị khi máy chủ gặp lỗi nghiêm trọng.
 */

require_once __DIR__ . '/inc/config.php';

http_response_code(500);

$tieuDe = 'Lỗi máy chủ - StyleHub';
$trang = '500';
$goc = './';
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">

        <section class="khu-vuc-gioi-thieu">
            <h1>500 - Lỗi máy chủ</h1>

            <p>
                Máy chủ gặp sự cố khi xử lý yêu cầu.
                Vui lòng thử lại sau.
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