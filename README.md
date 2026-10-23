# StyleHub – Website bán hàng thời trang (Nhóm 10)

Website bán hàng thời trang trực tuyến phục vụ học phần Lập trình Web. Dự án sử dụng PHP để xử lý phía máy chủ, MySQL/MariaDB để quản lý dữ liệu, PDO để kết nối cơ sở dữ liệu và JavaScript để hỗ trợ các tương tác trên giao diện.

## 1. Yêu cầu môi trường

| Thành phần      | Yêu cầu                                       |
| --------------- | --------------------------------------------- |
| PHP             | PHP >= 8.1                                    |
| MySQL / MariaDB | MySQL hoặc MariaDB có hỗ trợ InnoDB và UTF-8  |
| Composer        | Composer 2.x                                  |
| Trình duyệt     | Google Chrome, Microsoft Edge hoặc Firefox    |
| Hệ điều hành    | Windows hoặc hệ điều hành hỗ trợ PHP và MySQL |
| Git             | Dùng để quản lý mã nguồn                      |
| XAMPP           | Khuyến nghị khi phát triển trên Windows       |

### Kiểm tra môi trường

Mở PowerShell tại máy tính và chạy:

```powershell
php -v
composer --version
mysql --version
git --version
```

Nếu lệnh `mysql --version` không chạy được, hãy kiểm tra MySQL/MariaDB đã được cài đặt và cấu hình biến môi trường `PATH` hay chưa. Bạn vẫn có thể sử dụng phpMyAdmin đi kèm XAMPP để quản lý cơ sở dữ liệu.

## 2. Công nghệ sử dụng

| Công nghệ           | Vai trò                                                 |
| ------------------- | ------------------------------------------------------- |
| PHP 8.1 trở lên     | Xử lý yêu cầu HTTP, biểu mẫu và tạo nội dung HTML động  |
| MySQL / MariaDB     | Lưu trữ và quản lý dữ liệu của website                  |
| PDO                 | Kết nối cơ sở dữ liệu và thực thi truy vấn có tham số   |
| Prepared Statements | Hạn chế nguy cơ SQL Injection khi truy vấn dữ liệu      |
| Composer            | Quản lý các thư viện và hỗ trợ tự động nạp lớp          |
| PSR-4 Autoloading   | Tự động nạp các lớp PHP theo cấu hình Composer          |
| HTML5 và CSS3       | Xây dựng cấu trúc, giao diện và bố cục website          |
| JavaScript ES6+     | Xử lý tương tác phía trình duyệt                        |
| Fetch API           | Gửi yêu cầu HTTP bất đồng bộ đến các endpoint API       |
| JSON                | Định dạng dữ liệu trao đổi giữa client và server        |
| Session và Cookie   | Quản lý trạng thái phiên và một số thông tin người dùng |
| Git                 | Quản lý phiên bản và phối hợp phát triển nhóm           |

## 3. Cài đặt và khởi tạo dự án

### Bước 1. Mở thư mục gốc

Mở PowerShell và chuyển đến thư mục chứa mã nguồn:

```powershell
cd C:\xampp\htdocs\Itweb
```

Nếu dự án được đặt tại thư mục khác, hãy thay đường dẫn trên bằng đường dẫn thực tế.

### Bước 2. Cài đặt các phụ thuộc

Chạy:

```powershell
composer install
```

Lệnh này cài đặt các phụ thuộc được khai báo trong `composer.json` và tạo hoặc cập nhật thư mục `vendor/` khi cấu hình dự án yêu cầu.

### Bước 3. Khởi động MySQL

Nếu sử dụng XAMPP:

1. Mở XAMPP Control Panel.
2. Khởi động dịch vụ MySQL.
3. Khởi động Apache nếu cần sử dụng phpMyAdmin.
4. Mở địa chỉ `http://localhost/phpmyadmin`.

### Bước 4. Tạo cơ sở dữ liệu

Tạo cơ sở dữ liệu có tên:

