# StyleHub – Website Bán Hàng Thời Trang (Nhóm 10)

Website bán hàng thời trang trực tuyến phục vụ học phần **Thiết kế và Lập trình Web**. Dự án sử dụng PHP thuần xử lý phía máy chủ, kết nối MySQL/MariaDB thông qua PDO, cơ sở dữ liệu chuẩn hóa 3NF, mã hóa `utf8mb4`, tích hợp REST API và JavaScript xử lý tương tác bất đồng bộ.

---

## 1. Yêu cầu môi trường

| Thành phần          | Yêu cầu                                                                |
| :------------------ | :--------------------------------------------------------------------- |
| **PHP**             | PHP ≥ 8.1, bật extension `pdo_mysql`                                   |
| **MySQL / MariaDB** | MySQL 8.0+ hoặc MariaDB 10.4+                                          |
| **Composer**        | Composer 2.x                                                           |
| **Web Server**      | Apache (XAMPP) hoặc PHP Development Server                             |
| **Công cụ hỗ trợ**  | VS Code, Extension REST Client, Command Prompt / PowerShell / Git Bash |

---

## 2. Cài đặt và khởi chạy dự án

### Bước 1: Chuẩn bị môi trường

1. Cài đặt XAMPP và khởi động Apache, MySQL.
2. Cài đặt Composer 2.x.
3. Mở Terminal tại thư mục gốc dự án.

Ví dụ:

```powershell
cd C:\xampp\htdocs\Itweb
```

### Bước 2: Cài đặt thư viện Composer

Chạy lệnh:

```bash
composer install
```

### Bước 3: Khởi tạo cơ sở dữ liệu

Dự án sử dụng cơ sở dữ liệu `ltweb_nhom10`.

Mở Git Bash hoặc Command Prompt tại thư mục gốc dự án và thực hiện lần lượt:

```bash
mysql -u root < database/schema.sql
mysql -u root ltweb_nhom10 < database/seed.sql
```

Nếu MySQL của XAMPP chưa được thêm vào biến môi trường PATH, sử dụng đường dẫn đầy đủ:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql
C:\xampp\mysql\bin\mysql.exe -u root ltweb_nhom10 < database/seed.sql
```

Có thể thay thế bằng cách Import thông qua phpMyAdmin:

1. Truy cập http://localhost/phpmyadmin.
2. Chọn chức năng **Import** và tải tệp `database/schema.sql` lên.
3. Sau khi cơ sở dữ liệu `ltweb_nhom10` được tạo, chọn cơ sở dữ liệu này.
4. Tiếp tục Import tệp `database/seed.sql`.

**Lưu ý:** Tệp `schema.sql` có thể xóa và tạo lại cơ sở dữ liệu. Chỉ thực hiện bước này khi khởi tạo mới hoặc khi muốn làm mới toàn bộ dữ liệu.

### Bước 4: Thiết lập cấu hình môi trường

Tạo file `.env` từ file mẫu `.env.example`.

Trên Windows (PowerShell hoặc Command Prompt):

```powershell
copy .env.example .env
```

Trên Git Bash:

```bash
cp .env.example .env
```

Mở file `.env` và cấu hình thông tin kết nối cơ sở dữ liệu:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ltweb_nhom10
DB_USER=root
DB_PASSWORD=
```

Nếu tài khoản MySQL `root` có mật khẩu, điền mật khẩu tương ứng vào `DB_PASSWORD`.

Có thể chuyển sang sử dụng tài khoản ứng dụng quyền tối thiểu theo hướng dẫn tại mục 2.5.

**Lưu ý bảo mật:**

- Không đưa file `.env` chứa thông tin đăng nhập thật lên GitHub.
- Chỉ đưa file `.env.example` với các giá trị mẫu lên GitHub.
- Không chia sẻ mật khẩu tài khoản MySQL trong mã nguồn.

### Bước 5: Khởi chạy website

Tại thư mục gốc dự án, chạy:

```bash
php -S localhost:8000
```

