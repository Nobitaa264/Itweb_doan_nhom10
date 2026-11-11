<?php
declare(strict_types=1);

/**
 * inc/csrf.php — bộ kiểm tra CSRF dùng chung cho StyleHub.
 *
 * GIAI ĐOẠN 1:
 * - inc/ham.php hiện đã có csrfToken(), csrfField(), kiemTraCsrf().
 * - Không khai báo lại các hàm đó để tránh lỗi "Cannot redeclare".
 * - Cung cấp csrfHopLe() và csrfKiemTraPost() để chuẩn bị bật
 *   bảo vệ tập trung sau khi các form POST / fetch đã gửi mã.
 *
 * KHÔNG gọi csrfKiemTraPost() ở mọi request trước khi cập nhật hết
 * các form và fetch POST: nếu làm vậy các tính năng cũ sẽ nhận 403.
 */

/**
 * Kiểm tra mã CSRF. Có thể truyền mã cụ thể (kể cả mảng để kiểm thử
 * phủ định); nếu không truyền, lấy mã từ form hoặc header fetch.
 */
function csrfHopLe(mixed $maGuiLen = null): bool
{
    $maSession = $_SESSION['csrf_token'] ?? null;

    if (!is_string($maSession) || $maSession === '') {
        return false;
    }

    if ($maGuiLen === null) {
        // Ưu tiên trường form đang được sử dụng trên từng trang hiện tại.
        // Nếu trường đã gửi là mảng, phải từ chối chứ không thử header.
        foreach (['csrf_token', '_token', '_csrf', 'csrf'] as $tenTruong) {
            if (array_key_exists($tenTruong, $_POST)) {
                $maGuiLen = $_POST[$tenTruong];
                break;
            }
        }

        if ($maGuiLen === null) {
            $maGuiLen = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
    }

    return is_string($maGuiLen)
        && $maGuiLen !== ''
        && hash_equals($maSession, $maGuiLen);
}

/** Xác định yêu cầu API/JSON để trả đúng kiểu phản hồi khi CSRF sai. */
function csrfLaYeuCauJson(): bool
{
    $duongDan = parse_url((string) ($_SERVER['SCRIPT_NAME'] ?? ''), PHP_URL_PATH);
    $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
    $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');

    return (is_string($duongDan) && preg_match('~(?:^|/)api/~', $duongDan) === 1)
        || stripos($contentType, 'application/json') !== false
        || stripos($accept, 'application/json') !== false;
}

/** Gửi phản hồi 403, không đưa mã/token/chi tiết nội bộ ra trình duyệt. */
function csrfTuChoi(): never
{
    http_response_code(403);
    header('Cache-Control: no-store');

    if (csrfLaYeuCauJson()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['loi' => 'Trang đã hết hạn hoặc mã xác thực không hợp lệ.'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="vi"><head><meta charset="UTF-8">'
           . '<title>403 - Mã xác thực không hợp lệ</title></head><body>'
           . '<h1>403 - Trang đã hết hạn</h1>'
           . '<p>Vui lòng tải lại trang và thử lần nữa.</p></body></html>';
    }

    exit;
}

/**
 * Chặn POST thiếu/sai mã CSRF (sẽ kích hoạt trong inc/config.php
 * khi mọi form POST và fetch đã được cập nhật).
 */
function csrfKiemTraPost(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return;
    }

    if (!csrfHopLe()) {
        csrfTuChoi();
    }
}
