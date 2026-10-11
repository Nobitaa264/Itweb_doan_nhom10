<?php
/**
 * inc/config.php
 * Cấu hình chung cho toàn bộ website:
 * - Composer autoload
 * - Hàm tiện ích
 * - Môi trường chạy
 * - Báo lỗi và ghi log
 * - Session
 * - Xử lý ngoại lệ chưa được bắt
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ham.php';

const MOI_TRUONG = 'dev';

error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set(
    'error_log',
    __DIR__ . '/../logs/php-error.log'
);

if (MOI_TRUONG === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false
    ]);

    session_start();
}

$goc = './';
$trang = $trang ?? 'index';
$tieuDe = $tieuDe ?? 'StyleHub - Nhóm 10';

set_exception_handler(
    static function (Throwable $ngoaiLe): void {
        error_log(
            sprintf(
                "[EXCEPTION] %s: %s in %s:%d\n%s",
                get_class($ngoaiLe),
                $ngoaiLe->getMessage(),
                $ngoaiLe->getFile(),
                $ngoaiLe->getLine(),
                $ngoaiLe->getTraceAsString()
            )
        );

        http_response_code(500);

        if (MOI_TRUONG === 'prod') {
            require __DIR__ . '/../500.php';
        } else {
            echo '<!doctype html>';
            echo '<html lang="vi">';
            echo '<head><meta charset="UTF-8">';
            echo '<title>Lỗi máy chủ - StyleHub</title></head>';
            echo '<body>';
            echo '<h1>Lỗi máy chủ</h1>';
            echo '<p>Đã xảy ra ngoại lệ khi xử lý yêu cầu.</p>';
            echo '</body>';
            echo '</html>';
        }

        exit;
    }
);