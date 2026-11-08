<?php
/**
 * inc/ham.php
 * Các hàm tiện ích và bảo mật dùng chung cho website.
 */

/**
 * Escape dữ liệu trước khi đưa ra HTML (An toàn cả khi truyền nhầm mảng/object).
 */
function e(mixed $giaTri): string
{
    if (is_array($giaTri) || is_object($giaTri) || $giaTri === null) {
        return '';
    }

    return htmlspecialchars(
        (string) $giaTri,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Đọc và làm sạch tham số chuỗi (Chống lỗi Array Injection như ?q[]=1, ten[]=...).
 * Hỗ trợ 2 cách gọi:
 * - chuoi($_POST['ten'] ?? '', 100)
 * - chuoi($_GET, 'q', 100)
 */
function chuoi(mixed $nguon, int|string $khoaHoacDoDai = 0, int $doDaiToiDa = 0): string
{
    if (is_array($nguon) && is_string($khoaHoacDoDai)) {
        $giaTri = $nguon[$khoaHoacDoDai] ?? '';
        $maxLen = $doDaiToiDa;
    } else {
        $giaTri = $nguon;
        $maxLen = is_int($khoaHoacDoDai) ? $khoaHoacDoDai : 0;
    }

    if (!is_string($giaTri)) {
        return '';
    }

    $ketQua = trim($giaTri);

    if ($maxLen > 0) {
        $ketQua = mb_substr($ketQua, 0, $maxLen, 'UTF-8');
    }

    return $ketQua;
}

/**
 * Lấy hoặc khởi tạo mã CSRF trong Session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Sinh thẻ input ẩn chứa mã CSRF cho mọi <form method="post">.
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Kiểm tra mã CSRF từ POST form (csrf_token) hoặc fetch header (X-CSRF-Token).
 * Trả về 403 Forbidden nếu thiếu hoặc không khớp.
 */
function kiemTraCsrf(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken    = $_POST['csrf_token'] ?? $_POST['_csrf'] ?? $_POST['csrf'] ?? '';
    $headerToken  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    $guiLen = is_string($postToken) && $postToken !== ''
        ? $postToken
        : (is_string($headerToken) ? $headerToken : '');

    if (!is_string($sessionToken) || $sessionToken === '' || $guiLen === '' || !hash_equals($sessionToken, $guiLen)) {
        http_response_code(403);
        $file403 = __DIR__ . '/../403.php';
        if (file_exists($file403)) {
            require $file403;
        } else {
            echo '<h1>403 Forbidden</h1><p>Mã xác thực CSRF không hợp lệ hoặc bị thiếu.</p>';
        }
        exit;
    }
}

/**
 * Định dạng tiền Việt Nam.
 */
function vnd(int|float $gia): string
{
    return number_format(
        (float) $gia,
        0,
        ',',
        '.'
    ) . 'đ';
}

/**
 * Chuyển hướng và kết thúc request hiện tại.
 */
function redirect(string $duongDan): never
{
    header('Location: ' . $duongDan);
    exit;
}

/**
 * Kiểm tra ID sản phẩm hợp lệ theo dữ liệu hiện tại:
 * SH001, JE001, SH002...
 */
function idSanPhamHopLe(string $id): bool
{
    return preg_match(
        '/^[A-Z]{2}[0-9]{3}$/',
        $id
    ) === 1;
}

/**
 * Đọc cookie các sản phẩm đã xem gần đây.
 *
 * Chỉ giữ ID hợp lệ và tối đa 4 ID.
 *
 * @return array<int, string>
 */
function layDanhSachDaXem(): array
{
    $giaTriCookie = $_COOKIE['da_xem'] ?? '';

    if (!is_string($giaTriCookie) || $giaTriCookie === '') {
        return [];
    }

    try {
        $danhSach = json_decode(
            $giaTriCookie,
            true,
            32,
            JSON_THROW_ON_ERROR
        );
    } catch (JsonException) {
        return [];
    }

    if (!is_array($danhSach)) {
        return [];
    }

    $ketQua = [];

    foreach ($danhSach as $id) {
        if (!is_string($id)) {
            continue;
        }

        $id = trim($id);

        if (
            idSanPhamHopLe($id) &&
            !in_array($id, $ketQua, true)
        ) {
            $ketQua[] = $id;
        }

        if (count($ketQua) >= 4) {
            break;
        }
    }

    return $ketQua;
}

/**
 * Cập nhật cookie 4 sản phẩm xem gần đây.
 *
 * Sản phẩm mới nhất nằm đầu danh sách.
 *
 * @param array<int, string> $danhSachCu
 */
function capNhatDaXem(
    string $idMoi,
    array $danhSachCu
): void {
    if (!idSanPhamHopLe($idMoi)) {
        return;
    }

    $danhSachMoi = [$idMoi];

    foreach ($danhSachCu as $id) {
        if (
            is_string($id) &&
            idSanPhamHopLe($id) &&
            $id !== $idMoi
        ) {
            $danhSachMoi[] = $id;
        }

        if (count($danhSachMoi) >= 4) {
            break;
        }
    }

    $giaTriCookie = json_encode(
        $danhSachMoi,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    setcookie(
        'da_xem',
        $giaTriCookie,
        [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Đọc và kiểm tra tên khách hàng từ cookie.
 */
function layTenKhachHangTuCookie(): ?string
{
    $giaTri = $_COOKIE['stylehub_khach_hang'] ?? '';

    if (!is_string($giaTri)) {
        return null;
    }

    $giaTri = trim($giaTri);

    if ($giaTri === '') {
        return null;
    }

    if (mb_strlen($giaTri, 'UTF-8') > 100) {
        return null;
    }

    if (preg_match('/<\s*script\b/i', $giaTri) === 1) {
        return null;
    }

    return $giaTri;
}
