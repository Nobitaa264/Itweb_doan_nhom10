SET NAMES utf8mb4;
USE ltweb_nhom10;

-- 2. Thêm danh mục sản phẩm đầy đủ
INSERT INTO danh_muc (id, ten, mo_ta) VALUES
(1, 'Áo sơ mi', 'Các sản phẩm áo sơ mi công sở và dạo phố'),
(2, 'Quần Jeans', 'Các sản phẩm quần Jeans nam nữ thời trang'),
(3, 'Áo khoác', 'Các sản phẩm áo khoác ấm áp và cá tính'),
(4, 'Áo thun', 'Các sản phẩm áo thun Cotton năng động'),
(5, 'Đồ Lót & Vớ', 'Sản phẩm đồ lót và tất vớ cao cấp'),
(6, 'Đồ thể thao', 'Trang phục thể thao thoải mái, co giãn tốt'),
(7, 'Phụ kiện', 'Ví, thắt lưng, nón và phụ kiện thời trang')
ON DUPLICATE KEY UPDATE ten = VALUES(ten), mo_ta = VALUES(mo_ta);;

-- 3. Thêm danh sách 35 sản phẩm mẫu chuẩn UTF-8
INSERT INTO san_pham (id, ten, danh_muc_id, gia, hinh_anh, mo_ta, chat_lieu, mau_sac, kich_co, tinh_trang) VALUES
-- Áo sơ mi (Category 1)
('SH001', 'Áo sơ mi Basic Trắng', 1, 299000, 'images/somi.jpg', 'Áo sơ mi Basic trắng vải cotton chống nhăn, kiểu dáng thanh lịch.', 'Cotton', 'Trắng', 'S, M, L, XL', 'Còn hàng'),
('SH002', 'Áo sơ mi Caro Oxford', 1, 320000, 'images/somi.jpg', 'Áo sơ mi họa tiết caro trẻ trung, chất vải Oxford dày dặn.', 'Oxford', 'Xanh Caro', 'M, L, XL', 'Còn hàng'),
('SH003', 'Áo sơ mi Cổ Đứng Hàn Quốc', 1, 350000, 'images/somi.jpg', 'Áo sơ mi cổ Tàu phong cách Hàn Quốc tối giản.', 'Lụa đũi', 'Đen, Kem', 'S, M, L', 'Còn hàng'),
('SH004', 'Áo sơ mi Tay Ngắn Mùa Hè', 1, 270000, 'images/somi.jpg', 'Áo sơ mi ngắn tay thoáng mát phù hợp thời tiết mùa hè.', 'Linen', 'Xanh nhạt', 'M, L, XL', 'Còn hàng'),
('SH005', 'Áo sơ mi Denim Nam', 1, 380000, 'images/somi.jpg', 'Áo sơ mi chất liệu Denim cá tính, phong cách bụi bặm.', 'Denim', 'Xanh đậm', 'M, L', 'Còn hàng'),

-- Quần Jeans (Category 2)
('JE001', 'Quần Jeans Classic Xanh', 2, 399000, 'images/jeans.jpg', 'Quần Jeans ống đứng cổ điển, màu xanh denim chuẩn đẹp.', 'Denim', 'Xanh Denim', '29, 30, 31, 32', 'Còn hàng'),
('JE002', 'Quần Jeans Slimfit Đen', 2, 420000, 'images/jeans.jpg', 'Quần Jeans ôm dáng Slimfit màu đen tuyền cá tính.', 'Denim co giãn', 'Đen', '28, 29, 30, 31', 'Còn hàng'),
('JE003', 'Quần Jeans Rách Gối Style', 2, 450000, 'images/jeans.jpg', 'Quần Jeans rách gối nhẹ tạo điểm nhấn phá cách.', 'Denim', 'Xanh sáng', '29, 30, 31, 32', 'Còn hàng'),
('JE004', 'Quần Jeans ống Rộng Unisex', 2, 380000, 'images/jeans.jpg', 'Quần Jeans ống rộng mốt mới, mặc cực thoải mái.', 'Denim mềm', 'Xanh xám', 'S, M, L', 'Còn hàng'),
('JE005', 'Quần Jeans Short Mùa Hè', 2, 290000, 'images/jeans.jpg', 'Quần sọt Jeans lửng ngang gối năng động.', 'Denim', 'Xanh nhạt', '28, 29, 30, 31, 32', 'Còn hàng'),

