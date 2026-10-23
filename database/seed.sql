SET NAMES utf8mb4;
USE ltweb_nhom10;

-- Thêm 2 dòng này để dọn sạch dữ liệu cũ bị lỗi font trước khi chèn mới
DELETE FROM san_pham;
DELETE FROM danh_muc;

-- Them danh muc san pham
INSERT INTO danh_muc (ten, mo_ta)
VALUES
    ('Áo sơ mi', 'Các sản phẩm áo sơ mi'),
    ('Quần Jeans', 'Các sản phẩm quần Jeans')
ON DUPLICATE KEY UPDATE
    mo_ta = VALUES(mo_ta);

-- Them san pham mau
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
-- ==========================================
-- BỔ SUNG DỮ LIỆU MẪU SỔ LƯU BÚT (PHẦN B)
-- ==========================================
INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung, created_at) 
VALUES 
    ('3120224062', 'Nguyễn Văn A', 'Trang cá nhân thiết kế rất đẹp và trực quan!', '2026-03-01 10:00:00'),
    ('3120224062', 'Trần Thị B', 'Chúc bạn hoàn thành xuất sắc đồ án nhóm nhé!', '2026-03-01 11:30:00');

-- Thêm thông tin thành viên Đạt
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) 
VALUES ('3120224028', 'Nguyễn Đình Minh Đạt', 1)
ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten);

-- Mẫu lượt xem 7 ngày gần nhất cho Đạt
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
('3120224028', CURDATE() - INTERVAL 6 DAY, 15),
('3120224028', CURDATE() - INTERVAL 5 DAY, 28),
('3120224028', CURDATE() - INTERVAL 4 DAY, 42),
('3120224028', CURDATE() - INTERVAL 3 DAY, 35),
('3120224028', CURDATE() - INTERVAL 2 DAY, 50),
('3120224028', CURDATE() - INTERVAL 1 DAY, 64),
('3120224028', CURDATE(), 80)
ON DUPLICATE KEY UPDATE so_luot = VALUES(so_luot);

-- Dữ liệu dự án cá nhân của Đạt
INSERT INTO tv_du_an (mssv, ten_du_an, loai_du_an, mo_ta, lien_ket) VALUES
('3120224028', 'Website Thương mại Điện tử StyleHub', 'Web', 'Dự án bài tập thực hành nhóm 10 xây dựng bằng PHP MVC & MySQL.', 'https://github.com/Nobitaa264/Itweb_doan_nhom10'),
