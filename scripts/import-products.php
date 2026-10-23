<?php
declare(strict_types=1);

$duongDanJson = __DIR__ . '/../data/san-pham.json';

if (!is_file($duongDanJson)) {
    exit("Khong tim thay data/san-pham.json\n");
}

$duLieu = json_decode(
    file_get_contents($duongDanJson),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=stylehub_db;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$timDanhMuc = $pdo->prepare(
    'SELECT id FROM danh_muc WHERE ten = :ten'
);

$themDanhMuc = $pdo->prepare(
    'INSERT INTO danh_muc (ten) VALUES (:ten)'
);

$themSanPham = $pdo->prepare(
    'INSERT INTO san_pham (
        id, ten, danh_muc_id, gia, hinh_anh,
        mo_ta, chat_lieu, mau_sac, kich_co, tinh_trang
    ) VALUES (
        :id, :ten, :danh_muc_id, :gia, :hinh_anh,
        :mo_ta, :chat_lieu, :mau_sac, :kich_co, :tinh_trang
    )
    ON DUPLICATE KEY UPDATE
        ten = VALUES(ten),
        danh_muc_id = VALUES(danh_muc_id),
        gia = VALUES(gia),
        hinh_anh = VALUES(hinh_anh),
        mo_ta = VALUES(mo_ta),
        chat_lieu = VALUES(chat_lieu),
        mau_sac = VALUES(mau_sac),
        kich_co = VALUES(kich_co),
        tinh_trang = VALUES(tinh_trang)'
);

$pdo->beginTransaction();

try {
    foreach ($duLieu as $sp) {
        $tenDanhMuc = trim((string) ($sp['danhMuc'] ?? ''));

        if ($tenDanhMuc === '') {
            throw new RuntimeException('San pham thieu danh muc.');
        }

        $timDanhMuc->execute(['ten' => $tenDanhMuc]);
        $danhMuc = $timDanhMuc->fetch();

        if (!$danhMuc) {
            $themDanhMuc->execute(['ten' => $tenDanhMuc]);
            $danhMucId = (int) $pdo->lastInsertId();
        } else {
            $danhMucId = (int) $danhMuc['id'];
        }

        $themSanPham->execute([
            'id' => (string) $sp['id'],
            'ten' => (string) $sp['ten'],
            'danh_muc_id' => $danhMucId,
            'gia' => (int) $sp['gia'],
            'hinh_anh' => (string) $sp['hinhAnh'],
            'mo_ta' => (string) $sp['moTa'],
            'chat_lieu' => (string) $sp['chatLieu'],
            'mau_sac' => (string) $sp['mauSac'],
            'kich_co' => (string) $sp['kichCo'],
            'tinh_trang' => (string) $sp['tinhTrang'],
        ]);
    }

    $pdo->commit();
    echo 'Da nhap/cap nhat ' . count($duLieu) . " san pham.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Loi: " . $e->getMessage() . "\n");
    exit(1);
}
