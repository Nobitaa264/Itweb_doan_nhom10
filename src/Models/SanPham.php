<?php
/**
 * src/Models/SanPham.php
 * Mô hình dữ liệu của một sản phẩm StyleHub.
 */

namespace App\Models;

use InvalidArgumentException;

final readonly class SanPham
{
    public function __construct(
        public string $id,
        public string $ten,
        public int $gia,
        public string $danhMuc,
        public string $hinhAnh,
        public string $moTa,
        public string $chatLieu,
        public string $mauSac,
        public string $kichCo,
        public string $tinhTrang
    ) {
    }

    /**
     * Tạo đối tượng SanPham từ một phần tử JSON.
     */
    public static function tuMang(array $duLieu): self
    {
        $cacTruongBatBuoc = [
            'id',
            'ten',
            'danhMuc',
            'gia',
            'hinhAnh',
            'moTa',
            'chatLieu',
            'mauSac',
            'kichCo',
            'tinhTrang',
        ];

        foreach ($cacTruongBatBuoc as $truong) {
            if (!array_key_exists($truong, $duLieu)) {
                throw new InvalidArgumentException(
                    "Thiếu trường dữ liệu sản phẩm: {$truong}"
                );
            }
        }

        $id = trim((string) $duLieu['id']);
        $ten = trim((string) $duLieu['ten']);
        $gia = filter_var(
            $duLieu['gia'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        if ($id === '') {
            throw new InvalidArgumentException('ID sản phẩm không hợp lệ.');
        }

        if ($ten === '') {
            throw new InvalidArgumentException('Tên sản phẩm không hợp lệ.');
        }

        if ($gia === false) {
            throw new InvalidArgumentException('Giá sản phẩm không hợp lệ.');
        }

        return new self(
            id: $id,
            ten: $ten,
            gia: $gia,
            danhMuc: trim((string) $duLieu['danhMuc']),
            hinhAnh: trim((string) $duLieu['hinhAnh']),
            moTa: trim((string) $duLieu['moTa']),
            chatLieu: trim((string) $duLieu['chatLieu']),
            mauSac: trim((string) $duLieu['mauSac']),
            kichCo: trim((string) $duLieu['kichCo']),
            tinhTrang: trim((string) $duLieu['tinhTrang'])
        );
    }
}