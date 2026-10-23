USE stylehub_db;

-- 1. Tài khoản người dùng
CREATE TABLE IF NOT EXISTS nguoi_dung (
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

-- 2. Yêu cầu liên hệ
CREATE TABLE IF NOT EXISTS lien_he (
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

-- 3. Đơn hàng
CREATE TABLE IF NOT EXISTS don_hang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id BIGINT UNSIGNED NOT NULL,
    ho_ten_nhan VARCHAR(100) NOT NULL,
    so_dien_thoai_nhan VARCHAR(20) NOT NULL,
    dia_chi_nhan VARCHAR(255) NOT NULL,
    ghi_chu TEXT NULL,
    tong_tien BIGINT UNSIGNED NOT NULL DEFAULT 0,
    trang_thai ENUM(
        'cho_xac_nhan',
        'da_xac_nhan',
        'dang_giao',
        'hoan_thanh',
        'da_huy'
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

-- 4. Chi tiết đơn hàng: lưu giá tại thời điểm đặt mua
CREATE TABLE IF NOT EXISTS chi_tiet_don_hang (
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
    CONSTRAINT chk_ctdh_so_luong CHECK (so_luong > 0),
    INDEX idx_ctdh_san_pham (san_pham_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 5. Nhãn dùng để phân loại sản phẩm linh hoạt
CREATE TABLE IF NOT EXISTS nhan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng liên kết N-N giữa sản phẩm và nhãn
CREATE TABLE IF NOT EXISTS san_pham_nhan (
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