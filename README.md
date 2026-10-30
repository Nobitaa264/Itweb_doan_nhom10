# StyleHub – Website Bán Hàng Thời Trang (Nhóm 10)

Website bán hàng thời trang trực tuyến phục vụ học phần Thiết kế và Lập trình Web. Dự án sử dụng PHP thuần xử lý phía máy chủ (kết nối MySQL/MariaDB qua PDO), cấu trúc CSDL chuẩn 3NF (mã hóa `utf8mb4`), tích hợp REST API và JavaScript xử lý tương tác bất đồng bộ.

---

## 1. Yêu cầu môi trường

| Thành phần          | Yêu cầu                                                        |
| :------------------ | :------------------------------------------------------------- |
| **PHP**             | PHP ≥ 8.1 (bật extension `pdo_mysql`)                          |
| **MySQL / MariaDB** | MySQL 8.0+ hoặc MariaDB 10.4+ (đi kèm XAMPP/Laragon)           |
| **Composer**        | Composer 2.x                                                   |
| **Công cụ hỗ trợ**  | VS Code (Extension _REST Client_), Command Prompt / PowerShell |

---

## 2. Cài đặt và khởi chạy dự án

### Bước 1: Mở thư mục dự án

Mở PowerShell/Terminal tại thư mục gốc của dự án:

```powershell
cd C:\xampp\htdocs\Itweb
```

### Bước 2: Cài đặt phụ thuộc Composer

```powershell
composer install
```

### Bước 3: Khởi tạo Cơ sở dữ liệu `ltweb_nhom10`

Khởi động MySQL/MariaDB (qua XAMPP Control Panel hoặc dịch vụ hệ thống).

Thực thi 2 tệp SQL theo đúng thứ tự để tạo mới CSDL và nạp dữ liệu mẫu (Sử dụng **phpMyAdmin** hoặc chạy lệnh trong **Terminal**):

- **Cách 1: Chạy bằng Command Prompt (cmd / PowerShell):**

  ```powershell
  C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < database/schema.sql
  C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 ltweb_nhom10 < database/seed.sql
  ```

- **Cách 2: Import bằng phpMyAdmin:**
  1. Truy cập `http://localhost/phpmyadmin`
  2. Vào thẻ **Import** $\rightarrow$ Chọn tệp `database/schema.sql` $\rightarrow$ Bấm **Import**.
  3. Chọn CSDL `ltweb_nhom10` $\rightarrow$ Vào thẻ **Import** $\rightarrow$ Chọn tệp `database/seed.sql` $\rightarrow$ Bấm **Import**.

### Bước 4: Chạy Server phát triển PHP

Tại thư mục gốc dự án, chạy lệnh:

```powershell
php -S localhost:8000
```

Mở trình duyệt truy cập: **`http://localhost:8000`**

---

## 3. Tài khoản thử nghiệm

Toàn bộ tài khoản mẫu đã được tạo sẵn trong tệp `database/seed.sql` với mật khẩu chung là `123456`:

| Vai trò           | Email đăng nhập       | Mật khẩu | Tên người dùng | Chức năng kiểm thử                                     |
| :---------------- | :-------------------- | :------- | :------------- | :----------------------------------------------------- |
| **Quản trị viên** | `admin@stylehub.com`  | `123456` | Quản trị viên  | Quản lý sản phẩm (`quan-tri.php`), xem tất cả đơn hàng |
| **Khách hàng 1**  | `khach1@stylehub.com` | `123456` | Nguyễn Văn An  | Đặt hàng, xem đơn hàng cá nhân (`don-hang.php`)        |
| **Khách hàng 2**  | `khach2@stylehub.com` | `123456` | Trần Thị Bình  | Đặt hàng, xem đơn hàng cá nhân (`don-hang.php`)        |

---

## 4. Bảng Chức năng – URL – Tệp PHP

### 4.1. Trang giao diện và chức năng chính (Phần A)