```sql
CREATE DATABASE IF NOT EXISTS ltweb_nhom10
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Chọn cơ sở dữ liệu `ltweb_nhom10` trước khi nhập các tệp SQL.

### Bước 5. Tạo bảng và nạp dữ liệu mẫu

Dự án sử dụng hai tệp SQL dự kiến nằm trong thư mục `database/`:

- `database/schema.sql`: định nghĩa cấu trúc bảng, khóa chính, khóa ngoại và các ràng buộc dữ liệu.
- `database/seed.sql`: chèn dữ liệu mẫu để phục vụ kiểm thử.

Thực hiện theo thứ tự:

1. Mở phpMyAdmin.
2. Chọn cơ sở dữ liệu `ltweb_nhom10`.
3. Chọn **Import** và nhập tệp `database/schema.sql`.
4. Tiếp tục nhập tệp `database/seed.sql`.
5. Kiểm tra các bảng và dữ liệu đã được tạo thành công.

Nếu sử dụng MySQL CLI, có thể chạy:

```powershell
mysql -u root -p
```

Sau khi đăng nhập MySQL, thoát về PowerShell và chạy từng lệnh sau tại thư mục gốc dự án:

```powershell
mysql -u root -p ltweb_nhom10 < database/schema.sql
mysql -u root -p ltweb_nhom10 < database/seed.sql
```

Nếu MySQL của bạn không đặt mật khẩu cho tài khoản `root`, hãy điều chỉnh lệnh đăng nhập cho phù hợp với cấu hình cục bộ.

**Lưu ý:** Hai tệp SQL phải tồn tại đúng vị trí trong mã nguồn. Nếu chưa có, cần tạo chúng dựa trên thiết kế cơ sở dữ liệu thực tế của dự án trước khi thực hiện bước này.

### Bước 6. Cấu hình kết nối cơ sở dữ liệu

Kiểm tra tệp cấu hình kết nối PDO của dự án. Các thông tin cần cấu hình gồm:

- Máy chủ MySQL.
- Cổng kết nối.
- Tên cơ sở dữ liệu: `ltweb_nhom10`.
- Tên người dùng MySQL.
- Mật khẩu MySQL.
- Bộ mã ký tự `utf8mb4`.

Ví dụ cấu hình tham khảo:

```php
$host = '127.0.0.1';
$port = '3306';
$dbname = 'ltweb_nhom10';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

Đây là ví dụ cấu hình, không phải nội dung bắt buộc phải sao chép nguyên trạng. Hãy sử dụng đúng tên biến, vị trí cấu hình và thông tin đăng nhập mà mã nguồn hiện tại yêu cầu. Không đưa mật khẩu cơ sở dữ liệu thật vào kho Git.

## 4. Chạy website

Tại thư mục gốc của dự án, chạy:

```powershell
php -S localhost:8000
```

Giữ cửa sổ PowerShell mở trong khi sử dụng website.

### Các địa chỉ truy cập chính

| Chức năng          | URL                                           |
| ------------------ | --------------------------------------------- |
| Trang chủ          | `http://localhost:8000/`                      |
| Danh sách sản phẩm | `http://localhost:8000/danh-sach.php`         |
| Chi tiết sản phẩm  | `http://localhost:8000/chi-tiet.php?id=SH001` |
| Giỏ hàng           | `http://localhost:8000/gio-hang.php`          |
| Liên hệ            | `http://localhost:8000/lien-he.php`           |
| Đăng nhập          | `http://localhost:8000/dang-nhap.php`         |
| Đăng ký            | `http://localhost:8000/dang-ky.php`           |
| Đơn hàng cá nhân   | `http://localhost:8000/don-hang.php`          |
| Trang quản trị     | `http://localhost:8000/quan-tri.php`          |
| Giới thiệu nhóm    | `http://localhost:8000/gioi-thieu.php`        |

Các đường dẫn trên là danh sách tham khảo theo cấu trúc dự kiến; chỉ sử dụng những URL tương ứng với tệp thực sự tồn tại trong dự án.

Để dừng máy chủ, nhấn `Ctrl + C` trong cửa sổ PowerShell.

**Lưu ý:** PHP Development Server chỉ phù hợp cho phát triển và kiểm thử cục bộ, không nên dùng trực tiếp làm máy chủ website trong môi trường sản xuất.

## 5. Tài khoản thử nghiệm

Tài khoản thử nghiệm phải được tạo hoặc nạp từ dữ liệu thực tế của cơ sở dữ liệu. Không sử dụng thông tin bên dưới nếu chưa xác nhận tài khoản tồn tại và đăng nhập thành công.

| Vai trò       | Tên đăng nhập | Mật khẩu                    | Chức năng kiểm thử                                      |
| ------------- | ------------- | --------------------------- | ------------------------------------------------------- |
| Quản trị viên | `admin`       | `Admin@123` (cần xác minh)  | Truy cập trang quản trị và các chức năng được cấp quyền |
| Khách hàng 1  | `khach1`      | `Khach1@123` (cần xác minh) | Kiểm thử chức năng khách hàng nếu tài khoản đã được tạo |
| Khách hàng 2  | `khach2`      | `Khach2@123` (cần xác minh) | Kiểm thử chức năng khách hàng nếu tài khoản đã được tạo |

Trang đăng nhập dự kiến: `http://localhost:8000/dang-nhap.php`.

