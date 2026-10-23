<?php
declare(strict_types=1);

namespace App\Data;

use App\Config\Database;
use App\Models\SanPham;
use PDO;

final class KhoSanPham
{
    private ?array $duLieu = null;

    public function __construct()
    {
    }

    /**
     * Lấy toàn bộ sản phẩm từ MySQL.
     *
     * @return array<int, SanPham>
     */
    public function tatCa(): array
    {
        if ($this->duLieu !== null) {
            return $this->duLieu;
        }

        $sql = '
            SELECT
                sp.id,
                sp.ten,
                sp.gia,
                dm.ten AS danhMuc,
                sp.hinh_anh AS hinhAnh,
                sp.mo_ta AS moTa,
                sp.chat_lieu AS chatLieu,
                sp.mau_sac AS mauSac,
                sp.kich_co AS kichCo,
                sp.tinh_trang AS tinhTrang
            FROM san_pham sp
            INNER JOIN danh_muc dm
                ON dm.id = sp.danh_muc_id
            ORDER BY sp.id
        ';

        $stmt = Database::connect()->query($sql);
        $this->duLieu = [];

        foreach ($stmt->fetchAll() as $dong) {
            $dong['gia'] = (int) $dong['gia'];
            $this->duLieu[] = SanPham::tuMang($dong);
        }

        return $this->duLieu;
    }

    /**
     * Tìm sản phẩm theo mã.
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
     * Lấy danh sách tên danh mục không trùng nhau.
     *
     * @return array<int, string>
     */
    public function danhMuc(): array
    {
        $sql = '
            SELECT ten
            FROM danh_muc
            ORDER BY ten
        ';

        $stmt = Database::connect()->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

        $cacCotSapXep = [
            'mac-dinh' => 'sp.id ASC',
            'gia-tang' => 'sp.gia ASC',
            'gia-giam' => 'sp.gia DESC',
            'ten-a-z' => 'sp.ten ASC',
            'ten-z-a' => 'sp.ten DESC',
        ];

        if (!isset($cacCotSapXep[$sapXep])) {
            $sapXep = 'mac-dinh';
        }

        $sql = '
            SELECT
                sp.id,
                sp.ten,
                sp.gia,
                dm.ten AS danhMuc,
                sp.hinh_anh AS hinhAnh,
                sp.mo_ta AS moTa,
                sp.chat_lieu AS chatLieu,
                sp.mau_sac AS mauSac,
                sp.kich_co AS kichCo,
                sp.tinh_trang AS tinhTrang
            FROM san_pham sp
            INNER JOIN danh_muc dm
                ON dm.id = sp.danh_muc_id
            WHERE (
                :tuKhoaRong = 1
                OR sp.id LIKE :tuKhoaId
                OR sp.ten LIKE :tuKhoaTen
                OR dm.ten LIKE :tuKhoaDanhMuc
            )
        ';

        $thamSo = [
            'tuKhoaRong' => $tuKhoa === '' ? 1 : 0,
            'tuKhoaId' => '%' . $tuKhoa . '%',
            'tuKhoaTen' => '%' . $tuKhoa . '%',
            'tuKhoaDanhMuc' => '%' . $tuKhoa . '%',
        ];

        if ($danhMuc !== 'tat-ca') {
            $sql .= ' AND dm.ten = :danhMuc';
            $thamSo['danhMuc'] = $danhMuc;
        }

        $sql .= ' ORDER BY ' . $cacCotSapXep[$sapXep];

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($thamSo);

        $ketQua = [];

        foreach ($stmt->fetchAll() as $dong) {
            $dong['gia'] = (int) $dong['gia'];
            $ketQua[] = SanPham::tuMang($dong);
        }

        return $ketQua;
    }
}