| TT  | Chức năng                                               | URL                                | Tệp PHP xử lý                   |
| :-: | :------------------------------------------------------ | :--------------------------------- | :------------------------------ |
|  1  | Trang chủ                                               | `/` hoặc `/index.php`              | `index.php`                     |
|  2  | Danh sách sản phẩm (Lọc, tìm kiếm, sắp xếp, phân trang) | `/danh-sach.php`                   | `danh-sach.php`                 |
|  3  | Chi tiết sản phẩm                                       | `/chi-tiet.php?id=SH001`           | `chi-tiet.php`                  |
|  4  | Giỏ hàng                                                | `/gio-hang.php`                    | `gio-hang.php`                  |
|  5  | Gửi liên hệ / Phản hồi                                  | `/lien-he.php`                     | `lien-he.php`                   |
|  6  | Đăng ký tài khoản                                       | `/dang-ky.php`                     | `dang-ky.php`                   |
|  7  | Đăng nhập hệ thống                                      | `/dang-nhap.php`                   | `dang-nhap.php`                 |
|  8  | Đăng xuất                                               | `/dang-xuat.php`                   | `dang-xuat.php`                 |
|  9  | Đặt hàng từ giỏ (Giao dịch Transaction)                 | `/dat-hang.php`                    | `dat-hang.php`                  |
| 10  | Danh sách đơn hàng cá nhân                              | `/don-hang.php`                    | `don-hang.php`                  |
| 11  | Trang quản trị sản phẩm                                 | `/quan-tri.php`                    | `quan-tri.php`                  |
| 12  | Thêm sản phẩm mới                                       | `/them-san-pham.php`               | `them-san-pham.php`             |
| 13  | Sửa sản phẩm                                            | `/sua-san-pham.php?id=SH001`       | `sua-san-pham.php`              |
| 14  | Xóa sản phẩm (Chỉ POST)                                 | `/xoa-san-pham.php`                | `xoa-san-pham.php`              |
| 15  | Trang báo lỗi 403 / 404 / 500                           | `/403.php`, `/404.php`, `/500.php` | `403.php`, `404.php`, `500.php` |

### 4.2. Thành phần cấu trúc dùng chung

| Tệp dự kiến               | Vai trò kỹ thuật                                                                          |
| :------------------------ | :---------------------------------------------------------------------------------------- |
| `inc/config.php`          | Nạp cấu hình ứng dụng, môi trường (`MOI_TRUONG = 'dev'`), ghi log lỗi và khởi tạo Session |
| `inc/db.php`              | Hàm `db()` khởi tạo kết nối PDO với CSDL `ltweb_nhom10` chuẩn mã hóa `utf8mb4`            |
| `inc/xac-thuc.php`        | Quản lý hàm `nguoiDung()`, `yeuCauQuyen()`, phân quyền và xác thực Session                |
| `inc/kiem-tra.php`        | Hàm dùng chung `kiemTraSanPham()` validate dữ liệu thêm/sửa sản phẩm                      |
| `src/Data/KhoSanPham.php` | Lớp truy cập dữ liệu (Data Access Layer) chứa tất cả câu lệnh SQL xử lý sản phẩm          |

---

## 5. Bảng danh mục Endpoint REST API

Các API phản hồi định dạng JSON công khai, sử dụng tệp `api.http` để kiểm thử trong VS Code:

| STT | Phương thức      | Endpoint                     | Chức năng                                      | Mã HTTP Response                            |
| :-: | :--------------- | :--------------------------- | :--------------------------------------------- | :------------------------------------------ |
|  1  | `GET`            | `/api/san-pham.php`          | Lấy danh sách sản phẩm dạng JSON               | `200 OK`                                    |
|  2  | `GET`            | `/api/san-pham.php?q=somi`   | Tìm kiếm tức thời sản phẩm theo từ khóa        | `200 OK`                                    |
|  3  | `GET`            | `/api/san-pham.php?id=SH001` | Lấy chi tiết một sản phẩm theo ID              | `200 OK`, `404 Not Found`                   |
|  4  | `POST`           | `/api/gio-hang.php`          | Thêm sản phẩm vào giỏ hàng không tải lại trang | `200 OK`, `400`, `422 Unprocessable Entity` |
|  5  | `DELETE` / `PUT` | `/api/gio-hang.php`          | Từ chối phương thức không hỗ trợ               | `405 Method Not Allowed`                    |

### Cấu trúc Request / Response ví dụ (POST /api/gio-hang.php):

- **Payload gửi đi (JSON):**
  ```json
  {
    "id": "SH001",
    "sl": 2
  }
  ```
- **Phản hồi thành công (200 OK):**
  ```json
  {
    "so_mon": 2,
    "thong_bao": "Đã thêm vào giỏ hàng"
  }
  ```

---

## 6. Trang cá nhân của các thành viên (Phần B)

| STT |  Mã sinh viên  | Họ và tên                | URL trang cá nhân (`php -S localhost:8000`)                              |
| :-: | :------------: | :----------------------- | :----------------------------------------------------------------------- |
|  1  | **3120224028** | **Nguyễn Đình Minh Đạt** | `http://localhost:8000/thanhvien/3120224028_dat/gioithieu.php`           |
|  2  |   3120224062   | Phạm Huy Hoàng           | `http://localhost:8000/thanhvien/3120224062_phamhoang/gioithieu.php`     |
|  3  |   3120224123   | Dương Quang Sơn          | `http://localhost:8000/thanhvien/3120224123_son/gioithieu.php`           |
|  4  |   3120224170   | Nguyễn Hữu Tuân          | `http://localhost:8000/thanhvien/3120224170_nguyenhuutuan/gioithieu.php` |

---

**Thông tin dự án:** StyleHub – Website bán hàng thời trang trực tuyến  
**Học phần:** Thiết kế và Lập trình Web  
**Đơn vị:** Khoa Toán - Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng  
**Mã nhóm:** Nhóm 10
