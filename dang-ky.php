<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Config\Database;
use App\Data\KhoNguoiDung;

$tieuDe = 'Đăng ký - StyleHub';
$trang = 'dang-ky';
$goc = './';

$loi = '';
$ten = '';
$tenDangNhap = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim((string) ($_POST['ten'] ?? ''));
    $tenDangNhap = trim(
        (string) ($_POST['ten-dang-nhap'] ?? '')
    );
    $email = trim((string) ($_POST['email'] ?? ''));
    $matKhau = (string) ($_POST['mat-khau'] ?? '');
    $xacNhanMatKhau = (string) (
        $_POST['xac-nhan-mat-khau'] ?? ''
    );

    if (
        $ten === '' ||
        $tenDangNhap === '' ||
        $email === '' ||
        $matKhau === '' ||
        $xacNhanMatKhau === ''
    ) {
        $loi = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (mb_strlen($ten, 'UTF-8') > 100) {
        $loi = 'Họ tên không được vượt quá 100 ký tự.';
    } elseif (
        !preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $tenDangNhap)
    ) {
        $loi = 'Tên đăng nhập phải có 3–50 ký tự, chỉ gồm chữ cái không dấu, số, dấu chấm, gạch dưới hoặc gạch ngang.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        $loi = 'Email không hợp lệ.';
    } elseif (strlen($matKhau) < 8) {
        $loi = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif (strlen($matKhau) > 4096) {
        $loi = 'Mật khẩu quá dài.';
    } elseif ($matKhau !== $xacNhanMatKhau) {
        $loi = 'Mật khẩu xác nhận không khớp.';
    } else {
        try {
            $khoNguoiDung = new KhoNguoiDung(
                Database::connect()
            );

            $khoNguoiDung->taoTaiKhoan(
                $ten,
                $tenDangNhap,
                $email,
                $matKhau
            );

            $_SESSION['flash'] = [
                'loai' => 'thanh-cong',
                'noiDung' => 'Đăng ký thành công. Vui lòng đăng nhập.',
            ];

            redirect('dang-nhap.php');
        } catch (\RuntimeException $e) {
            $loi = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('[REGISTER_ERROR] ' . $e->getMessage());

            $loi = 'Không thể đăng ký lúc này. Vui lòng thử lại.';
        }
    }
}
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <h1 class="tieu-de-trang">Đăng ký tài khoản</h1>

        <?php if ($loi !== ''): ?>
            <p role="alert"><?= e($loi) ?></p>
        <?php endif; ?>

        <div class="khu-vuc-form">
            <form method="post" action="dang-ky.php">
                <div class="nhom-form">
                    <label for="ten">Họ và tên</label>
                    <input
                        type="text"
                        id="ten"
                        name="ten"
                        maxlength="100"
                        autocomplete="name"
                        value="<?= e($ten) ?>"
                        required
                    >
                </div>

                <div class="nhom-form">
                    <label for="ten-dang-nhap">Tên đăng nhập</label>
                    <input
                        type="text"
                        id="ten-dang-nhap"
                        name="ten-dang-nhap"
                        minlength="3"
                        maxlength="50"
                        pattern="[a-zA-Z0-9_.-]{3,50}"
                        autocomplete="username"
                        value="<?= e($tenDangNhap) ?>"
                        required
                    >
                </div>

                <div class="nhom-form">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        autocomplete="email"
                        value="<?= e($email) ?>"
                        required
                    >
                </div>

                <div class="nhom-form">
                    <label for="mat-khau">Mật khẩu</label>
                    <input
                        type="password"
                        id="mat-khau"
                        name="mat-khau"
                        minlength="8"
                        maxlength="4096"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="nhom-form">
                    <label for="xac-nhan-mat-khau">
                        Xác nhận mật khẩu
                    </label>
                    <input
                        type="password"
                        id="xac-nhan-mat-khau"
                        name="xac-nhan-mat-khau"
                        minlength="8"
                        maxlength="4096"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button class="nut" type="submit">
                    Đăng ký
                </button>
            </form>

            <p>
                Đã có tài khoản?
                <a href="dang-nhap.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
