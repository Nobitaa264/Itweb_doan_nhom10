CREATE DATABASE ltweb_nhom10
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ltweb_nhom10;

-- 1. Danh mục sản phẩm
CREATE TABLE danh_muc (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL UNIQUE,
    mo_ta VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 2. Sản phẩm
CREATE TABLE san_pham (
    id VARCHAR(20) PRIMARY KEY,
    ten VARCHAR(255) NOT NULL,
    danh_muc_id INT UNSIGNED NOT NULL,
    gia INT UNSIGNED NOT NULL,
    hinh_anh VARCHAR(500) NOT NULL,
    mo_ta TEXT NOT NULL,
    chat_lieu VARCHAR(100) NOT NULL,
    mau_sac VARCHAR(255) NOT NULL,
    kich_co VARCHAR(255) NOT NULL,
    tinh_trang VARCHAR(50) NOT NULL DEFAULT 'Còn hàng',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_san_pham_danh_muc
        FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_san_pham_ten (ten),
    INDEX idx_san_pham_gia (gia),
    INDEX idx_san_pham_danh_muc (danh_muc_id),
    INDEX idx_san_pham_tinh_trang (tinh_trang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 3. Người dùng
CREATE TABLE nguoi_dung (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL,
    ten_dang_nhap VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    vai_tro ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 4. Yêu cầu liên hệ
CREATE TABLE lien_he (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    so_dien_thoai VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    san_pham_id VARCHAR(20) NULL,
    san_pham_ten VARCHAR(255) NULL,
    noi_dung TEXT NOT NULL,
    anh VARCHAR(255) NULL,
    trang_thai ENUM('moi', 'dang_xu_ly', 'da_xu_ly')
        NOT NULL DEFAULT 'moi',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lien_he_san_pham
        FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_lien_he_created_at (created_at),
    INDEX idx_lien_he_trang_thai (trang_thai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 5. Đơn hàng
CREATE TABLE don_hang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id BIGINT UNSIGNED NOT NULL,
    ho_ten_nhan VARCHAR(100) NOT NULL,
    so_dien_thoai_nhan VARCHAR(20) NOT NULL,
    dia_chi_nhan VARCHAR(255) NOT NULL,
    ghi_chu TEXT NULL,
    tong_tien BIGINT UNSIGNED NOT NULL DEFAULT 0,
    trang_thai ENUM(
        'cho_xac_nhan', 'da_xac_nhan', 'dang_giao',
        'hoan_thanh', 'da_huy'
    ) NOT NULL DEFAULT 'cho_xac_nhan',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_don_hang_nguoi_dung
        FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_don_hang_nguoi_dung (nguoi_dung_id),
    INDEX idx_don_hang_trang_thai (trang_thai),
    INDEX idx_don_hang_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 6. Chi tiết đơn hàng
CREATE TABLE chi_tiet_don_hang (
    don_hang_id BIGINT UNSIGNED NOT NULL,
    san_pham_id VARCHAR(20) NOT NULL,
    ten_san_pham VARCHAR(255) NOT NULL,
    gia_don_vi BIGINT UNSIGNED NOT NULL,
    so_luong INT UNSIGNED NOT NULL,
    thanh_tien BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (don_hang_id, san_pham_id),
    CONSTRAINT fk_ctdh_don_hang
        FOREIGN KEY (don_hang_id) REFERENCES don_hang(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ctdh_san_pham
        FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_ctdh_san_pham (san_pham_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 7. Nhãn sản phẩm
CREATE TABLE nhan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 8. Liên kết sản phẩm và nhãn (N-N)
CREATE TABLE san_pham_nhan (
    san_pham_id VARCHAR(20) NOT NULL,
    nhan_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (san_pham_id, nhan_id),
    CONSTRAINT fk_spn_san_pham
        FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_spn_nhan
        FOREIGN KEY (nhan_id) REFERENCES nhan(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_spn_nhan (nhan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
-- ==========================================
-- BỔ SUNG BẢNG SỔ LƯU BÚT (PHẦN B)
-- ==========================================
CREATE TABLE IF NOT EXISTS tv_luu_but (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv VARCHAR(20) NOT NULL,
    ten_nguoi_gui VARCHAR(100) NOT NULL,
    noi_dung TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tv_luu_but_thanh_vien FOREIGN KEY (mssv) 
        REFERENCES thanh_vien(mssv) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_tv_luu_but_mssv (mssv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng thành viên nhóm
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS thanh_vien (
    mssv VARCHAR(20) PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    nguoi_dung_id BIGINT UNSIGNED NULL,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng riêng cho Đạt: Lượt xem trang cá nhân
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS tv_luot_xem (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv VARCHAR(20) NOT NULL,
    ngay DATE NOT NULL,
    so_luot INT UNSIGNED DEFAULT 1,
    UNIQUE KEY uk_mssv_ngay (mssv, ngay),
    FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng riêng cho Đạt: Quản lý dự án cá nhân
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS tv_du_an (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv VARCHAR(20) NOT NULL,
    ten_du_an VARCHAR(255) NOT NULL,
    loai_du_an VARCHAR(50) NOT NULL, -- Web, App, AI, OpenSource
    mo_ta TEXT,
    lien_ket VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
