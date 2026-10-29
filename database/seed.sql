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
('SH003', 'Áo sơ mi Cổ Đứng Hàn Quốc', 1, 350000, 'images/somi.jpg', 'Áo sơ mi cổ Tàu phong cách Hàn Quốc tối giản.', 'Lụa đũi', 'Đen, Kem', 'S, M, L', 'Còn hàng'),
('SH004', 'Áo sơ mi Tay Ngắn Mùa Hè', 1, 270000, 'images/somi.jpg', 'Áo sơ mi ngắn tay thoáng mát phù hợp thời tiết mùa hè.', 'Linen', 'Xanh nhạt', 'M, L, XL', 'Còn hàng'),
('SH005', 'Áo sơ mi Denim Nam', 1, 380000, 'images/somi.jpg', 'Áo sơ mi chất liệu Denim cá tính, phong cách bụi bặm.', 'Denim', 'Xanh đậm', 'M, L', 'Còn hàng'),

('JE001', 'Quần Jeans Classic Xanh', 2, 399000, 'images/jeans.jpg', 'Quần Jeans ống đứng cổ điển, màu xanh denim chuẩn đẹp.', 'Denim', 'Xanh Denim', '29, 30, 31, 32', 'Còn hàng'),
('JE002', 'Quần Jeans Slimfit Đen', 2, 420000, 'images/jeans.jpg', 'Quần Jeans ôm dáng Slimfit màu đen tuyền cá tính.', 'Denim co giãn', 'Đen', '28, 29, 30, 31', 'Còn hàng'),
('JE003', 'Quần Jeans Rách Gối Style', 2, 450000, 'images/jeans.jpg', 'Quần Jeans rách gối nhẹ tạo điểm nhấn phá cách.', 'Denim', 'Xanh sáng', '29, 30, 31, 32', 'Còn hàng'),
('JE004', 'Quần Jeans ống Rộng Unisex', 2, 380000, 'images/jeans.jpg', 'Quần Jeans ống rộng mốt mới, mặc cực thoải mái.', 'Denim mềm', 'Xanh xám', 'S, M, L', 'Còn hàng'),
('JE005', 'Quần Jeans Short Mùa Hè', 2, 290000, 'images/jeans.jpg', 'Quần sọt Jeans lửng ngang gối năng động.', 'Denim', 'Xanh nhạt', '28, 29, 30, 31, 32', 'Còn hàng'),

('AK001', 'Áo khoác Bomber Đen', 3, 499000, 'images/bomber.jpg', 'Áo khoác Bomber phong cách trẻ trung, giữ ấm tốt.', 'Dù 2 lớp', 'Đen', 'M, L, XL', 'Còn hàng'),
('AK002', 'Áo khoác Denim Jacket', 3, 549000, 'images/denim-jacket.jpg', 'Áo khoác Denim dày dặn, cúc kim loại bền chắc.', 'Denim', 'Xanh nhạt', 'M, L', 'Còn hàng'),
('AK003', 'Áo khoác Cardigan Wool', 3, 420000, 'images/bomber.jpg', 'Áo khoác len Cardigan nhẹ nhàng chuẩn phong cách Hàn Quốc.', 'Len mềm', 'Kem, Xám', 'Free Size', 'Còn hàng'),
('AK004', 'Áo khoác Gió Chống Nước', 3, 350000, 'images/bomber.jpg', 'Áo khoác gió thể thao chống nước và cản gió tốt.', 'Vải Dù', 'Xanh đen', 'M, L, XL', 'Còn hàng'),
('AK005', 'Áo khoác Hoodie Form Rộng', 3, 390000, 'images/bomber.jpg', 'Áo Hoodie có nón chất nỉ n bông ấm áp.', 'Nỉ n bông', 'Xám tiêu', 'S, M, L, XL', 'Còn hàng'),

