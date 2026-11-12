
-- =====================================================
-- TAI KHOAN MYSQL QUYEN TOI THIEU
-- Database: ltweb_nhom10
-- Tai khoan: nhom10_app
-- Host: 127.0.0.1
-- =====================================================

-- 1. Tao tai khoan ung dung
CREATE USER IF NOT EXISTS 'nhom10_app'@'127.0.0.1'
IDENTIFIED BY 'THAY_BANG_MAT_KHAU_MANH';
-- 2. Xoa cac quyen cu neu tai khoan da ton tai
REVOKE ALL PRIVILEGES, GRANT OPTION
FROM 'nhom10_app'@'127.0.0.1';

-- 3. Chi cap quyen thao tac du lieu
GRANT SELECT, INSERT, UPDATE, DELETE
ON `ltweb_nhom10`.*
TO 'nhom10_app'@'127.0.0.1';

-- 4. Kiem tra quyen tai khoan
SHOW GRANTS FOR 'nhom10_app'@'127.0.0.1';

-- =====================================================
-- KET QUA MONG DOI:
-- SELECT, INSERT, UPDATE, DELETE: DUOC PHEP
-- CREATE, DROP, ALTER: KHONG DUOC PHEP
-- =====================================================
