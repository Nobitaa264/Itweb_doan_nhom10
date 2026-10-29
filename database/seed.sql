USE ltweb_nhom10;

-- Thêm danh mục sản phẩm mẫu
INSERT INTO danh_muc (ten, mo_ta)
VALUES
    ('Áo sơ mi', 'Các sản phẩm áo sơ mi'),
    ('Quần Jeans', 'Các sản phẩm quần Jeans')
ON DUPLICATE KEY UPDATE
    mo_ta = VALUES(mo_ta);

-- Thêm sản phẩm mẫu 1
INSERT INTO san_pham (
    id,
    ten,
    danh_muc_id,
    gia,
    hinh_anh,
    mo_ta,
    chat_lieu,
    mau_sac,
    kich_co,
    tinh_trang
)
SELECT
    'SH001',
    'Áo sơ mi Basic',
    dm.id,
    299000,
    'images/somi.jpg',
    'Áo sơ mi Basic sử dụng chất liệu cotton mềm mại, thoáng mát và dễ phối đồ.',
    'Cotton',
    'Trắng, đen, xanh',
    'S, M, L, XL',
    'Còn hàng'
FROM danh_muc AS dm
WHERE dm.ten = 'Áo sơ mi'
AND NOT EXISTS (
    SELECT 1 FROM san_pham WHERE id = 'SH001'
);

-- Thêm sản phẩm mẫu 2
INSERT INTO san_pham (
    id,
    ten,
    danh_muc_id,
    gia,
    hinh_anh,
    mo_ta,
    chat_lieu,
    mau_sac,
    kich_co,
    tinh_trang
)
SELECT
    'JE001',
    'Quần Jeans Classic',
    dm.id,
    399000,
    'images/quanjeans.jpg',
    'Quần Jeans Classic sử dụng chất liệu denim bền, kiểu dáng hiện đại và dễ phối với nhiều loại áo.',
    'Denim',
    'Xanh đậm, xanh nhạt, đen',
    '28, 29, 30, 31, 32',
    'Còn hàng'
FROM danh_muc AS dm
WHERE dm.ten = 'Quần Jeans'
AND NOT EXISTS (
    SELECT 1 FROM san_pham WHERE id = 'JE001'
);

-- ============================================================
-- DỮ LIỆU MẪU CÁ NHÂN: PHẠM HUY HOÀNG (3120224062)
-- ============================================================

-- 1. Thêm thành viên vào bảng thanh_vien
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) 
VALUES ('3120224062', 'Phạm Huy Hoàng', NULL)
ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten);

-- 2. Thêm dữ liệu dự án cá nhân vào bảng tv_du_an
INSERT INTO tv_du_an (mssv, ten_du_an, loai_du_an, mo_ta, link_demo) 
VALUES ('3120224062', 'Website Bán Quần Áo StyleHub', 'Fullstack Web', 'Xây dựng module trang cá nhân, sổ lưu bút, bộ đếm lượt xem và bảo mật chống XSS/SQL Injection.', 'http://localhost:8000/thanhvien/3120224062_phamhoang/gioithieu.php')
ON DUPLICATE KEY UPDATE ten_du_an = VALUES(ten_du_an);

-- 3. Thêm dữ liệu lượt xem vào bảng tv_luot_xem
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) 
VALUES ('3120224062', CURDATE(), 15)
ON DUPLICATE KEY UPDATE so_luot = so_luot + 1;

-- ==========================================
-- DỮ LIỆU MẪU SỔ LƯU BÚT (tv_luu_but)[cite: 5]
-- ==========================================
INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung, created_at) 
VALUES 
    ('3120224062', 'Nguyễn Văn A', 'Trang cá nhân thiết kế rất đẹp và trực quan!', '2026-03-01 10:00:00'),
    ('3120224062', 'Trần Thị B', 'Chúc bạn hoàn thành xuất sắc đồ án nhóm nhé!', '2026-03-01 11:30:00');