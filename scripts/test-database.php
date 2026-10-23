<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$pdo = App\Config\Database::connect();

$soLuong = $pdo->query(
    'SELECT COUNT(*) FROM san_pham'
)->fetchColumn();

echo "Ket noi MySQL thanh cong.\n";
echo "So san pham: {$soLuong}\n";