-- Áo khoác (Category 3)
('AK001', 'Áo khoác Bomber Đen', 3, 499000, 'images/bomber.jpg', 'Áo khoác Bomber phong cách trẻ trung, giữ ấm tốt.', 'Dù 2 lớp', 'Đen', 'M, L, XL', 'Còn hàng'),
('AK002', 'Áo khoác Denim Jacket', 3, 549000, 'images/denim-jacket.jpg', 'Áo khoác Denim dày dặn, cúc kim loại bền chắc.', 'Denim', 'Xanh nhạt', 'M, L', 'Còn hàng'),
('AK003', 'Áo khoác Cardigan Wool', 3, 420000, 'images/bomber.jpg', 'Áo khoác len Cardigan nhẹ nhàng chuẩn phong cách Hàn Quốc.', 'Len mềm', 'Kem, Xám', 'Free Size', 'Còn hàng'),
('AK004', 'Áo khoác Gió Chống Nước', 3, 350000, 'images/bomber.jpg', 'Áo khoác gió thể thao chống nước và cản gió tốt.', 'Vải Dù', 'Xanh đen', 'M, L, XL', 'Còn hàng'),
('AK005', 'Áo khoác Hoodie Form Rộng', 3, 390000, 'images/bomber.jpg', 'Áo Hoodie có nón chất nỉ n bông ấm áp.', 'Nỉ n bông', 'Xám tiêu', 'S, M, L, XL', 'Còn hàng'),

-- Áo thun (Category 4)
('AT001', 'Áo thun Cotton 100% Trắng', 4, 199000, 'images/tshirt.jpg', 'Áo thun cổ tròn chất cotton thấm hút mồ hôi cực tốt.', 'Cotton 100%', 'Trắng', 'S, M, L, XL', 'Còn hàng'),
('AT002', 'Áo thun Oversize Đen', 4, 220000, 'images/tshirt.jpg', 'Áo thun dáng rộng Oversize chuẩn phong cách Streetwear.', 'Cotton 250gsm', 'Đen', 'M, L, XL', 'Còn hàng'),
('AT003', 'Áo thun Polo Thể Thao', 4, 280000, 'images/tshirt.jpg', 'Áo Polo có cổ lịch sự, vải cá sấu co giãn 4 chiều.', 'Polo CVC', 'Xanh bích', 'M, L, XL, XXL', 'Còn hàng'),
('AT004', 'Áo thun In In Graphic', 4, 240000, 'images/tshirt.jpg', 'Áo thun in hình Graphic cá tính ở mặt sau lưng.', 'Cotton', 'Trắng, Đen', 'M, L', 'Còn hàng'),
('AT005', 'Áo thun Ba Lỗ Gymmer', 4, 150000, 'images/tshirt.jpg', 'Áo ba lỗ ôm body tập gym thoải mái vận động.', 'Thun lạnh', 'Đen, Xám', 'M, L, XL', 'Còn hàng'),

-- Đồ Lót & Vớ (Category 5)
('DL001', 'Combo 3 Quần Lót Nam Boxer', 5, 180000, 'images/somi.jpg', 'Set 3 quần lót Boxer thun thun lạnh co giãn thông thoáng.', 'Thun lạnh', 'Đen, Xám, Xanh', 'M, L, XL', 'Còn hàng'),
('DL002', 'Combo 5 Đôi Vớ Cổ Cao', 5, 120000, 'images/somi.jpg', 'Combo vớ cổ cao thể thao khử mùi hiệu quả.', 'Cotton dệt', 'Trắng, Đen', 'Free Size', 'Còn hàng'),
('DL003', 'Quần Lót Tam Giác Seamless', 5, 65000, 'images/somi.jpg', 'Quần lót nam đúc nguyên khối không đường may.', 'Microfiber', 'Đen', 'L, XL', 'Còn hàng'),
('DL004', 'Combo 5 Đôi Vớ Lười', 5, 99000, 'images/somi.jpg', 'Vớ lười mang giày thể thao có silicon chống tuột gót.', 'Cotton', 'Nhiều màu', 'Free Size', 'Còn hàng'),
('DL005', 'Áo Lót Ba Lỗ Nam', 5, 85000, 'images/somi.jpg', 'Áo lót mặc trong áo sơ mi thấm hút mồ hôi.', 'Cotton 100%', 'Trắng', 'M, L, XL', 'Còn hàng'),

