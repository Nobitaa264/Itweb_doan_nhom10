
USE ltweb_nhom10;
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
