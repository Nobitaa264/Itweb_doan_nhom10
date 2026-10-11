<?php
/**
 * lien-he.php
 * Xử lý và hiển thị biểu mẫu liên hệ bằng PHP.
 *
 * Chức năng:
 * - Kiểm tra dữ liệu POST ở phía máy chủ
 * - Họ tên, số điện thoại, email, sản phẩm quan tâm, nội dung
 * - Upload ảnh tùy chọn, tối đa 2 MB, kiểm tra MIME thật bằng finfo
 * - Lưu dữ liệu vào storage/lien-he.jsonl
 * - PRG + flash message + cookie ghi nhớ tên khách hàng
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$tieuDe = 'Liên hệ - StyleHub';
$trang = 'lien-he';
$goc = './';

$khoSanPham = new KhoSanPham();

$loi = [];

$hoTen = trim((string) ($_POST['ho-ten'] ?? ''));
$soDienThoai = trim((string) ($_POST['so-dien-thoai'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$sanPhamId = trim((string) ($_POST['san-pham'] ?? ''));
$noiDung = trim((string) ($_POST['noi-dung'] ?? ''));

$sanPhamDaChon = null;

$thuMucStorage = __DIR__ . '/storage';
$thuMucUploads = __DIR__ . '/uploads';

$tepAnhDaLuu = '';
$tenTepDaChuanBi = '';
$duongDanAnhDaChuanBi = '';
$tepTaiLenHopLe = false;

// Các thư mục này đã có .htaccess/.gitkeep; tạo bổ sung nếu cần khi chạy trên máy mới.
if (!is_dir($thuMucStorage) && !@mkdir($thuMucStorage, 0755, true) && !is_dir($thuMucStorage)) {
    throw new RuntimeException('Không thể tạo thư mục storage.');
}

if (!is_dir($thuMucUploads) && !@mkdir($thuMucUploads, 0755, true) && !is_dir($thuMucUploads)) {
    throw new RuntimeException('Không thể tạo thư mục uploads.');
}

$danhSachSanPham = $khoSanPham->tatCa();

if ($sanPhamId !== '') {
    $sanPhamDaChon = $khoSanPham->timTheoId($sanPhamId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Họ tên.
    if ($hoTen === '') {
        $loi['ho-ten'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($hoTen, 'UTF-8') < 2) {
        $loi['ho-ten'] = 'Họ và tên phải có ít nhất 2 ký tự.';
    } elseif (mb_strlen($hoTen, 'UTF-8') > 100) {
        $loi['ho-ten'] = 'Họ và tên không được vượt quá 100 ký tự.';
    } elseif (preg_match('/<\s*script\b/i', $hoTen) === 1) {
        $loi['ho-ten'] = 'Họ và tên không hợp lệ.';
    }

    // Số điện thoại.
    if ($soDienThoai === '') {
        $loi['so-dien-thoai'] = 'Vui lòng nhập số điện thoại.';
    } elseif (
        preg_match('/^(?:0|\+84)(?:3|5|7|8|9)\d{8}$/', $soDienThoai) !== 1
    ) {
        $loi['so-dien-thoai'] = 'Số điện thoại không hợp lệ.';
    }

    // Email là trường không bắt buộc; nếu có nhập thì phải đúng định dạng.
    if (
        $email !== ''
        && filter_var($email, FILTER_VALIDATE_EMAIL) === false
    ) {
        $loi['email'] = 'Email không hợp lệ.';
    } elseif (mb_strlen($email, 'UTF-8') > 150) {
        $loi['email'] = 'Email không được vượt quá 150 ký tự.';
    }

    // Sản phẩm quan tâm là trường tùy chọn; nếu có chọn thì ID phải tồn tại.
    if ($sanPhamId !== '' && $sanPhamDaChon === null) {
        $loi['san-pham'] = 'Sản phẩm được chọn không tồn tại.';
    }

    // Nội dung liên hệ.
    if ($noiDung === '') {
        $loi['noi-dung'] = 'Vui lòng nhập nội dung liên hệ.';
    } elseif (mb_strlen($noiDung, 'UTF-8') < 5) {
        $loi['noi-dung'] = 'Nội dung phải có ít nhất 5 ký tự.';
    } elseif (mb_strlen($noiDung, 'UTF-8') > 2000) {
        $loi['noi-dung'] = 'Nội dung không được vượt quá 2000 ký tự.';
    } elseif (preg_match('/<\s*script\b/i', $noiDung) === 1) {
        $loi['noi-dung'] = 'Nội dung không hợp lệ.';
    }

    // Kiểm tra file upload nếu người dùng có chọn.
    $tepTaiLen = $_FILES['anh'] ?? null;

    if (
        is_array($tepTaiLen)
        && ($tepTaiLen['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ) {
        $maLoiUpload = (int) ($tepTaiLen['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($maLoiUpload !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Không thể tải ảnh lên. Vui lòng thử lại.';
        } else {
            $kichThuoc = (int) ($tepTaiLen['size'] ?? 0);
            $gioiHan2MB = 2 * 1024 * 1024;

            if ($kichThuoc <= 0) {
                $loi['anh'] = 'Tệp ảnh không hợp lệ.';
            } elseif ($kichThuoc > $gioiHan2MB) {
                $loi['anh'] = 'Ảnh không được vượt quá 2 MB.';
            } elseif (
                !isset($tepTaiLen['tmp_name'])
                || !is_string($tepTaiLen['tmp_name'])
                || !is_uploaded_file($tepTaiLen['tmp_name'])
            ) {
                $loi['anh'] = 'Tệp tải lên không hợp lệ.';
            } else {
                try {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tepTaiLen['tmp_name']);
                } catch (Throwable) {
                    $mime = false;
                }

                $cacMimeChoPhep = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];

                if (!is_string($mime) || !array_key_exists($mime, $cacMimeChoPhep)) {
                    $loi['anh'] = 'Chỉ nhận ảnh JPG, PNG hoặc WebP.';
                } else {
                    try {
                        $tenNgauNhien = bin2hex(random_bytes(16));
                    } catch (Throwable) {
                        $tenNgauNhien = '';
                    }

                    if ($tenNgauNhien === '') {
                        $loi['anh'] = 'Không thể tạo tên tệp an toàn.';
                    } else {
                        $tenTepDaChuanBi = $tenNgauNhien . '.' . $cacMimeChoPhep[$mime];
                        $duongDanAnhDaChuanBi = $thuMucUploads
                            . DIRECTORY_SEPARATOR
                            . $tenTepDaChuanBi;
                        $tepTaiLenHopLe = true;
                    }
                }
            }
        }
    }

    // Chỉ di chuyển file và ghi JSONL khi toàn bộ dữ liệu đã hợp lệ.
    if ($loi === []) {
        if ($tepTaiLenHopLe) {
            if (
                !is_array($tepTaiLen)
                || !isset($tepTaiLen['tmp_name'])
                || !is_string($tepTaiLen['tmp_name'])
                || !move_uploaded_file($tepTaiLen['tmp_name'], $duongDanAnhDaChuanBi)
            ) {
                $loi['anh'] = 'Không thể lưu ảnh lên máy chủ.';
            } else {
                $tepAnhDaLuu = $tenTepDaChuanBi;
            }
        }
    }

    if ($loi === []) {
        $duongDanLuu = $thuMucStorage
            . DIRECTORY_SEPARATOR
            . 'lien-he.jsonl';

        $banGhi = [
            'thoiGian' => date(DATE_ATOM),
            'hoTen' => $hoTen,
            'soDienThoai' => $soDienThoai,
            'email' => $email,
            'sanPhamId' => $sanPhamDaChon?->id,
            'sanPhamTen' => $sanPhamDaChon?->ten,
            'noiDung' => $noiDung,
            'anh' => $tepAnhDaLuu,
        ];

        try {
            $dongJson = json_encode(
                $banGhi,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            if ($tepAnhDaLuu !== '') {
                $duongDanAnh = $thuMucUploads
                    . DIRECTORY_SEPARATOR
                    . $tepAnhDaLuu;

                if (is_file($duongDanAnh)) {
                    @unlink($duongDanAnh);
                }
            }

            throw new RuntimeException('Không thể tạo dữ liệu liên hệ.');
        }

        $soByte = file_put_contents(
            $duongDanLuu,
            $dongJson . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($soByte === false) {
            if ($tepAnhDaLuu !== '') {
                $duongDanAnh = $thuMucUploads
                    . DIRECTORY_SEPARATOR
                    . $tepAnhDaLuu;

                if (is_file($duongDanAnh)) {
                    @unlink($duongDanAnh);
                }
            }

            throw new RuntimeException('Không thể lưu yêu cầu liên hệ.');
        }

        $_SESSION['flash'] = [
            'loai' => 'thanh-cong',
            'noiDung' => 'Gửi yêu cầu thành công. Cảm ơn bạn đã liên hệ với StyleHub.',
        ];

        setcookie(
            'stylehub_khach_hang',
            $hoTen,
            [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        redirect('lien-he.php');
    }
}

$flash = null;

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$tenKhachHang = layTenKhachHangTuCookie();
?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <h1 class="tieu-de-trang">Liên hệ StyleHub</h1>

        <p>
            Gửi thông tin để StyleHub tư vấn và hỗ trợ bạn.
        </p>

        <?php if ($flash !== null): ?>
            <p class="thong-bao" role="status" aria-live="polite">
                <?= e($flash['noiDung'] ?? '') ?>
            </p>
        <?php endif; ?>

        <div class="bo-cuc-lien-he">
            <section class="khu-vuc-form">
                <h2>Gửi yêu cầu hỗ trợ</h2>

                <form
                    id="form-dat-hang"
                    class="form-lien-he"
                    action="lien-he.php"
                    method="post"
                    enctype="multipart/form-data"
                >
                    <div class="nhom-form">
                        <label for="ho-ten">Họ và tên <span>*</span></label>
                        <input
                            type="text"
                            id="ho-ten"
                            name="ho-ten"
                            minlength="2"
                            maxlength="100"
                            value="<?= e($hoTen) ?>"
                            autocomplete="name"
                            required
                            aria-describedby="loi-ho-ten"
                        >
                        <?php if (isset($loi['ho-ten'])): ?>
                            <p class="loi-truong" id="loi-ho-ten" role="alert">
                                <?= e($loi['ho-ten']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="so-dien-thoai">Số điện thoại <span>*</span></label>
                        <input
                            type="tel"
                            id="so-dien-thoai"
                            name="so-dien-thoai"
                            maxlength="13"
                            value="<?= e($soDienThoai) ?>"
                            autocomplete="tel"
                            inputmode="tel"
                            placeholder="Ví dụ: 0912345678"
                            required
                            aria-describedby="loi-so-dien-thoai"
                        >
                        <?php if (isset($loi['so-dien-thoai'])): ?>
                            <p class="loi-truong" id="loi-so-dien-thoai" role="alert">
                                <?= e($loi['so-dien-thoai']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            maxlength="150"
                            value="<?= e($email) ?>"
                            autocomplete="email"
                            aria-describedby="loi-email"
                        >
                        <?php if (isset($loi['email'])): ?>
                            <p class="loi-truong" id="loi-email" role="alert">
                                <?= e($loi['email']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="san-pham">Sản phẩm quan tâm</label>
                        <select
                            id="san-pham"
                            name="san-pham"
                            aria-describedby="loi-san-pham"
                        >
                            <option value="">-- Không chọn --</option>
                            <?php foreach ($danhSachSanPham as $sanPham): ?>
                                <option
                                    value="<?= e($sanPham->id) ?>"
                                    <?= $sanPhamId === $sanPham->id ? 'selected' : '' ?>
                                >
                                    <?= e($sanPham->ten) ?> - <?= vnd($sanPham->gia) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($loi['san-pham'])): ?>
                            <p class="loi-truong" id="loi-san-pham" role="alert">
                                <?= e($loi['san-pham']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="noi-dung">Nội dung liên hệ <span>*</span></label>
                        <textarea
                            id="noi-dung"
                            name="noi-dung"
                            minlength="5"
                            maxlength="2000"
                            rows="6"
                            required
                            aria-describedby="loi-noi-dung"
                        ><?= e($noiDung) ?></textarea>
                        <?php if (isset($loi['noi-dung'])): ?>
                            <p class="loi-truong" id="loi-noi-dung" role="alert">
                                <?= e($loi['noi-dung']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="anh">
                            Ảnh đính kèm <span>(không bắt buộc)</span>
                        </label>
                        <input
                            type="file"
                            id="anh"
                            name="anh"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            aria-describedby="mo-ta-anh loi-anh"
                        >
                        <p id="mo-ta-anh">
                            JPG, PNG hoặc WebP, tối đa 2 MB.
                        </p>
                        <?php if (isset($loi['anh'])): ?>
                            <p class="loi-truong" id="loi-anh" role="alert">
                                <?= e($loi['anh']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <button class="nut" type="submit">Gửi yêu cầu</button>
                    </div>
                </form>
            </section>

            <section class="thong-tin-lien-he">
                <h2>Thông tin liên hệ</h2>

                <address>
                    <p><strong>StyleHub</strong></p>
                    <p>
                        Website phục vụ học phần Thiết kế và Lập trình web.
                    </p>

                    <?php if ($tenKhachHang !== null): ?>
                        <p>
                            Xin chào,
                            <strong><?= e($tenKhachHang) ?></strong>.
                        </p>
                    <?php endif; ?>
                </address>

                <section class="khoi-noi-dung">
    <h2>Địa chỉ & Bản đồ</h2>
    <p>Trường Đại học Sư phạm - Đại học Đà Nẵng</p>
    <p>
        <a 
            class="nut" 
            href="https://www.google.com/maps?q=Truong%20Dai%20hoc%20Su%20pham%20Dai%20hoc%20Da%20Nang" 
            target="_blank" 
            rel="noopener noreferrer"
        >
            Xem vị trí trên Google Maps
        </a>
    </p>
</section>

                <section class="khoi-noi-dung">
                    <h2>Lưu ý</h2>
                    <p>
                        Đây là website phục vụ học tập. Thông tin từ biểu mẫu được
                        sử dụng để minh họa chức năng xử lý dữ liệu bằng PHP.
                    </p>
                </section>
            </section>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>