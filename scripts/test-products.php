<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham();

echo "Tong san pham: " . count($kho->tatCa()) . PHP_EOL;

echo PHP_EOL . "Danh muc:" . PHP_EOL;
foreach ($kho->danhMuc() as $danhMuc) {
    echo "- {$danhMuc}" . PHP_EOL;
}

echo PHP_EOL . "Tim san pham SH001:" . PHP_EOL;
$sanPham = $kho->timTheoId('SH001');

if ($sanPham !== null) {
    echo $sanPham->ten . ' - ' . $sanPham->gia . PHP_EOL;
} else {
    echo "Khong tim thay san pham." . PHP_EOL;
}

echo PHP_EOL . "Tim kiem tu khoa Ao:" . PHP_EOL;
foreach ($kho->timKiem('Ao') as $sanPham) {
    echo $sanPham->id . ' - ' . $sanPham->ten . PHP_EOL;
}

echo PHP_EOL . "Sap xep gia tang:" . PHP_EOL;
foreach ($kho->timKiem('', 'tat-ca', 'gia-tang') as $sanPham) {
    echo $sanPham->ten . ' - ' . $sanPham->gia . PHP_EOL;
}