('AT001', 'Áo thun Cotton 100% Trắng', 4, 199000, 'images/tshirt.jpg', 'Áo thun cổ tròn chất cotton thấm hút mồ hôi cực tốt.', 'Cotton 100%', 'Trắng', 'S, M, L, XL', 'Còn hàng'),
('AT002', 'Áo thun Oversize Đen', 4, 220000, 'images/tshirt.jpg', 'Áo thun dáng rộng Oversize chuẩn phong cách Streetwear.', 'Cotton 250gsm', 'Đen', 'M, L, XL', 'Còn hàng'),
('AT003', 'Áo thun Polo Thể Thao', 4, 280000, 'images/tshirt.jpg', 'Áo Polo có cổ lịch sự, vải cá sấu co giãn 4 chiều.', 'Polo CVC', 'Xanh bích', 'M, L, XL, XXL', 'Còn hàng'),
('AT004', 'Áo thun In In Graphic', 4, 240000, 'images/tshirt.jpg', 'Áo thun in hình Graphic cá tính ở mặt sau lưng.', 'Cotton', 'Trắng, Đen', 'M, L', 'Còn hàng'),
('AT005', 'Áo thun Ba Lỗ Gymmer', 4, 150000, 'images/tshirt.jpg', 'Áo ba lỗ ôm body tập gym thoải mái vận động.', 'Thun lạnh', 'Đen, Xám', 'M, L, XL', 'Còn hàng'),

('DL002', 'Combo 5 Đôi Vớ Cổ Cao', 5, 120000, 'images/somi.jpg', 'Combo vớ cổ cao thể thao khử mùi hiệu quả.', 'Cotton dệt', 'Trắng, Đen', 'Free Size', 'Còn hàng'),
('DL004', 'Combo 5 Đôi Vớ Lười', 5, 99000, 'images/somi.jpg', 'Vớ lười mang giày thể thao có silicon chống tuột gót.', 'Cotton', 'Nhiều màu', 'Free Size', 'Còn hàng'),
('DL005', 'Áo Lót Ba Lỗ Nam', 5, 85000, 'images/somi.jpg', 'Áo lót mặc trong áo sơ mi thấm hút mồ hôi.', 'Cotton 100%', 'Trắng', 'M, L, XL', 'Còn hàng'),

('TT001', 'Bộ Đồ Thể Thao RunPro', 6, 380000, 'images/tshirt.jpg', 'Bộ quần áo thể thao chạy bộ vải thun co giãn chuyên dụng.', 'Polyester', 'Đen phối Đỏ', 'M, L, XL', 'Còn hàng'),
('TT002', 'Quần Short Thể Thao 2 Lớp', 6, 210000, 'images/jeans.jpg', 'Quần tập gym 2 lớp có túi đựng điện thoại tiện lợi.', 'Thun dù', 'Đen', 'M, L, XL', 'Còn hàng'),
('TT003', 'Áo Tập Gym Bodyfit', 6, 195000, 'images/tshirt.jpg', 'Áo thể thao ôm sát cơ thể hỗ trợ tập luyện thể thao.', 'Spandex', 'Xám', 'M, L', 'Còn hàng'),
('TT004', 'Quần Jogger Thể Thao Nỉ', 6, 290000, 'images/jeans.jpg', 'Quần Jogger bo gấu dây rút thể thao năng động.', 'Nỉ da cá', 'Đen, Xám đậm', 'M, L, XL', 'Còn hàng'),

('PK001', 'Thắt Lưng Da Bò Cổ Điển', 7, 290000, 'images/somi.jpg', 'Thắt lưng da bò thật khóa kim loại chống gỉ.', 'Da bò', 'Nâu, Đen', 'Standard', 'Còn hàng'),
('PK002', 'Ví Da Nam Dáng Ngang', 7, 350000, 'images/somi.jpg', 'Ví da nam nhiều ngăn nhỏ gọn đựng thẻ và tiền mặt.', 'Da tổng hợp', 'Đen', 'Standard', 'Còn hàng'),
('PK003', 'Nón Lưỡi Trai StyleHub', 7, 150000, 'images/somi.jpg', 'Nón kết thêu logo StyleHub thô cứng phom đẹp.', 'Kaki', 'Đen, Trắng', 'Free Size', 'Còn hàng'),
('PK004', 'Mũ Bucket Hai Mặt', 7, 165000, 'images/somi.jpg', 'Mũ tai bèo Bucket đội được 2 mặt phối đồ cực chất.', 'Kaki', 'Đen/Vàng', 'Free Size', 'Còn hàng'),
('PK005', 'Kính Mát Thời Trang UV400', 7, 230000, 'images/somi.jpg', 'Kính mát chống tia UV400 bảo vệ mắt khi đi đường.', 'Gọng Nhựa/Chống UV', 'Đen huyền', 'Standard', 'Còn hàng')
ON DUPLICATE KEY UPDATE ten = VALUES(ten);