Nếu chưa có dữ liệu tài khoản mẫu, hãy tạo tài khoản qua chức năng đăng ký hoặc bổ sung dữ liệu hợp lệ vào tệp seed theo cấu trúc bảng người dùng thực tế. Mật khẩu lưu trong cơ sở dữ liệu cần được băm bằng cơ chế phù hợp như `password_hash()` và xác minh bằng `password_verify()`.

Không sử dụng tài khoản thử nghiệm hoặc mật khẩu mặc định trên môi trường công khai.

## 6. Bảng chức năng – URL – Tệp PHP

Bảng dưới đây mô tả các trang dự kiến của hệ thống. Cần đối chiếu tên tệp thực tế trước khi sử dụng làm danh mục chức năng chính thức.

### 6.1. Các trang giao diện

| Chức năng            | URL dự kiến                  | Tệp PHP             |
| -------------------- | ---------------------------- | ------------------- |
| Trang chủ            | `/` hoặc `/index.php`        | `index.php`         |
| Danh sách sản phẩm   | `/danh-sach.php`             | `danh-sach.php`     |
| Chi tiết sản phẩm    | `/chi-tiet.php?id=SH001`     | `chi-tiet.php`      |
| Giỏ hàng             | `/gio-hang.php`              | `gio-hang.php`      |
| Gửi yêu cầu liên hệ  | `/lien-he.php`               | `lien-he.php`       |
| Đăng ký tài khoản    | `/dang-ky.php`               | `dang-ky.php`       |
| Đăng nhập            | `/dang-nhap.php`             | `dang-nhap.php`     |
| Đăng xuất            | `/dang-xuat.php`             | `dang-xuat.php`     |
| Đặt hàng             | `/dat-hang.php`              | `dat-hang.php`      |
| Xem đơn hàng cá nhân | `/don-hang.php`              | `don-hang.php`      |
| Trang quản trị       | `/quan-tri.php`              | `quan-tri.php`      |
| Thêm sản phẩm        | `/them-san-pham.php`         | `them-san-pham.php` |
| Sửa sản phẩm         | `/sua-san-pham.php?id=SH001` | `sua-san-pham.php`  |
| Xóa sản phẩm         | `/xoa-san-pham.php`          | `xoa-san-pham.php`  |
| Lỗi không tìm thấy   | `/404.php`                   | `404.php`           |
| Lỗi máy chủ          | `/500.php`                   | `500.php`           |

Các trang quản trị phải kiểm tra trạng thái đăng nhập và quyền quản trị ở phía máy chủ. Những thao tác thay đổi dữ liệu cần kiểm tra dữ liệu đầu vào và sử dụng phương thức HTTP phù hợp.

### 6.2. Các thành phần PHP dùng chung

| Thành phần             | Tệp dự kiến        | Vai trò                                  |
| ---------------------- | ------------------ | ---------------------------------------- |
| Cấu hình ứng dụng      | `inc/config.php`   | Khởi tạo cấu hình chung và session       |
| Kết nối cơ sở dữ liệu  | `inc/db.php`       | Tạo kết nối PDO tới MySQL                |
| Xác thực và phân quyền | `inc/xac-thuc.php` | Kiểm tra đăng nhập và quyền truy cập     |
| Kiểm tra dữ liệu       | `inc/kiem-tra.php` | Kiểm tra tính hợp lệ của dữ liệu đầu vào |
| Hàm tiện ích           | `inc/ham.php`      | Các hàm dùng chung                       |
| Header                 | `inc/header.php`   | Phần đầu giao diện                       |
| Menu                   | `inc/menu.php`     | Thanh điều hướng                         |
| Footer                 | `inc/footer.php`   | Phần chân trang                          |

Tên tệp trong bảng phải được cập nhật theo cấu trúc thực tế của thư mục `inc/`. Không tham chiếu đến các tệp đã bị xóa hoặc chưa được tạo.

## 7. Các endpoint REST API

Các endpoint dưới đây là danh mục dự kiến cho API sản phẩm và giỏ hàng. Chỉ xem endpoint là đã triển khai sau khi kiểm tra mã nguồn trong thư mục `api/`.

| STT | Phương thức              | Endpoint                     | Chức năng dự kiến                     | Mã HTTP dự kiến              |
| --- | ------------------------ | ---------------------------- | ------------------------------------- | ---------------------------- |
| 1   | GET                      | `/api/san-pham.php`          | Lấy danh sách sản phẩm dạng JSON      | `200 OK`                     |
| 2   | GET                      | `/api/san-pham.php?q=somi`   | Tìm kiếm sản phẩm theo từ khóa        | `200 OK`                     |
| 3   | GET                      | `/api/san-pham.php?id=SH001` | Lấy chi tiết sản phẩm theo mã         | `200 OK`, `404 Not Found`    |
| 4   | POST                     | `/api/gio-hang.php`          | Thêm sản phẩm vào giỏ hàng            | `200 OK` hoặc mã lỗi phù hợp |
| 5   | Phương thức không hỗ trợ | `/api/san-pham.php`          | Từ chối phương thức không được hỗ trợ | `405 Method Not Allowed`     |

