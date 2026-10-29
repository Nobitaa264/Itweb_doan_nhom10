<?php

declare(strict_types=1);

/**
 * dang-nhap.php
 * Xác thực tài khoản bằng MySQL và password_verify().
 */

require_once __DIR__ . '/inc/config.php';

use App\Config\Database;

$tieuDe = 'Đăng nhập - StyleHub';
$trang = 'dang-nhap';
$goc = './';

$loi = '';
$flash = null;

$tenDangNhap = trim(
    (string) ($_POST['ten-dang-nhap'] ?? '')
);

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matKhau = (string) ($_POST['mat-khau'] ?? '');

    if ($tenDangNhap === '' || $matKhau === '') {
        $loi = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        try {
            $pdo = Database::connect();

            $stmt = $pdo->prepare(
                'SELECT id, ten, ten_dang_nhap, email, mat_khau, vai_tro
                 FROM nguoi_dung
                 WHERE ten_dang_nhap = :ten_dang_nhap
                 LIMIT 1'
            );

            $stmt->execute([
                'ten_dang_nhap' => $tenDangNhap,
            ]);

            $taiKhoan = $stmt->fetch();

            $hopLe = is_array($taiKhoan)
                && password_verify(
                    $matKhau,
                    (string) $taiKhoan['mat_khau']
                );

            if (!$hopLe) {
                error_log(
                    sprintf(
                        '[LOGIN_FAIL] Dang nhap that bai | IP: %s',
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    )
                );

                $loi = 'Sai tên đăng nhập hoặc mật khẩu.';
            } else {
                // Cập nhật hash nếu thuật toán/cấu hình đã thay đổi.
                if (password_needs_rehash(
                    (string) $taiKhoan['mat_khau'],
                    PASSWORD_DEFAULT
                )) {
                    $hashMoi = password_hash(
                        $matKhau,
                        PASSWORD_DEFAULT
                    );

                    $update = $pdo->prepare(
                        'UPDATE nguoi_dung
                         SET mat_khau = :mat_khau
                         WHERE id = :id'
                    );

                    $update->execute([
                        'mat_khau' => $hashMoi,
                        'id' => $taiKhoan['id'],
                    ]);
                }

                session_regenerate_id(true);

                // Chỉ lưu thông tin cần thiết vào session.
                $_SESSION['nguoi_dung'] = [
                    'id' => (int) $taiKhoan['id'],
                    'tenDangNhap' => (string) $taiKhoan['ten_dang_nhap'],
                    'ten' => (string) $taiKhoan['ten'],
                    'vaiTro' => (string) $taiKhoan['vai_tro'],
                ];

                $_SESSION['flash'] = [
                    'loai' => 'thanh-cong',
                    'noiDung' => 'Đăng nhập thành công.',
                ];

                if ($taiKhoan['vai_tro'] === 'quan_tri' || $taiKhoan['vai_tro'] === 'admin') {
    header('Location: quan-tri.php');
} else {
    header('Location: index.php');
}

                exit;
            }
        } catch (\Throwable $e) {
            error_log('[LOGIN_ERROR] ' . $e->getMessage());

            $loi = 'Hệ thống chưa thể đăng nhập lúc này. Vui lòng thử lại.';
        }
    }
}
?>



<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>
<main class="noi-dung">
    <div class="bao">

        <h1 class="tieu-de-trang">
            Đăng nhập
        </h1>

        

        <section class="khu-vuc-form">
            
<?php if ($flash !== null): ?>
    <p role="status" aria-live="polite">
        <?= e($flash['noiDung'] ?? '') ?>
    </p>
<?php endif; ?>

<?php if ($loi !== ''): ?>
    <p role="alert">
        <?= e($loi) ?>
    </p>
<?php endif; ?>


            <h2 class="tieu-de-trang">Chào mừng trở lại</h2>
<p class="mo-ta-dang-nhap">
    Đăng nhập vào tài khoản StyleHub của bạn.
</p>

            <form
                method="post"
                action="dang-nhap.php"
            >

                <div class="nhom-form">

                    <label for="ten-dang-nhap">
                        Tên đăng nhập
                    </label>

                    <input
                        type="text"
                        id="ten-dang-nhap"
                        name="ten-dang-nhap"
                        value="<?= e($tenDangNhap) ?>"
                        autocomplete="username"
                        required
                    >

                </div>

                <div class="nhom-form">

                    <label for="mat-khau">
                        Mật khẩu
                    </label>

                    <input
                        type="password"
                        id="mat-khau"
                        name="mat-khau"
                        autocomplete="current-password"
                        required
                    >

                </div>

                <button
                    class="nut"
                    type="submit"
                >
                    Đăng nhập
                </button>

            </form>

            <p class="chuyen-trang" style="margin-top: 15px;">
                Chưa có tài khoản? <a href="dang-ky.php">Đăng ký ngay</a>
            </p>

            <a class="lien-ket-trang-chu" href="index.php">
    ← Quay lại trang chủ
</a>

        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>