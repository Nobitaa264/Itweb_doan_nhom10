<?php
/**
 * dang-nhap.php
 * Xử lý đăng nhập bằng password_verify().
 */

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Đăng nhập - StyleHub';
$trang = 'dang-nhap';
$goc = './';

$loi = '';

$tenDangNhap = trim(
    (string) ($_POST['ten-dang-nhap'] ?? '')
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $matKhau = (string) (
        $_POST['mat-khau'] ?? ''
    );

    if ($tenDangNhap === '' || $matKhau === '') {

        $loi = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';

    } else {

        $taiKhoan = require __DIR__ . '/inc/tai-khoan.php';

        $taiKhoanHienTai =
            $taiKhoan[$tenDangNhap] ?? null;

        $hopLe =
            is_array($taiKhoanHienTai) &&
            isset($taiKhoanHienTai['matKhau']) &&
            password_verify(
                $matKhau,
                $taiKhoanHienTai['matKhau']
            );

        if (!$hopLe) {

            error_log(
                sprintf(
                    '[LOGIN_FAIL] Tài khoản thất bại: %s | IP: %s',
                    $tenDangNhap,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                )
            );

            $loi =
                'Sai tên đăng nhập hoặc mật khẩu.';

        } else {

            session_regenerate_id(true);

            $_SESSION['nguoi_dung'] = [
                'tenDangNhap' => $tenDangNhap,
                'ten' =>
                    (string) (
                        $taiKhoanHienTai['ten']
                        ?? $tenDangNhap
                    ),
            ];

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đăng nhập thành công.',
            ];

            header('Location: quan-tri.php');
            exit;
        }
    }
}

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
            Đăng nhập
        </h1>

        <?php if ($flash !== null): ?>
            <p
                role="status"
                aria-live="polite"
            >
                <?= e($flash['noiDung'] ?? '') ?>
            </p>
        <?php endif; ?>

        <?php if ($loi !== ''): ?>
            <p
                role="alert"
            >
                <?= e($loi) ?>
            </p>
        <?php endif; ?>

        <section class="khu-vuc-form">

            <h2>
                Tài khoản quản trị
            </h2>

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

        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>