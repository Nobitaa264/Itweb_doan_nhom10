<?php

declare(strict_types=1);

/**
 * quan-tri.php
 *
 * Trang quản trị:
 * - Chỉ người đã đăng nhập mới được truy cập.
 * - Lấy dữ liệu liên hệ thông qua KhoLienHe.
 * - Hiển thị liên hệ mới nhất trước.
 * - Hiển thị đầy đủ thông tin liên hệ và ảnh đính kèm.
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/bao-ve.php';

use App\Data\KhoLienHe;

$tieuDe = 'Quản trị - StyleHub';
$trang = 'quan-tri';
$goc = './';

$khoLienHe = new KhoLienHe();

$danhSachLienHe = $khoLienHe->tatCa();

$nguoiDung = $_SESSION['nguoi_dung'] ?? null;

$flash = null;

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);
}
?>

<?php require __DIR__ . '/inc/header.php'; ?>

<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">

        <h1 class="tieu-de-trang">
            Quản trị
        </h1>

        <p>
            Xin chào,
            <strong>
                <?= e($nguoiDung['ten'] ?? '') ?>
            </strong>.
        </p>

        <?php if ($flash !== null): ?>

            <p
                class="thong-bao"
                role="status"
                aria-live="polite"
            >
                <?= e($flash['noiDung'] ?? '') ?>
            </p>

        <?php endif; ?>

        <section class="khu-vuc">

            <h2>
                Danh sách liên hệ
            </h2>

            <?php if ($danhSachLienHe === []): ?>

                <p>
                    Chưa có liên hệ nào.
                </p>

            <?php else: ?>

                <?php foreach (
                    $danhSachLienHe as $lienHe
                ): ?>

                    <article class="khoi-noi-dung">

                        <h3>
                            <?= e(
                                $lienHe['hoTen']
                                ?? 'Không có tên'
                            ) ?>
                        </h3>

                        <p>
                            <strong>
                                Số điện thoại:
                            </strong>

                            <?= e(
                                $lienHe['soDienThoai']
                                ?? ''
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Email:
                            </strong>

                            <?= e(
                                $lienHe['email']
                                ?? ''
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Xe quan tâm:
                            </strong>

                            <?= e(
                                $lienHe['sanPhamTen']
                                ?? $lienHe['sanPhamId']
                                ?? 'Không chọn'
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Nội dung:
                            </strong>
                        </p>

                        <p>
                            <?= nl2br(
                                e(
                                    $lienHe['noiDung']
                                    ?? ''
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>
                                Thời gian:
                            </strong>

                            <?= e(
                                $lienHe['thoiGian']
                                ?? ''
                            ) ?>
                        </p>

                        <?php
                        $tenAnh = (string) (
                            $lienHe['anh'] ?? ''
                        );

                        $tenAnhAnToan = basename($tenAnh);
                        ?>

                        <?php if ($tenAnhAnToan !== ''): ?>

                            <p>
                                <strong>
                                    Ảnh đính kèm:
                                </strong>
                            </p>

                            <img
                                src="uploads/<?= e($tenAnhAnToan) ?>"
                                alt="Ảnh đính kèm của liên hệ"
                                width="300"
                                loading="lazy"
                            >

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>