Mở trình duyệt và truy cập:

http://localhost:8000

### 2.5. Thiết lập tài khoản MySQL quyền tối thiểu

Dự án hỗ trợ tài khoản MySQL riêng dành cho ứng dụng nhằm hạn chế quyền truy cập trực tiếp vào cơ sở dữ liệu.

Thông tin tài khoản:

| Thành phần        | Giá trị        |
| :---------------- | :------------- |
| Tên cơ sở dữ liệu | `ltweb_nhom10` |
| Tên tài khoản     | `nhom10_app`   |
| Host              | `127.0.0.1`    |

Tài khoản `nhom10_app` chỉ được cấp 4 quyền thao tác dữ liệu:

| Quyền    | Chức năng              | Trạng thái      |
| :------- | :--------------------- | :-------------- |
| `SELECT` | Đọc dữ liệu            | Được phép       |
| `INSERT` | Thêm dữ liệu           | Được phép       |
| `UPDATE` | Cập nhật dữ liệu       | Được phép       |
| `DELETE` | Xóa dữ liệu            | Được phép       |
| `CREATE` | Tạo bảng               | Không được phép |
| `DROP`   | Xóa bảng               | Không được phép |
| `ALTER`  | Thay đổi cấu trúc bảng | Không được phép |

**Bước 1: Mở file SQL phân quyền**

Mở tệp `database/tai-khoan.sql`.

Trong tệp này, mật khẩu được ghi dưới dạng chuỗi giữ chỗ:

```sql
IDENTIFIED BY 'THAY_BANG_MAT_KHAU_MANH';
```

Trước khi thực thi, thay `THAY_BANG_MAT_KHAU_MANH` bằng mật khẩu riêng do người cài đặt tự đặt.

**Bước 2: Thực thi tệp SQL bằng tài khoản root**

Mở Git Bash hoặc Command Prompt tại thư mục gốc dự án:

```bash
mysql -u root < database/tai-khoan.sql
```

Nếu sử dụng MySQL của XAMPP:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < database/tai-khoan.sql
```

Có thể mở phpMyAdmin, đăng nhập bằng tài khoản `root`, chọn tab **SQL**, sao chép nội dung tệp `database/tai-khoan.sql` và thực thi.

**Bước 3: Cấu hình tài khoản ứng dụng trong `.env`**

Mở file `.env` và cập nhật:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ltweb_nhom10
DB_USER=nhom10_app
DB_PASSWORD=NHAP_MAT_KHAU_VUA_DAT
```

Thay `NHAP_MAT_KHAU_VUA_DAT` bằng đúng mật khẩu đã sử dụng khi tạo tài khoản trong Bước 1.

**Bước 4: Kiểm tra quyền tài khoản**

Đăng nhập MySQL bằng tài khoản ứng dụng:

```bash
mysql -u nhom10_app -p -h 127.0.0.1 -P 3306 ltweb_nhom10
```

Kiểm tra tài khoản và quyền hiện tại:

```sql
SELECT USER(), CURRENT_USER();

SHOW GRANTS;
```

Thử tạo bảng:

```sql
CREATE TABLE kiem_tra_quyen (
    id INT
);
```

Kết quả mong đợi: MySQL từ chối thao tác và trả về lỗi `1142` do tài khoản không có quyền `CREATE`.

Thử xóa bảng:

```sql
DROP TABLE thanh_vien;
```

Kết quả mong đợi: MySQL từ chối thao tác và trả về lỗi `1142` do tài khoản không có quyền `DROP`.

**Lưu ý:** Chỉ thực hiện kiểm tra bằng tài khoản `nhom10_app`. Không sử dụng tài khoản `root` để kiểm tra vì tài khoản quản trị có quyền tạo và xóa bảng.

Sau khi kiểm tra, thoát MySQL:

```sql
EXIT;
```

**Bước 5: Kiểm tra website**

Khởi chạy website:

```bash
php -S localhost:8000
```

Truy cập http://localhost:8000 và kiểm tra các chức năng đọc, thêm, sửa, xóa dữ liệu.

