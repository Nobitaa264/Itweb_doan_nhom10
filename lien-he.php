<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Liên hệ - StyleHub';
$trang = 'lien-he';
$goc = './';

$du = [
    'ho-ten' => '',
    'so-dien-thoai' => '',
    'email' => '',
    'san-pham' => '',
    'noi-dung' => '',
];

$loi = [];

$thuMucStorage = __DIR__ . '/storage';
$thuMucUploads = __DIR__ . '/uploads';

if (!is_dir($thuMucStorage) &&
    !mkdir($thuMucStorage, 0755, true) &&
    !is_dir($thuMucStorage)) {
    throw new RuntimeException('Không thể tạo thư mục storage.');
}

if (!is_dir($thuMucUploads) &&
    !mkdir($thuMucUploads, 0755, true) &&
    !is_dir($thuMucUploads)) {
    throw new RuntimeException('Không thể tạo thư mục uploads.');
}

// Lấy danh sách sản phẩm nếu dự án có lớp KhoSanPham.
$khoSanPham = new \App\Data\KhoSanPham();
$danhSachSanPham = $khoSanPham->tatCa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đọc dữ liệu và giữ lại giá trị để hiển thị khi có lỗi.
    foreach ($du as $truong => $giaTri) {
        $du[$truong] = trim((string) ($_POST[$truong] ?? ''));
    }

    // 1. Họ tên.
    if ($du['ho-ten'] === '') {
        $loi['ho-ten'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($du['ho-ten'], 'UTF-8') < 2) {
        $loi['ho-ten'] = 'Họ tên phải có ít nhất 2 ký tự.';
    } elseif (mb_strlen($du['ho-ten'], 'UTF-8') > 100) {
        $loi['ho-ten'] = 'Họ tên không được vượt quá 100 ký tự.';
    } elseif (preg_match('/<\s*script\b/i', $du['ho-ten'])) {
        $loi['ho-ten'] = 'Họ tên không hợp lệ.';
    }

    // 2. Số điện thoại.
    if ($du['so-dien-thoai'] === '') {
        $loi['so-dien-thoai'] = 'Vui lòng nhập số điện thoại.';
    } elseif (
        !preg_match('/^(?:0|\+84)(?:3|5|7|8|9)\d{8}$/', $du['so-dien-thoai'])
    ) {
        $loi['so-dien-thoai'] = 'Số điện thoại Việt Nam không hợp lệ.';
    }

    // 3. Email không bắt buộc, nhưng nếu nhập thì phải hợp lệ.
    if (
        $du['email'] !== '' &&
        filter_var($du['email'], FILTER_VALIDATE_EMAIL) === false
    ) {
        $loi['email'] = 'Email không đúng định dạng.';
    } elseif (mb_strlen($du['email'], 'UTF-8') > 150) {
        $loi['email'] = 'Email không được vượt quá 150 ký tự.';
    }

    // 4. Sản phẩm quan tâm không bắt buộc.
    $sanPhamDaChon = null;

    if ($du['san-pham'] !== '') {
        $sanPhamDaChon = $khoSanPham->timTheoId($du['san-pham']);

        if ($sanPhamDaChon === null) {
            $loi['san-pham'] = 'Sản phẩm được chọn không tồn tại.';
        }
    }

    // 5. Nội dung liên hệ.
    if ($du['noi-dung'] === '') {
        $loi['noi-dung'] = 'Vui lòng nhập nội dung liên hệ.';
    } elseif (mb_strlen($du['noi-dung'], 'UTF-8') < 5) {
        $loi['noi-dung'] = 'Nội dung phải có ít nhất 5 ký tự.';
    } elseif (mb_strlen($du['noi-dung'], 'UTF-8') > 2000) {
        $loi['noi-dung'] = 'Nội dung không được vượt quá 2000 ký tự.';
    } elseif (preg_match('/<\s*script\b/i', $du['noi-dung'])) {
        $loi['noi-dung'] = 'Nội dung không hợp lệ.';
    }

    // 6. Kiểm tra một ảnh đính kèm, không bắt buộc.
    $tenAnh = '';
    $duongDanAnhTam = '';
    $tepAnh = $_FILES['anh'] ?? null;

    if (is_array($tepAnh) &&
        ($tepAnh['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

        if (($tepAnh['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Tải ảnh lên thất bại.';
        } elseif (($tepAnh['size'] ?? 0) <= 0) {
            $loi['anh'] = 'Ảnh không hợp lệ.';
        } elseif (($tepAnh['size'] ?? 0) > 2 * 1024 * 1024) {
            $loi['anh'] = 'Ảnh không được vượt quá 2 MB.';
        } elseif (
            !isset($tepAnh['tmp_name']) ||
            !is_uploaded_file($tepAnh['tmp_name'])
        ) {
            $loi['anh'] = 'Tệp tải lên không hợp lệ.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tepAnh['tmp_name']);

            $mimeChoPhep = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            if (!is_string($mime) || !isset($mimeChoPhep[$mime])) {
                $loi['anh'] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
            } else {
                try {
                    $tenAnh = bin2hex(random_bytes(16))
                        . '.' . $mimeChoPhep[$mime];
                } catch (Throwable $e) {
                    $loi['anh'] = 'Không thể tạo tên ảnh an toàn.';
                }

                $duongDanAnhTam = $thuMucUploads . DIRECTORY_SEPARATOR . $tenAnh;
            }
        }
    }

    // 7. Nếu hợp lệ, lưu ảnh và thông tin liên hệ.
    if ($loi === []) {
        if ($tenAnh !== '' &&
            !move_uploaded_file($tepAnh['tmp_name'], $duongDanAnhTam)) {
            $loi['anh'] = 'Không thể lưu ảnh lên máy chủ.';
        }
    }

    if ($loi === []) {
        $banGhi = [
            'thoiGian' => date(DATE_ATOM),
            'hoTen' => $du['ho-ten'],
            'soDienThoai' => $du['so-dien-thoai'],
            'email' => $du['email'],
            'sanPhamId' => $sanPhamDaChon?->id,
            'sanPhamTen' => $sanPhamDaChon?->ten,
            'noiDung' => $du['noi-dung'],
            'anh' => $tenAnh,
        ];

        $dongJson = json_encode(
            $banGhi,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_THROW_ON_ERROR
        );

        $duongDanLuu = $thuMucStorage . '/lien-he.jsonl';

        if (file_put_contents(
            $duongDanLuu,
            $dongJson . PHP_EOL,
            FILE_APPEND | LOCK_EX
        ) === false) {
            if ($tenAnh !== '' && is_file($duongDanAnhTam)) {
                unlink($duongDanAnhTam);
            }

            throw new RuntimeException('Không thể lưu yêu cầu liên hệ.');
        }

        // Flash message và PRG.
        $_SESSION['flash'] = 'Gửi yêu cầu thành công. Cảm ơn bạn đã liên hệ với StyleHub.';

        redirect('lien-he.php');
    }
}

// Đọc thông báo flash một lần sau khi chuyển hướng.
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>

<?php require __DIR__ . '/inc/header.php'; ?>
<?php require __DIR__ . '/inc/menu.php'; ?>

<main class="noi-dung">
    <div class="bao">
        <h1 class="tieu-de-trang">Liên hệ StyleHub</h1>
        <p>Gửi thông tin để StyleHub tư vấn và hỗ trợ bạn.</p>

        <?php if (is_string($flash) && $flash !== ''): ?>
            <div class="thong-bao-thanh-cong" role="status" aria-live="polite">
                <?= e($flash) ?>
            </div>
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
                        <label for="ho-ten">Họ và tên *</label>
                        <input
                            type="text"
                            id="ho-ten"
                            name="ho-ten"
                            minlength="2"
                            maxlength="100"
                            value="<?= e($du['ho-ten']) ?>"
                            class="<?= isset($loi['ho-ten']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['ho-ten']) ? 'true' : 'false' ?>"
                            autocomplete="name"
                            required
                        >
                        <?php if (isset($loi['ho-ten'])): ?>
                            <p class="thong-bao-loi" role="alert">
                                <?= e($loi['ho-ten']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="so-dien-thoai">Số điện thoại *</label>
                        <input
                            type="tel"
                            id="so-dien-thoai"
                            name="so-dien-thoai"
                            value="<?= e($du['so-dien-thoai']) ?>"
                            class="<?= isset($loi['so-dien-thoai']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['so-dien-thoai']) ? 'true' : 'false' ?>"
                            autocomplete="tel"
                            placeholder="Ví dụ: 0912345678"
                            required
                        >
                        <?php if (isset($loi['so-dien-thoai'])): ?>
                            <p class="thong-bao-loi" role="alert">
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
                            value="<?= e($du['email']) ?>"
                            class="<?= isset($loi['email']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['email']) ? 'true' : 'false' ?>"
                            autocomplete="email"
                        >
                        <?php if (isset($loi['email'])): ?>
                            <p class="thong-bao-loi" role="alert">
                                <?= e($loi['email']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="san-pham">Sản phẩm quan tâm</label>
                        <select id="san-pham" name="san-pham"
                            class="<?= isset($loi['san-pham']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['san-pham']) ? 'true' : 'false' ?>">
                            <option value="">-- Không chọn --</option>
                            <?php foreach ($danhSachSanPham as $sanPham): ?>
                                <option
                                    value="<?= e((string) $sanPham->id) ?>"
                                    <?= $du['san-pham'] === (string) $sanPham->id ? 'selected' : '' ?>
                                >
                                    <?= e((string) $sanPham->ten) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($loi['san-pham'])): ?>
                            <p class="thong-bao-loi" role="alert">
                                <?= e($loi['san-pham']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="noi-dung">Nội dung liên hệ *</label>
                        <textarea
                            id="noi-dung"
                            name="noi-dung"
                            minlength="5"
                            maxlength="2000"
                            rows="6"
                            required
                        
                            class="<?= isset($loi['noi-dung']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['noi-dung']) ? 'true' : 'false' ?>"><?= e($du['noi-dung']) ?></textarea>
                        <?php if (isset($loi['noi-dung'])): ?>
                            <p class="thong-bao-loi" role="alert">
                                <?= e($loi['noi-dung']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="nhom-form">
                        <label for="anh">Ảnh đính kèm (không bắt buộc)</label>
                        <input
                            type="file"
                            id="anh"
                            name="anh"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        
                            class="<?= isset($loi['anh']) ? 'o-nhap-loi' : '' ?>"
                            aria-invalid="<?= isset($loi['anh']) ? 'true' : 'false' ?>">
                        <p>Chấp nhận JPG, PNG hoặc WebP, tối đa 2 MB.</p>
                        <?php if (isset($loi['anh'])): ?>
                            <p class="thong-bao-loi" role="alert">
                                <?= e($loi['anh']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <button class="nut-form" type="submit">Gửi yêu cầu</button>
                </form>
            </section>
            
<section class="thong-tin-lien-he">
    <h2>Thông tin liên hệ</h2>
    <p>StyleHub luôn sẵn sàng tư vấn và hỗ trợ bạn.</p>

    <h3>Bản đồ vị trí</h3>
    <div class="ban-do-lien-he">
        <iframe
            src="https://maps.google.com/maps?q=Da%20Nang%2C%20Viet%20Nam&t=&z=14&ie=UTF8&iwloc=&output=embed"
            title="Bản đồ vị trí liên hệ StyleHub"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen>
        </iframe>
    </div>
</section>

        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
