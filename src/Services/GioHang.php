<?php
/**
 * src/Services/GioHang.php
 * Quản lý giỏ hàng trong $_SESSION['gio'].
 */

namespace App\Services;

use App\Data\KhoSanPham;
use DomainException;

final class GioHang
{
    private const TEN_SESSION = 'gio';

    private const SO_LUONG_TOI_THIEU = 1;

    private const SO_LUONG_TOI_DA = 99;

    public function __construct()
    {
        if (!isset($_SESSION[self::TEN_SESSION])) {
            $_SESSION[self::TEN_SESSION] = [];
        }

        if (!is_array($_SESSION[self::TEN_SESSION])) {
            $_SESSION[self::TEN_SESSION] = [];
        }
    }

    /**
     * Thêm sản phẩm vào giỏ.
     */
    public function them(
    string $id,
    KhoSanPham $kho,
    int $soLuong = 1
): void {
        $id = trim($id);

        if ($id === '') {
            throw new DomainException(
                'ID sản phẩm không được để trống.'
            );
        }

        if (
            $soLuong < self::SO_LUONG_TOI_THIEU ||
            $soLuong > self::SO_LUONG_TOI_DA
        ) {
            throw new DomainException(
                'Số lượng phải từ 1 đến 99.'
            );
        }

        if ($kho->timTheoId($id) === null) {
            throw new DomainException(
                'Sản phẩm không tồn tại.'
            );
        }

        $soLuongCu = (int) (
            $_SESSION[self::TEN_SESSION][$id] ?? 0
        );

        $soLuongMoi = $soLuongCu + $soLuong;

        if ($soLuongMoi > self::SO_LUONG_TOI_DA) {
            throw new DomainException(
                'Số lượng sản phẩm trong giỏ vượt quá 99.'
            );
        }

        $_SESSION[self::TEN_SESSION][$id] = $soLuongMoi;
    }

    /**
     * Cập nhật số lượng một sản phẩm.
     */
    public function capNhat(
        string $id,
        int $soLuong,
        KhoSanPham $kho
    ): void {
        $id = trim($id);

        if ($kho->timTheoId($id) === null) {
            throw new DomainException(
                'Sản phẩm không tồn tại.'
            );
        }

        if (
            $soLuong < self::SO_LUONG_TOI_THIEU ||
            $soLuong > self::SO_LUONG_TOI_DA
        ) {
            throw new DomainException(
                'Số lượng phải từ 1 đến 99.'
            );
        }

        $_SESSION[self::TEN_SESSION][$id] = $soLuong;
    }

    /**
     * Xóa một sản phẩm khỏi giỏ.
     */
    public function xoa(string $id): void
    {
        unset($_SESSION[self::TEN_SESSION][$id]);
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     */
    public function xoaHet(): void
    {
        $_SESSION[self::TEN_SESSION] = [];
    }

    /**
     * Tổng số món đang có trong giỏ.
     */
    public function soMon(): int
    {
        $tong = 0;

        foreach ($_SESSION[self::TEN_SESSION] as $soLuong) {
            $tong += max(0, (int) $soLuong);
        }

        return $tong;
    }

    /**
     * Tính tổng tiền dựa trên giá trong kho dữ liệu.
     * Không lấy giá từ form.
     */
    public function tongTien(KhoSanPham $kho): int
    {
        $tong = 0;

        foreach ($_SESSION[self::TEN_SESSION] as $id => $soLuong) {
            $sanPham = $kho->timTheoId((string) $id);

            if ($sanPham === null) {
                continue;
            }

            $tong += $sanPham->gia * (int) $soLuong;
        }

        return $tong;
    }

    /**
     * Lấy dữ liệu giỏ hàng để hiển thị.
     *
     * @return array<int, array{
     *     sanPham: \App\Models\SanPham,
     *     soLuong: int,
     *     thanhTien: int
     * }>
     */
    public function cacMuc(KhoSanPham $kho): array
    {
        $ketQua = [];

        foreach ($_SESSION[self::TEN_SESSION] as $id => $soLuong) {
            $sanPham = $kho->timTheoId((string) $id);

            if ($sanPham === null) {
                continue;
            }

            $soLuongHopLe = max(
                self::SO_LUONG_TOI_THIEU,
                min(
                    self::SO_LUONG_TOI_DA,
                    (int) $soLuong
                )
            );

            $ketQua[] = [
                'sanPham' => $sanPham,
                'soLuong' => $soLuongHopLe,
                'thanhTien' => $sanPham->gia * $soLuongHopLe,
            ];
        }

        return $ketQua;
    }
}