-- 4. Bảng thành viên (Phần B - Đạt)
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
('3120224028', 'Nguyễn Đình Minh Đạt', NULL),
('3120224062', 'Phạm Hoàng', NULL),
('3120224123', 'Dương Quang Sơn', NULL),
('3120224170', 'Nguyễn Hữu Tuân', NULL) 
ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten);

-- ============================================================
-- PHẦN B (3120224028 - Nguyễn Đình Minh Đạt): DỮ LIỆU MẪU TV_LUOT_XEM
-- ============================================================
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
('3120224028', CURDATE(), 25),
('3120224028', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 18),
('3120224028', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 12)
ON DUPLICATE KEY UPDATE so_luot = VALUES(so_luot);

-- ============================================================
-- PHẦN B (3120224028 - Nguyễn Đình Minh Đạt): DỮ LIỆU MẪU TV_DU_AN
-- ============================================================
INSERT INTO tv_du_an (mssv, ten_du_an, loai_du_an, mo_ta, link_dem) VALUES
('3120224028', 'Website thương mại điện tử StyleHub', 'Web', 'Dự án bài tập lớn môn Lập trình Web. Xây dựng trang bán hàng thời trang sử dụng PHP PDO, MySQL, REST API giỏ hàng và trang quản trị Admin.', 'https://github.com/Nobitaa264/ltweb_doan_nhom10'),
('3120224028', 'Ứng dụng di động Quản lý chi tiêu cá nhân', 'App Mobile', 'Xây dựng ứng dụng di động hỗ trợ theo dõi thu chi hàng ngày, lập kế hoạch tài chính và xuất báo cáo biểu đồ trực quan.', ''),
('3120224028', 'Hệ thống AI phân loại hình ảnh thời trang', 'AI / Trí tuệ nhân tạo', 'Ứng dụng mô hình học máy (Machine Learning) để nhận diện và phân loại tự động danh mục quần áo từ hình ảnh người dùng tải lên.', '')
ON DUPLICATE KEY UPDATE ten_du_an = VALUES(ten_du_an);

-- ============================================================
-- PHẦN CÁ NHÂN CỦA PHẠM HOÀNG (3120224062): DỮ LIỆU SỔ LƯU BÚT
-- ============================================================
INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, noi_dung, created_at) VALUES 
('3120224062', 'Nguyễn Văn A', 'Trang cá nhân thiết kế rất đẹp và trực quan!', '2026-03-01 10:00:00'),
('3120224062', 'Trần Thị B', 'Chúc bạn hoàn thành xuất sắc đồ án nhóm nhé!', '2026-03-01 11:30:00');

-- 7. Phần B cá nhân: Dương Quang Sơn (MSSV 3120224123)
INSERT INTO tv_son_lich_hen (mssv, ho_ten, ngay_hen, gio_hen, noi_dung)
VALUES ('3120224123', 'Khách demo', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:00',
        'Lịch mẫu kiểm thử dữ liệu cá nhân StyleHub')
ON DUPLICATE KEY UPDATE ho_ten = ho_ten;

INSERT INTO tv_son_trac_nghiem (mssv, bi_danh, so_cau_dung, tong_cau, diem, chi_tiet)
SELECT '3120224123', 'Khách demo', 4, 5, 8, '[]'
WHERE NOT EXISTS (
    SELECT 1 FROM tv_son_trac_nghiem
    WHERE mssv = '3120224123' AND bi_danh = 'Khách demo'
);