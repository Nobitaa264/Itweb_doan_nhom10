<?php
/**
 * inc/ham.php
 * Các hàm tiện ích dùng chung cho website.
 */

/**
 * Escape dữ liệu trước khi đưa ra HTML.
 */
function e(mixed $giaTri): string
{
    return htmlspecialchars(
        (string) $giaTri,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
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