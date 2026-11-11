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
     * TH6 – Phân trang quản trị: đếm tổng số sản phẩm trực tiếp trong MySQL.
     */
    public function dem(): int
    {
        $stmt = Database::connect()->query('SELECT COUNT(*) FROM san_pham');
        return (int) $stmt->fetchColumn();
    }

    /**
     * TH6 – Phân trang quản trị: chỉ truy vấn các sản phẩm của trang hiện tại.
     *
     * @return array<int, SanPham>
     */
    public function trang(int $soTrang, int $soMoiTrang = 10): array
    {
        $soTrang = max(1, $soTrang);
        $soMoiTrang = max(1, min(100, $soMoiTrang));
        $offset = ($soTrang - 1) * $soMoiTrang;

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
            INNER JOIN danh_muc dm ON dm.id = sp.danh_muc_id
            ORDER BY sp.id ASC
            LIMIT ? OFFSET ?
        ';

        $stmt = Database::connect()->prepare($sql);
        $stmt->bindValue(1, $soMoiTrang, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        $ketQua = [];
        foreach ($stmt->fetchAll() as $dong) {
            $dong['gia'] = (int) $dong['gia'];
            $ketQua[] = SanPham::tuMang($dong);
        }

        return $ketQua;
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
     * Đọc trực tiếp tồn kho và trạng thái mới nhất từ MySQL.
     * Không dùng danh sách tatCa() có thể đã được cache trong request.
     *
     * @return array{ton_kho: int, tinh_trang: string}|null
     */
    public function trangThaiTonKho(string $id): ?array
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        $stmt = Database::connect()->prepare(
            'SELECT ton_kho, tinh_trang FROM san_pham WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $dong = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($dong)) {
            return null;
        }

        return [
            'ton_kho' => max(0, (int) $dong['ton_kho']),
            'tinh_trang' => (string) $dong['tinh_trang'],
        ];
    }

    /**
     * Lấy tối đa 4 sản phẩm khác thuộc cùng danh mục với sản phẩm đang xem.
     * So sánh bằng ID danh mục trong CSDL, loại trừ sản phẩm hiện tại.
     * Nếu danh mục có ít hơn 5 sản phẩm thì kết quả có thể ít hơn 4.
     *
     * @return array<int, SanPham>
     */
    public function cungDanhMuc(string $idSanPham, int $gioiHan = 4): array
    {
        if (trim($idSanPham) === '') {
            return [];
        }

        $gioiHan = max(1, min(4, $gioiHan));

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
            INNER JOIN san_pham sp_goc
                ON sp_goc.danh_muc_id = sp.danh_muc_id
            INNER JOIN danh_muc dm
                ON dm.id = sp.danh_muc_id
            WHERE sp_goc.id = :id
              AND sp.id <> sp_goc.id
            ORDER BY sp.id ASC
            LIMIT :gioiHan
        ';

        $stmt = Database::connect()->prepare($sql);
        $stmt->bindValue(':id', $idSanPham, PDO::PARAM_STR);
        $stmt->bindValue(':gioiHan', $gioiHan, PDO::PARAM_INT);
        $stmt->execute();

        $ketQua = [];
        foreach ($stmt->fetchAll() as $dong) {
            $dong['gia'] = (int) $dong['gia'];
            $ketQua[] = SanPham::tuMang($dong);
        }

        return $ketQua;
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
     * Dùng chung các điều kiện lọc cho COUNT và SELECT.
     * Dấu %, _ và ! trong từ khóa là ký tự thường, không phải wildcard LIKE.
     * MySQL/MariaDB: ESCAPE '!' nên không phụ thuộc NO_BACKSLASH_ESCAPES.
     *
     * @return array{0: string, 1: array<string, int|string>}
     */
    private function dieuKienLoc(
        string $tuKhoa,
        string $danhMuc,
        ?int $giaToiDa
    ): array {
        $dieuKien = [];
        $thamSo = [];
        $tuKhoa = trim($tuKhoa);

        if ($tuKhoa !== '') {
            $daThoat = str_replace(
                ['!', '%', '_'],
                ['!!', '!%', '!_'],
                $tuKhoa
            );
            $mau = '%' . $daThoat . '%';
            $dieuKien[] = "(sp.id LIKE :timMa ESCAPE '!'
                OR sp.ten LIKE :timTen ESCAPE '!'
                OR dm.ten LIKE :timDanhMuc ESCAPE '!')";
            $thamSo['timMa'] = $mau;
            $thamSo['timTen'] = $mau;
            $thamSo['timDanhMuc'] = $mau;
        }

        if (
            $danhMuc !== 'tat-ca'
            && in_array($danhMuc, $this->danhMuc(), true)
        ) {
            $dieuKien[] = 'dm.ten = :danhMuc';
            $thamSo['danhMuc'] = $danhMuc;
        }

        if ($giaToiDa !== null && $giaToiDa >= 0) {
            $dieuKien[] = 'sp.gia <= :giaToiDa';
            $thamSo['giaToiDa'] = $giaToiDa;
        }

        return [
            $dieuKien ? ' WHERE ' . implode(' AND ', $dieuKien) : '',
            $thamSo,
        ];
    }

    private function thuTuSapXep(string $sapXep): string
    {
        // Không ghép trực tiếp ORDER BY từ tham số của người dùng.
        $duocPhep = [
            'mac-dinh' => 'sp.id ASC',
            'gia-tang' => 'sp.gia ASC, sp.id ASC',
            'gia-giam' => 'sp.gia DESC, sp.id ASC',
            'ten-a-z' => 'sp.ten ASC, sp.id ASC',
            'ten-z-a' => 'sp.ten DESC, sp.id ASC',
        ];

        return $duocPhep[$sapXep] ?? $duocPhep['mac-dinh'];
    }

    /**
     * Đếm số sản phẩm đúng với bộ lọc (không tính phân trang).
     */
    public function demTimKiem(
        string $tuKhoa = '',
        string $danhMuc = 'tat-ca',
        ?int $giaToiDa = null
    ): int {
        [$where, $thamSo] = $this->dieuKienLoc($tuKhoa, $danhMuc, $giaToiDa);
        $sql = 'SELECT COUNT(*) FROM san_pham sp'
            . ' INNER JOIN danh_muc dm ON dm.id = sp.danh_muc_id'
            . $where;

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($thamSo);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Tìm kiếm, lọc theo danh mục và giá tối đa, sắp xếp, phân trang.
     * Không thay đổi chữ ký timKiem() để API và script cũ vẫn chạy.
     *
     * @return array<int, SanPham>
     */
    public function timKiem(
        string $tuKhoa = '',
        string $danhMuc = 'tat-ca',
        string $sapXep = 'mac-dinh',
        ?int $giaToiDa = null,
        ?int $trangHienTai = null,
        int $soMoiTrang = 10
    ): array {
        [$where, $thamSo] = $this->dieuKienLoc($tuKhoa, $danhMuc, $giaToiDa);

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
            INNER JOIN danh_muc dm ON dm.id = sp.danh_muc_id
        ' . $where . ' ORDER BY ' . $this->thuTuSapXep($sapXep);

        if ($trangHienTai !== null) {
            $trangHienTai = max(1, $trangHienTai);
            $soMoiTrang = max(1, min(100, $soMoiTrang));
            $offset = ($trangHienTai - 1) * $soMoiTrang;
            $sql .= ' LIMIT :gioiHan OFFSET :boQua';
        }

        $stmt = Database::connect()->prepare($sql);
        foreach ($thamSo as $khoa => $giaTri) {
            $stmt->bindValue(
                ':' . $khoa,
                $giaTri,
                is_int($giaTri) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        if ($trangHienTai !== null) {
            $stmt->bindValue(':gioiHan', $soMoiTrang, PDO::PARAM_INT);
            $stmt->bindValue(':boQua', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();

        $ketQua = [];
        foreach ($stmt->fetchAll() as $dong) {
            $dong['gia'] = (int) $dong['gia'];
            $ketQua[] = SanPham::tuMang($dong);
        }
        return $ketQua;
    }
}
