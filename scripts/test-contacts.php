<?php

require __DIR__ . '/../vendor/autoload.php';

$kho = new App\Data\KhoLienHe();
$danhSach = $kho->tatCa();

echo 'So lien he: ', count($danhSach), PHP_EOL;
print_r(array_slice($danhSach, 0, 2));