### Ví dụ gửi yêu cầu API

Lấy danh sách sản phẩm:

```http
GET /api/san-pham.php HTTP/1.1
Host: localhost:8000
Accept: application/json
```

Tìm kiếm sản phẩm:

```http
GET /api/san-pham.php?q=somi HTTP/1.1
Host: localhost:8000
Accept: application/json
```

Thêm sản phẩm vào giỏ hàng, nếu API thực tế nhận JSON theo cấu trúc này:

```http
POST /api/gio-hang.php HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Accept: application/json

{
  "san_pham_id": "SH001",
  "so_luong": 1
}
```

Tên trường dữ liệu, phương thức HTTP, điều kiện xác thực và mã phản hồi phải khớp với phần triển khai thực tế. Không mặc định rằng API hỗ trợ PUT, DELETE hoặc CRUD đầy đủ nếu mã nguồn chưa xử lý các phương thức đó.

## 8. Trang giới thiệu nhóm và trang cá nhân thành viên

### 8.1. Trang giới thiệu nhóm

URL: `http://localhost:8000/gioi-thieu.php`

### 8.2. Trang cá nhân thành viên

| STT | Mã sinh viên | Thành viên           | Đường dẫn                                           |
| --- | ------------ | -------------------- | --------------------------------------------------- |
| 1   | 3120224028   | Nguyễn Đình Minh Đạt | `/thanhvien/3120224028_dat/gioithieu.php`           |
| 2   | 3120224062   | Phạm Huy Hoàng       | `/thanhvien/3120224062_phamhoang/gioithieu.php`     |
| 3   | 3120224123   | Dương Quang Sơn      | `/thanhvien/3120224123_son/gioithieu.php`           |
| 4   | 3120224170   | Nguyễn Hữu Tuân      | `/thanhvien/3120224170_nguyenhuutuan/gioithieu.php` |

Các trang cá nhân cần được kiểm tra riêng để bảo đảm đường dẫn tồn tại, giao diện hoạt động và các chức năng PHP sử dụng đúng cấu trúc dữ liệu của dự án.

## 9. Kiểm thử sau khi cài đặt

Sau khi hoàn tất cấu hình, thực hiện lần lượt:

1. Kiểm tra PHP, Composer và MySQL/MariaDB.
2. Chạy `composer install` và kiểm tra thư mục `vendor/` nếu dự án sử dụng Composer Autoload.
3. Kiểm tra cơ sở dữ liệu `ltweb_nhom10` đã có các bảng theo thiết kế.
4. Kiểm tra dữ liệu mẫu đã được nạp thành công.
5. Mở trang chủ và danh sách sản phẩm.
6. Mở chi tiết một sản phẩm tồn tại trong cơ sở dữ liệu.
7. Kiểm thử giỏ hàng và kiểm tra dữ liệu đầu vào.
8. Gửi biểu mẫu liên hệ hợp lệ và không hợp lệ.
9. Đăng nhập, đăng xuất và kiểm tra quyền truy cập.
10. Kiểm thử các chức năng quản trị nếu đã triển khai.
11. Gửi yêu cầu đến các endpoint API và kiểm tra JSON, mã HTTP.
12. Kiểm tra nhật ký lỗi nếu có lỗi phát sinh.

## 10. Lưu ý bảo mật và dữ liệu

- Sử dụng PDO Prepared Statements cho các truy vấn có dữ liệu đầu vào.
- Kiểm tra dữ liệu ở phía máy chủ, không chỉ dựa vào JavaScript.
- Mã hóa đầu ra HTML để hạn chế nguy cơ XSS.
- Bảo vệ các chức năng quản trị bằng cơ chế xác thực và phân quyền phía máy chủ.
- Bảo vệ các thao tác thay đổi dữ liệu bằng cơ chế chống CSRF phù hợp.
- Không lưu mật khẩu người dùng dưới dạng văn bản thuần.
- Không đưa thông tin xác thực cơ sở dữ liệu vào kho mã nguồn.
- Sao lưu dữ liệu trước khi chạy lại các lệnh tạo bảng hoặc nạp dữ liệu mẫu.
- Không sử dụng PHP Development Server làm máy chủ triển khai công khai.

---

**Dự án:** StyleHub – Website bán hàng thời trang  
**Môn học:** Lập trình Web  
**Nhóm:** Nhóm 10
