SET NAMES utf8mb4;
USE ltweb_nhom10;

-- 1. Thêm danh mục sản phẩm
INSERT INTO danh_muc (id, ten, mo_ta) VALUES
(1, 'Áo sơ mi', 'Các sản phẩm áo sơ mi công sở và dạo phố'),
(2, 'Quần Jeans', 'Các sản phẩm quần Jeans nam nữ thời trang'),
(3, 'Áo khoác', 'Các sản phẩm áo khoác ấm áp và cá tính'),
(4, 'Áo thun', 'Các sản phẩm áo thun Cotton năng động'),
(5, 'Đồ Lót & Vớ', 'Sản phẩm đồ lót và tất vớ cao cấp'),
(6, 'Đồ thể thao', 'Trang phục thể thao thoải mái, co giãn tốt'),
(7, 'Phụ kiện', 'Ví, thắt lưng, nón và phụ kiện thời trang')
ON DUPLICATE KEY UPDATE ten = VALUES(ten), mo_ta = VALUES(mo_ta);

-- 2. Thêm danh sách sản phẩm mẫu
INSERT INTO san_pham (id, ten, danh_muc_id, gia, hinh_anh, mo_ta, chat_lieu, mau_sac, kich_co, tinh_trang) VALUES
('SH001', 'Áo sơ mi Basic Trắng', 1, 299000, 'images/somi.jpg', 'Áo sơ mi Basic trắng vải cotton chống nhăn, kiểu dáng thanh lịch.', 'Cotton', 'Trắng', 'S, M, L, XL', 'Còn hàng'),
('SH002', 'Áo sơ mi Caro Oxford', 1, 320000, 'images/somi.jpg', 'Áo sơ mi họa tiết caro trẻ trung, chất vải Oxford dày dặn.', 'Oxford', 'Xanh Caro', 'M, L, XL', 'Còn hàng'),
('JE001', 'Quần Jeans Classic Xanh', 2, 399000, 'images/jeans.jpg', 'Quần Jeans ống đứng cổ điển, màu xanh denim chuẩn đẹp.', 'Denim', 'Xanh Denim', '29, 30, 31, 32', 'Còn hàng'),
('JE002', 'Quần Jeans Slimfit Đen', 2, 420000, 'images/jeans.jpg', 'Quần Jeans ôm dáng Slimfit màu đen tuyền cá tính.', 'Denim co giãn', 'Đen', '28, 29, 30, 31', 'Còn hàng'),
('AK001', 'Áo khoác Bomber Đen', 3, 499000, 'images/bomber.jpg', 'Áo khoác Bomber phong cách trẻ trung, giữ ấm tốt.', 'Dù 2 lớp', 'Đen', 'M, L, XL', 'Còn hàng'),
('AT001', 'Áo thun Cotton 100% Trắng', 4, 199000, 'images/tshirt.jpg', 'Áo thun cổ tròn chất cotton thấm hút mồ hôi cực tốt.', 'Cotton 100%', 'Trắng', 'S, M, L, XL', 'Còn hàng')
ON DUPLICATE KEY UPDATE ten = VALUES(ten);

-- 3. Bảng thành viên
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
('3120224028', 'Nguyễn Đình Minh Đạt', NULL),
('3120224062', 'Phạm Huy Hoàng', NULL),
('3120224123', 'Dương Quang Sơn', NULL),
('3120224170', 'Nguyễn Hữu Tuân', NULL) 
ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten);

-- 4. Dữ liệu mẫu TV_LUOT_XEM & TV_DU_AN của Phạm Huy Hoàng (3120224062)
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
('3120224062', CURDATE(), 20)
ON DUPLICATE KEY UPDATE so_luot = so_luot + 1;

INSERT INTO tv_du_an (mssv, ten_du_an, loai_du_an, mo_ta, link_demo) VALUES
('3120224062', 'Website Bán Quần Áo StyleHub', 'Web Fullstack', 'Xây dựng module trang cá nhân, sổ lưu bút, bộ đếm lượt xem và bảo mật.', 'http://localhost:8000/thanhvien/3120224062_phamhoang/gioithieu.php')
ON DUPLICATE KEY UPDATE ten_du_an = VALUES(ten_du_an);

-- 5. Dữ liệu mẫu Sổ Lưu Bút
INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung, created_at) VALUES 
('3120224062', 'Nguyễn Văn A', 'Trang cá nhân thiết kế rất đẹp và trực quan!', '2026-03-01 10:00:00'),
('3120224062', 'Trần Thị B', 'Chúc bạn hoàn thành xuất sắc đồ án nhóm nhé!', '2026-03-01 11:30:00');

-- 6. Dữ liệu mẫu Dương Quang Sơn (3120224123)
INSERT INTO tv_son_lich_hen (mssv, ho_ten, ngay_hen, gio_hen, noi_dung)
VALUES ('3120224123', 'Khách demo', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:00', 'Lịch mẫu kiểm thử dữ liệu cá nhân StyleHub')
ON DUPLICATE KEY UPDATE ho_ten = ho_ten;

INSERT INTO tv_son_trac_nghiem (mssv, bi_danh, so_cau_dung, tong_cau, diem, chi_tiet)
SELECT '3120224123', 'Khách demo', 4, 5, 8, '[]'
WHERE NOT EXISTS (
    SELECT 1 FROM tv_son_trac_nghiem
    WHERE mssv = '3120224123' AND bi_danh = 'Khách demo'
);