<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Config\Database;

if (empty($_SESSION['nguoi_dung']['id'])) {
    redirect('dang-nhap.php');
}

$nguoiDungId = (int) $_SESSION['nguoi_dung']['id'];
$pdo = Database::connect();

$sql = 'SELECT id, ho_ten_nhan, so_dien_thoai_nhan,
               dia_chi_nhan, tong_tien, trang_thai, created_at
        FROM don_hang
        WHERE nguoi_dung_id = :nguoi_dung_id
        ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute(['nguoi_dung_id' => $nguoiDungId]);
$cacDonHang = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tieuDeTrang = 'Đơn hàng của tôi';
require_once __DIR__ . '/inc/header.php';
?>

<main class="container">
    <h1>Đơn hàng của tôi</h1>

    <?php if (empty($cacDonHang)): ?>
        <p>Bạn chưa có đơn hàng nào.</p>
        <p><a href="index.php">Tiếp tục mua sắm</a></p>
    <?php else: ?>
        <?php foreach ($cacDonHang as $donHang): ?>
            <section class="don-hang">
                <h2>Đơn hàng #<?= (int) $donHang['id'] ?></h2>

                <p>
                    <strong>Người nhận:</strong>
                    <?= e($donHang['ho_ten_nhan']) ?>
                </p>

                <p>
                    <strong>Số điện thoại:</strong>
                    <?= e($donHang['so_dien_thoai_nhan']) ?>
                </p>

                <p>
                    <strong>Địa chỉ:</strong>
                    <?= e($donHang['dia_chi_nhan']) ?>
                </p>

                <p>
                    <strong>Tổng tiền:</strong>
                    <?= number_format((float) $donHang['tong_tien'], 0, ',', '.') ?> ₫
                </p>

                <p>
                    <strong>Trạng thái:</strong>
                    <?= e($donHang['trang_thai']) ?>
                </p>

                <p>
                    <strong>Ngày đặt:</strong>
                    <?= e((string) $donHang['created_at']) ?>
                </p>
            </section>
            <hr>
        <?php endforeach; ?>

        <p><a href="index.php">Tiếp tục mua sắm</a></p>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>