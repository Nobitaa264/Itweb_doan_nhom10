<?php
/**
 * src/Data/KhoSanPham.php
 * Đọc và truy vấn dữ liệu sản phẩm từ data/san-pham.json.
 */

namespace App\Data;

use App\Models\SanPham;
use RuntimeException;

final class KhoSanPham
{
    /**
     * @var array<int, SanPham>|null
     */
    private ?array $duLieu = null;

    public function __construct(
        private readonly string $duongDanJson = __DIR__ . '/../../data/san-pham.json'
    ) {
    }

    /**
     * Đọc JSON đúng một nơi trong toàn bộ ứng dụng.
     *
     * @return array<int, SanPham>
     */
    public function tatCa(): array
    {
        if ($this->duLieu !== null) {
            return $this->duLieu;
        }

        if (!is_file($this->duongDanJson)) {
            throw new RuntimeException(
                'Không tìm thấy tệp dữ liệu sản phẩm.'
            );
        }

        $noiDung = file_get_contents($this->duongDanJson);

        if ($noiDung === false) {
            throw new RuntimeException(
                'Không thể đọc tệp dữ liệu sản phẩm.'
            );
        }

        try {
            $duLieuGoc = json_decode(
                $noiDung,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $ngoaiLe) {
            throw new RuntimeException(
                'Tệp dữ liệu sản phẩm không phải JSON hợp lệ.',
                previous: $ngoaiLe
            );
        }

        if (!is_array($duLieuGoc)) {
            throw new RuntimeException(
                'Dữ liệu sản phẩm không đúng cấu trúc.'
            );
        }

        $this->duLieu = [];

        foreach ($duLieuGoc as $muc) {
            if (!is_array($muc)) {
                continue;
            }

            $this->duLieu[] = SanPham::tuMang($muc);
        }

        return $this->duLieu;
    }

    /**
     * Tìm sản phẩm theo ID chuỗi như SH001, JE001...
     */
    public function timTheoId(string $id): ?SanPham
    {
        $id = trim($id);

        if ($id === '') {
            return null;
        }

        foreach ($this->tatCa() as $sanPham) {
            if ($sanPham->id === $id) {
                return $sanPham;
            }
        }

        return null;
    }

    /**
     * Trả về danh mục duy nhất.
     *
     * @return array<int, string>
     */
    public function danhMuc(): array
    {
        $ketQua = [];

        foreach ($this->tatCa() as $sanPham) {
            if (
                $sanPham->danhMuc !== '' &&
                !in_array($sanPham->danhMuc, $ketQua, true)
            ) {
                $ketQua[] = $sanPham->danhMuc;
            }
        }

        sort($ketQua);

        return $ketQua;
    }

    /**
     * Tìm kiếm, lọc và sắp xếp sản phẩm.
     *
     * @return array<int, SanPham>
     */
    public function timKiem(
        string $tuKhoa = '',
        string $danhMuc = 'tat-ca',
        string $sapXep = 'mac-dinh'
    ): array {
        $tuKhoa = trim($tuKhoa);

        $danhMucChoPhep = $this->danhMuc();

        if (
            $danhMuc !== 'tat-ca' &&
            !in_array($danhMuc, $danhMucChoPhep, true)
        ) {
            $danhMuc = 'tat-ca';
        }

        $sapXepChoPhep = [
            'mac-dinh',
            'gia-tang',
            'gia-giam',
            'ten-a-z',
            'ten-z-a',
        ];

        if (!in_array($sapXep, $sapXepChoPhep, true)) {
            $sapXep = 'mac-dinh';
        }

        $ketQua = array_filter(
            $this->tatCa(),
            static function (SanPham $sanPham) use (
                $tuKhoa,
                $danhMuc
            ): bool {
                if (
                    $danhMuc !== 'tat-ca' &&
                    $sanPham->danhMuc !== $danhMuc
                ) {
                    return false;
                }

                if ($tuKhoa === '') {
                    return true;
                }

                $noiDungTimKiem = implode(' ', [
                    $sanPham->id,
                    $sanPham->ten,
                    $sanPham->danhMuc,
                ]);

                return mb_stripos(
                    $noiDungTimKiem,
                    $tuKhoa,
                    0,
                    'UTF-8'
                ) !== false;
            }
        );

        $ketQua = array_values($ketQua);

        switch ($sapXep) {
            case 'gia-tang':
                usort(
                    $ketQua,
                    static fn (
                        SanPham $a,
                        SanPham $b
                    ): int => $a->gia <=> $b->gia
                );
                break;

            case 'gia-giam':
                usort(
                    $ketQua,
                    static fn (
                        SanPham $a,
                        SanPham $b
                    ): int => $b->gia <=> $a->gia
                );
                break;

            case 'ten-a-z':
                usort(
                    $ketQua,
                    static fn (
                        SanPham $a,
                        SanPham $b
                    ): int => strcoll($a->ten, $b->ten)
                );
                break;

            case 'ten-z-a':
                usort(
                    $ketQua,
                    static fn (
                        SanPham $a,
                        SanPham $b
                    ): int => strcoll($b->ten, $a->ten)
                );
                break;
        }

        return $ketQua;
    }
}