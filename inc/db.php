<?php
declare(strict_types=1);

/**
 * TH7 - Ket noi MySQL bang file .env rieng tu.
 * Tu dong ho tro nhieu duong dan linh hoat tren may ca nhan.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    require_once dirname(__DIR__) . '/vendor/autoload.php';

    // Danh sach cac duong dan tiem nang chua file .env de phong thich nghi cao nhat
    $cacThuMucThu = [
        'D:' . DIRECTORY_SEPARATOR . 'qlcsdl' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'StyleHub_private' . DIRECTORY_SEPARATOR . 'security',
        'D:' . DIRECTORY_SEPARATOR . 'qlcsdl' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'StyleHub_private',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'StyleHub_private' . DIRECTORY_SEPARATOR . 'security',
        'D:' . DIRECTORY_SEPARATOR . 'StyleHub_private',
        dirname(__DIR__) // Thu muc goc du an (phong khi de .env tai goc)
    ];

    $thuMucCauHinh = null;
    $tepEnv = null;

    foreach ($cacThuMucThu as $thuMuc) {
        $duongDan = $thuMuc . DIRECTORY_SEPARATOR . '.env';
        if (is_file($duongDan) && is_readable($duongDan)) {
            $thuMucCauHinh = $thuMuc;
            $tepEnv = $duongDan;
            break;
        }
    }

    if (!$tepEnv || !$thuMucCauHinh) {
        throw new RuntimeException(
            'Khong tim thay hoac khong doc duoc file .env rieng tu.'
        );
    }

    \Dotenv\Dotenv::createImmutable($thuMucCauHinh)->load();

    $layBien = static function (string $ten): ?string {
        $giaTri = $_ENV[$ten] ?? $_SERVER[$ten] ?? null;

        return is_string($giaTri) ? $giaTri : null;
    };

    $host = trim((string) $layBien('DB_HOST'));
    $port = trim((string) $layBien('DB_PORT'));
    $name = trim((string) $layBien('DB_NAME'));
    $user = trim((string) $layBien('DB_USER'));
    $pass = $layBien('DB_PASSWORD');

    if (
        $host === '' ||
        $port === '' ||
        $name === '' ||
        $user === '' ||
        $pass === null ||
        $user === 'your_mysql_user' ||
        $pass === 'your_mysql_password'
    ) {
        throw new RuntimeException(
            'Cau hinh MySQL trong .env thieu hoac chua hop le.'
        );
    }

    if (
        !ctype_digit($port) ||
        (int) $port < 1 ||
        (int) $port > 65535
    ) {
        throw new RuntimeException(
            'DB_PORT trong .env khong hop le.'
        );
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}