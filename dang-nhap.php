<?php

declare(strict_types=1);

/**
 * dang-nhap.php - Xac thuc MySQL, CSRF, gioi han thu sai va ghi nhat ky TH7.
 * Giu nguyen form HTML, Session, CSP va chuyen trang cua StyleHub.
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/chong-brute-force.php';

use App\Config\Database;

$tieuDe = 'Đăng nhập - StyleHub';
$trang = 'dang-nhap';
$goc = './';

$loi = '';
$flash = null;
$tenDangNhap = chuoi($_POST['ten-dang-nhap'] ?? '', 100);

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // inc/config.php da kiem tra CSRF; giu lan kiem tra tai trang dang nhap.
    csrfKiemTraPost();

    $matKhauGuiLen = $_POST['mat-khau'] ?? '';
    $matKhau = is_string($matKhauGuiLen) ? $matKhauGuiLen : '';

    if ($tenDangNhap === '' || $matKhau === '') {
        $loi = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        $ip = th7LoginIp();
        try {
            // Dat cho mot lan thu truoc khi kiem tra mat khau.
            // 5 luot / tai khoan hoac 15 luot / IP trong 15 phut.
            $choGiay = th7LoginBatDauThu($tenDangNhap, $ip);

            if ($choGiay > 0) {
                http_response_code(429);
                header('Retry-After: ' . $choGiay);
                header('Cache-Control: no-store');
                th7LoginGhiNhatKy('login_blocked', $tenDangNhap, $ip);
                $loi = 'Đăng nhập đang bị giới hạn tạm thời. Vui lòng thử lại sau ít phút.';
            } else {
                $pdo = Database::connect();
                $stmt = $pdo->prepare(
                    'SELECT id, ten, ten_dang_nhap, email, mat_khau, vai_tro
                     FROM nguoi_dung
                     WHERE ten_dang_nhap = :ten_dang_nhap
                     LIMIT 1'
                );
                $stmt->execute(['ten_dang_nhap' => $tenDangNhap]);
                $taiKhoan = $stmt->fetch(PDO::FETCH_ASSOC);

                // Xac minh hash gia lap neu khong ton tai tai khoan de
                // giam khac biet thoi gian phan hoi, khong lo username ton tai.
                $hashGia = '$2y$10$UrmUvJLJFIWJKarMqkjrX.hSgmmdrdxtZ01/qusZ5/izj13b8yKfK';
                $hashDeKiemTra = is_array($taiKhoan)
                    ? (string) $taiKhoan['mat_khau']
                    : $hashGia;
                $hopLe = password_verify($matKhau, $hashDeKiemTra)
                    && is_array($taiKhoan);

                if (!$hopLe) {
                    th7LoginGhiNhatKy('login_failed', $tenDangNhap, $ip);
                    $loi = 'Sai tên đăng nhập hoặc mật khẩu.';
                } else {
                    // Bao toan chuc nang cap nhat password hash hien tai.
                    if (password_needs_rehash(
                        (string) $taiKhoan['mat_khau'],
                        PASSWORD_DEFAULT
                    )) {
                        $hashMoi = password_hash($matKhau, PASSWORD_DEFAULT);
                        $update = $pdo->prepare(
                            'UPDATE nguoi_dung SET mat_khau = :mat_khau WHERE id = :id'
                        );
                        $update->execute([
                            'mat_khau' => $hashMoi,
                            'id' => $taiKhoan['id'],
                        ]);
                    }

                    th7LoginThanhCong($tenDangNhap);
                    th7LoginGhiNhatKy('login_success', $tenDangNhap, $ip);
                    if (!session_regenerate_id(true)) {
                        throw new RuntimeException('Khong the tao phien dang nhap moi.');
                    }
                    $_SESSION['uid'] = (int) $taiKhoan['id'];
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
            }
        } catch (Throwable $e) {
            // Hien thi loi chi tiet de debug truc quan tren man hinh
            $loi = 'Lỗi chi tiết: ' . $e->getMessage() . ' (Tại dòng ' . $e->getLine() . ')';
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
            
            <h2 class="tieu-de-trang">Chào mừng trở lại</h2>
            <p class="mo-ta-dang-nhap">
                Đăng nhập vào tài khoản StyleHub của bạn.
            </p>

            <?php if ($flash !== null): ?>
                <p role="status" aria-live="polite">
                    <?= e($flash['noiDung'] ?? '') ?>
                </p>
            <?php endif; ?>

            <?php if ($loi !== ''): ?>
                <p role="alert" style="color: red; font-weight: bold;">
                    <?= e($loi) ?>
                </p>
            <?php endif; ?>

            <form
                method="post"
                action="dang-nhap.php"
            >
                <?= csrfField() ?>

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

            <p class="chuyen-trang chuyen-trang--dang-nhap">
                Chưa có tài khoản? <a href="dang-ky.php">Đăng ký ngay</a>
            </p>

            <a class="lien-ket-trang-chu" href="index.php">
                ← Quay lại trang chủ
            </a>

        </section>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>