**Nguyên tắc phân quyền:**

- Tài khoản `root` chỉ dùng để khởi tạo cơ sở dữ liệu, chạy các tệp SQL cấu trúc và thực hiện thao tác quản trị.
- Website sử dụng tài khoản `nhom10_app` để kết nối và thao tác dữ liệu trong quá trình hoạt động.
- File `database/tai-khoan.sql` trên GitHub chỉ chứa mật khẩu giữ chỗ, không chứa mật khẩu cá nhân.
- File `.env` chứa mật khẩu thực tế và không được đưa lên GitHub.

---

## 3. Tài khoản thử nghiệm

Các tài khoản mẫu được tạo trong tệp `database/seed.sql`.

| Vai trò           | Tên đăng nhập | Mật khẩu     | Tên hiển thị  | Chức năng kiểm thử                    |
| :---------------- | :------------ | :----------- | :------------ | :------------------------------------ |
| **Quản trị viên** | `admin`       | `Admin@123`  | Quản trị viên | Quản lý sản phẩm, xem tất cả đơn hàng |
| **Khách hàng 1**  | `khach1`      | `Khach1@123` | khach1        | Đặt hàng, xem đơn hàng cá nhân        |

---

## 4. Bảng chức năng – URL – Tệp PHP

### 4.1. Trang giao diện và chức năng chính (Phần A)

| TT  | Chức năng                                               | URL                                | Tệp PHP xử lý                   |
| :-: | :------------------------------------------------------ | :--------------------------------- | :------------------------------ |
|  1  | Trang chủ                                               | `/` hoặc `/index.php`              | `index.php`                     |
|  2  | Danh sách sản phẩm (lọc, tìm kiếm, sắp xếp, phân trang) | `/danh-sach.php`                   | `danh-sach.php`                 |
|  3  | Chi tiết sản phẩm                                       | `/chi-tiet.php?id=SH001`           | `chi-tiet.php`                  |
|  4  | Giỏ hàng                                                | `/gio-hang.php`                    | `gio-hang.php`                  |
|  5  | Gửi liên hệ / Phản hồi                                  | `/lien-he.php`                     | `lien-he.php`                   |
|  6  | Đăng ký tài khoản                                       | `/dang-ky.php`                     | `dang-ky.php`                   |
|  7  | Đăng nhập hệ thống                                      | `/dang-nhap.php`                   | `dang-nhap.php`                 |
|  8  | Đăng xuất                                               | `/dang-xuat.php`                   | `dang-xuat.php`                 |
|  9  | Đặt hàng từ giỏ (Transaction)                           | `/dat-hang.php`                    | `dat-hang.php`                  |
| 10  | Danh sách đơn hàng cá nhân                              | `/don-hang.php`                    | `don-hang.php`                  |
| 11  | Trang quản trị sản phẩm                                 | `/quan-tri.php`                    | `quan-tri.php`                  |
| 12  | Thêm sản phẩm mới                                       | `/them-san-pham.php`               | `them-san-pham.php`             |
| 13  | Sửa sản phẩm                                            | `/sua-san-pham.php?id=SH001`       | `sua-san-pham.php`              |
| 14  | Xóa sản phẩm (chỉ POST)                                 | `/xoa-san-pham.php`                | `xoa-san-pham.php`              |
| 15  | Trang báo lỗi 403 / 404 / 500                           | `/403.php`, `/404.php`, `/500.php` | `403.php`, `404.php`, `500.php` |

### 4.2. Thành phần cấu trúc dùng chung