-- Đồ thể thao (Category 6)
('TT001', 'Bộ Đồ Thể Thao RunPro', 6, 380000, 'images/tshirt.jpg', 'Bộ quần áo thể thao chạy bộ vải thun co giãn chuyên dụng.', 'Polyester', 'Đen phối Đỏ', 'M, L, XL', 'Còn hàng'),
('TT002', 'Quần Short Thể Thao 2 Lớp', 6, 210000, 'images/jeans.jpg', 'Quần tập gym 2 lớp có túi đựng điện thoại tiện lợi.', 'Thun dù', 'Đen', 'M, L, XL', 'Còn hàng'),
('TT003', 'Áo Tập Gym Bodyfit', 6, 195000, 'images/tshirt.jpg', 'Áo thể thao ôm sát cơ thể hỗ trợ tập luyện thể thao.', 'Spandex', 'Xám', 'M, L', 'Còn hàng'),
('TT004', 'Quần Jogger Thể Thao Nỉ', 6, 290000, 'images/jeans.jpg', 'Quần Jogger bo gấu dây rút thể thao năng động.', 'Nỉ da cá', 'Đen, Xám đậm', 'M, L, XL', 'Còn hàng'),
('TT005', 'Bộ Quần Áo Đá Bóng', 6, 170000, 'images/tshirt.jpg', 'Trang phục thi đấu bóng đá vải thun lạnh thoáng khí.', 'Thun méo', 'Xanh lam', 'M, L, XL', 'Còn hàng'),

-- Phụ kiện (Category 7)
('PK001', 'Thắt Lưng Da Bò Cổ Điển', 7, 290000, 'images/somi.jpg', 'Thắt lưng da bò thật khóa kim loại chống gỉ.', 'Da bò', 'Nâu, Đen', 'Standard', 'Còn hàng'),
('PK002', 'Ví Da Nam Dáng Ngang', 7, 350000, 'images/somi.jpg', 'Ví da nam nhiều ngăn nhỏ gọn đựng thẻ và tiền mặt.', 'Da tổng hợp', 'Đen', 'Standard', 'Còn hàng'),
('PK003', 'Nón Lưỡi Trai StyleHub', 7, 150000, 'images/somi.jpg', 'Nón kết thêu logo StyleHub thô cứng phom đẹp.', 'Kaki', 'Đen, Trắng', 'Free Size', 'Còn hàng'),
('PK004', 'Mũ Bucket Hai Mặt', 7, 165000, 'images/somi.jpg', 'Mũ tai bèo Bucket đội được 2 mặt phối đồ cực chất.', 'Kaki', 'Đen/Vàng', 'Free Size', 'Còn hàng'),
('PK005', 'Kính Mát Thời Trang UV400', 7, 230000, 'images/somi.jpg', 'Kính mát chống tia UV400 bảo vệ mắt khi đi đường.', 'Gọng Nhựa/Chống UV', 'Đen huyền', 'Standard', 'Còn hàng');

-- Dữ liệu mẫu cho bảng lượt xem cá nhân (Đạt - 3120224028)
INSERT INTO tv_luot_xem (mssv, ngay, so_luot_xem) VALUES
('3120224028', CURDATE(), 25),
('3120224028', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 18),
('3120224028', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 12)
ON DUPLICATE KEY UPDATE so_luot_xem = VALUES(so_luot_xem);

INSERT INTO tv_du_an (mssv, ten_du_an, loai_du_an, mo_ta, nam_hoan_thanh) VALUES
('3120224028', 'Website Bán Hàng Thời Trang StyleHub', 'Web', 'Dự án bài tập nhóm môn Thiết kế và Lập trình Web.', 2026),
ON DUPLICATE KEY UPDATE ten_du_an = VALUES(ten_du_an);