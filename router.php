<?php

declare(strict_types=1);

/**
 * TH7 - Router bao mat cho PHP Development Server.
 *
 * Chan truy cap truc tiep vao file va thu muc nhay cam.
 * Khong thay doi cac chuc nang PHP, CSS, JS va hinh anh.
 *
 * Chi su dung cho moi truong phat trien.
 */

function tuChoiTruyCap(): void
{
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');

    echo '403 Forbidden';
    exit;
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$duongDanGoc = parse_url($uri, PHP_URL_PATH);

if (!is_string($duongDanGoc)) {
    tuChoiTruyCap();
}

$duongDan = str_replace(
    '\\',
    '/',
    rawurldecode($duongDanGoc)
);

$thanhPhan = array_values(
    array_filter(
        explode('/', $duongDan),
        static fn (string $item): bool => $item !== ''
    )
);

// Chan duong dan an, dot-file, traversal va ky tu bat thuong.
foreach ($thanhPhan as $item) {
    if (
        str_starts_with($item, '.') ||
        str_contains($item, '%') ||
        str_contains($item, "\0") ||
        str_contains($item, ':')
    ) {
        tuChoiTruyCap();
    }
}

$thuMucDau = strtolower($thanhPhan[0] ?? '');
$tenFile = strtolower(end($thanhPhan) ?: '');

// Thu muc chi danh cho ma nguon va du lieu noi bo.
$thuMucCam = [
    'database',
    'inc',
    'includes',
    'src',
    'vendor',
    'storage',
    'logs',
    'scripts',
];

if (in_array($thuMucDau, $thuMucCam, true)) {
    tuChoiTruyCap();
}

// File cau hinh va quan ly phu thuoc.
$fileCam = [
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'router.php',
    'phpunit.xml',
    'phpunit.xml.dist',
];

if (in_array($tenFile, $fileCam, true)) {
    tuChoiTruyCap();
}

// Du lieu, file sao luu, khoa va file quan tri.
$duoiFileCam = [
    'sql',
    'zip',
    '7z',
    'rar',
    'bak',
    'old',
    'ini',
    'log',
    'lock',
    'pem',
    'key',
    'sqlite',
    'sqlite3',
    'db',
    'phar',
    'phtml',
    'ps1',
    'bat',
];

$duoiFile = strtolower(pathinfo($tenFile, PATHINFO_EXTENSION));

if (in_array($duoiFile, $duoiFileCam, true)) {
    tuChoiTruyCap();
}

// Khong cho thuc thi PHP trong thu muc tai nguyen.
$thuMucTaiNguyen = ['uploads', 'images', 'css', 'js'];

if (
    in_array($thuMucDau, $thuMucTaiNguyen, true) &&
    $duoiFile === 'php'
) {
    tuChoiTruyCap();
}

// Bao ve cac file bao cao kiem thu TXT tai thu muc goc.
if (count($thanhPhan) === 1 && $duoiFile === 'txt') {
    tuChoiTruyCap();
}

// Tai nguyen hop le: PHP, CSS, JS, hinh anh...
return false;