| Tệp                       | Vai trò kỹ thuật                                                                           |
| :------------------------ | :----------------------------------------------------------------------------------------- |
| `inc/config.php`          | Nạp cấu hình ứng dụng, môi trường, ghi log lỗi và khởi tạo Session                         |
| `inc/db.php`              | Hàm `db()` khởi tạo kết nối PDO với cơ sở dữ liệu `ltweb_nhom10`, sử dụng mã hóa `utf8mb4` |
| `inc/xac-thuc.php`        | Quản lý hàm `nguoiDung()`, `yeuCauQuyen()`, phân quyền và xác thực Session                 |
| `inc/kiem-tra.php`        | Hàm dùng chung `kiemTraSanPham()` để kiểm tra dữ liệu thêm/sửa sản phẩm                    |
| `src/Data/KhoSanPham.php` | Lớp truy cập dữ liệu (Data Access Layer), chứa các câu lệnh SQL xử lý sản phẩm             |

---

## 5. Bảng danh mục Endpoint REST API

Các API phản hồi dữ liệu định dạng JSON. Có thể sử dụng tệp `api.http` để kiểm thử bằng Extension REST Client trong VS Code.

| STT | Phương thức      | Endpoint                     | Chức năng                                      | Mã HTTP Response                                        |
| :-: | :--------------- | :--------------------------- | :--------------------------------------------- | :------------------------------------------------------ |
|  1  | `GET`            | `/api/san-pham.php`          | Lấy danh sách sản phẩm dạng JSON               | `200 OK`                                                |
|  2  | `GET`            | `/api/san-pham.php?q=somi`   | Tìm kiếm sản phẩm theo từ khóa                 | `200 OK`                                                |
|  3  | `GET`            | `/api/san-pham.php?id=SH001` | Lấy chi tiết sản phẩm theo ID                  | `200 OK`, `404 Not Found`                               |
|  4  | `POST`           | `/api/gio-hang.php`          | Thêm sản phẩm vào giỏ hàng không tải lại trang | `200 OK`, `400 Bad Request`, `422 Unprocessable Entity` |
|  5  | `DELETE` / `PUT` | `/api/gio-hang.php`          | Từ chối phương thức không được hỗ trợ          | `405 Method Not Allowed`                                |

### Cấu trúc Request / Response ví dụ

**Request – POST `/api/gio-hang.php`**

Payload JSON:

```json
{
  "id": "SH001",
  "sl": 2
}
```

**Response – 200 OK**

```json
{
  "so_mon": 2,
  "thong_bao": "Đã thêm vào giỏ hàng"
}
```

---

## 6. Trang cá nhân của các thành viên (Phần B)

| STT |  Mã sinh viên  | Họ và tên                | URL trang cá nhân                                   |
| :-: | :------------: | :----------------------- | :-------------------------------------------------- |
|  1  | **3120224028** | **Nguyễn Đình Minh Đạt** | `/thanhvien/3120224028_dat/gioithieu.php`           |
|  2  |   3120224062   | Phạm Huy Hoàng           | `/thanhvien/3120224062_phamhoang/gioithieu.php`     |
|  3  |   3120224123   | Dương Quang Sơn          | `/thanhvien/3120224123_son/gioithieu.php`           |
|  4  |   3120224170   | Nguyễn Hữu Tuân          | `/thanhvien/3120224170_nguyenhuutuan/gioithieu.php` |

Các đường dẫn trên sử dụng địa chỉ cơ sở khi chạy PHP Development Server là `http://localhost:8000`.

Ví dụ trang cá nhân của thành viên Nguyễn Đình Minh Đạt:

http://localhost:8000/thanhvien/3120224028_dat/gioithieu.php

---

## 7. Thông tin dự án

| Nội dung                  | Thông tin                                                 |
| :------------------------ | :-------------------------------------------------------- |
| Tên dự án                 | StyleHub – Website bán hàng thời trang trực tuyến         |
| Học phần                  | Thiết kế và Lập trình Web                                 |
| Đơn vị                    | Khoa Toán - Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng |
| Mã nhóm                   | Nhóm 10                                                   |
| Cơ sở dữ liệu             | `ltweb_nhom10`                                            |
| Ngôn ngữ phía máy chủ     | PHP                                                       |
| Hệ quản trị cơ sở dữ liệu | MySQL / MariaDB                                           |
| Giao tiếp dữ liệu         | REST API, JSON                                            |

---

**© Nhóm 10 – StyleHub.**
