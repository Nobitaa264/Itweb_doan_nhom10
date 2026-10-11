# StyleHub – Website bán hàng thời trang

## 1. Yêu cầu môi trường

Các công cụ và môi trường cần thiết:

| Thành phần   | Yêu cầu                                                             |
| ------------ | ------------------------------------------------------------------- |
| PHP          | Phiên bản 8.2.12                                                    |
| Composer     | Phiên bản 2.10.3 hỗ trợ các yêu cầu được khai báo trong dự án       |
| Trình duyệt  | Google Chrome, Microsoft Edge hoặc trình duyệt hiện đại tương đương |
| Hệ điều hành | Windows hoặc hệ điều hành hỗ trợ PHP                                |
| Git          | Sử dụng để quản lý mã nguồn và làm việc nhóm                        |
| XAMPP        | Tùy chọn, hỗ trợ môi trường phát triển PHP trên Windows             |

### Kiểm tra môi trường

Mở PowerShell hoặc Terminal và chạy:

```powershell
php -v
composer --version
git --version
```

Nếu các lệnh trên hiển thị phiên bản tương ứng, có thể tiếp tục cài đặt dự án. Nếu PHP hoặc Composer chưa được nhận diện, cần cài đặt và cấu hình biến môi trường `PATH`.

## 2. Công nghệ sử dụng

| Công nghệ                            | Vai trò                                                                                                                     |
| ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------- |
| **PHP 8.2.12**                       | Ngôn ngữ lập trình phía máy chủ; xử lý yêu cầu HTTP, biểu mẫu, kiểm tra dữ liệu, session, cookie và tạo nội dung HTML động. |
| **Composer**                         | Quản lý các phụ thuộc PHP và hỗ trợ tự động nạp lớp.                                                                        |
| **PSR-4 Autoloading**                | Quy ước ánh xạ namespace và lớp PHP vào thư mục mã nguồn, được cấu hình thông qua Composer.                                 |
| **HTML5**                            | Xây dựng cấu trúc nội dung của các trang web.                                                                               |
| **CSS3**                             | Thiết kế giao diện, bố cục và định dạng các thành phần.                                                                     |
| **JavaScript**                       | Xử lý tương tác phía trình duyệt, bao gồm chức năng sản phẩm yêu thích.                                                     |
| **JSON**                             | Lưu trữ và đọc dữ liệu sản phẩm theo cấu trúc JSON.                                                                         |
| **JSON Lines (JSONL)**               | Lưu các yêu cầu liên hệ, mỗi dòng là một bản ghi JSON.                                                                      |
| **Session và Cookie**                | Quản lý trạng thái đăng nhập, giỏ hàng, thông báo và ghi nhớ một số thông tin người dùng.                                   |
| **Web Storage API (`localStorage`)** | Lưu danh sách sản phẩm yêu thích trong trình duyệt.                                                                         |
| **Git**                              | Quản lý phiên bản mã nguồn và hỗ trợ phối hợp làm việc nhóm.                                                                |
| **PHP Development Server**           | Chạy website cục bộ phục vụ phát triển và kiểm thử.                                                                         |

### Công cụ phát triển

- **Visual Studio Code hoặc trình soạn thảo tương đương:** chỉnh sửa mã nguồn.
- **PowerShell:** chạy lệnh cài đặt, khởi động máy chủ và kiểm tra dự án.
- **Google Chrome hoặc Microsoft Edge:** kiểm thử giao diện và các yêu cầu HTTP.
- **XAMPP:** môi trường hỗ trợ phát triển PHP trên Windows; có thể sử dụng khi phù hợp với cấu hình dự án.

## 3. Cài đặt dự án

### Bước 1. Mở thư mục dự án

Mở PowerShell tại thư mục gốc:

```powershell
cd C:\xampp\htdocs\Itweb
```

Nếu mã nguồn chưa có trên máy, cần lấy mã nguồn từ kho Git của nhóm trước khi thực hiện các bước tiếp theo.

### Bước 2. Cài đặt các phụ thuộc

Chạy lệnh:

```powershell
composer install
```

Lệnh này cài đặt các phụ thuộc được khai báo trong `composer.json` và tạo hoặc cập nhật cơ chế autoload trong thư mục `vendor`.

Nếu gặp lỗi không tìm thấy lệnh `composer`, hãy kiểm tra việc cài đặt Composer và biến môi trường `PATH`.

### Bước 3. Kiểm tra các thư mục dữ liệu

Dự án sử dụng các thư mục và thành phần chính sau:

- `data/`: dữ liệu sản phẩm.
- `storage/`: dữ liệu liên hệ được lưu trữ.
- `uploads/`: thư mục dành cho tệp tải lên.
- `logs/`: nhật ký lỗi PHP.
- `src/`: mã nguồn các lớp và dịch vụ.
- `inc/`: các thành phần PHP dùng chung.
- `js/`: mã JavaScript phía trình duyệt.
- `vendor/`: các phụ thuộc và mã autoload do Composer quản lý.

Đảm bảo các thư mục cần thiết tồn tại và tài khoản chạy PHP có quyền ghi vào các thư mục được sử dụng để lưu dữ liệu, tệp tải lên và nhật ký.

Không xóa dữ liệu đang sử dụng trong `storage/` hoặc `uploads/` khi chưa sao lưu.

## 4. Chạy website

Tại thư mục gốc của dự án, chạy:

```powershell
php -S localhost:8000
```

Giữ cửa sổ Terminal đang chạy máy chủ và mở trình duyệt để truy cập website.

### Các địa chỉ truy cập

| Trang              | URL                                         |
| ------------------ | ------------------------------------------- |
| Trang chủ          | http://localhost:8000/                      |
| Danh sách sản phẩm | http://localhost:8000/danh-sach.php         |
| Chi tiết sản phẩm  | http://localhost:8000/chi-tiet.php?id=SH001 |
| Giỏ hàng           | http://localhost:8000/gio-hang.php          |
| Liên hệ            | http://localhost:8000/lien-he.php           |
| Đăng nhập          | http://localhost:8000/dang-nhap.php         |
| Đăng xuất          | http://localhost:8000/dang-xuat.php         |
| Quản trị           | http://localhost:8000/quan-tri.php          |

**Lưu ý:** Mã sản phẩm `SH001` trong URL chi tiết chỉ là ví dụ; sản phẩm phải tồn tại trong dữ liệu hiện tại của dự án.

Để dừng máy chủ, quay lại cửa sổ Terminal và nhấn `Ctrl + C`.

PHP Development Server phù hợp cho môi trường phát triển và kiểm thử cục bộ, không nên sử dụng làm máy chủ cho website công khai trong môi trường sản xuất.

## 5. Tài khoản thử nghiệm trang quản trị

Tài khoản dưới đây chỉ dùng để kiểm thử website StyleHub của nhóm trong môi trường phát triển.

| Thông tin           | Giá trị                             |
| ------------------- | ----------------------------------- |
| Tên đăng nhập       | `admin`                             |
| Mật khẩu thử nghiệm | `Admin@123`                         |
| Trang đăng nhập     | http://localhost:8000/dang-nhap.php |
| Trang quản trị      | http://localhost:8000/quan-tri.php  |

### Lưu ý bảo mật

- Đây là tài khoản thử nghiệm của website nhóm, không sử dụng cho các hệ thống khác.
- Không sử dụng tài khoản này cho website công khai hoặc môi trường sản xuất.
- Không sử dụng lại mật khẩu thử nghiệm cho tài khoản cá nhân.
- Khi triển khai thực tế, cần thay đổi thông tin xác thực và cấu hình bảo mật phù hợp.
- Chỉ người dùng đã đăng nhập và được cấp quyền mới được truy cập các chức năng quản trị.

## 6. Bảng chức năng – URL – Tệp PHP

| Chức năng                | URL                      | Tệp PHP chính   |
| ------------------------ | ------------------------ | --------------- |
| Trang chủ                | `/` hoặc `/index.php`    | `index.php`     |
| Danh sách sản phẩm       | `/danh-sach.php`         | `danh-sach.php` |
| Chi tiết sản phẩm        | `/chi-tiet.php?id=SH001` | `chi-tiet.php`  |
| Giỏ hàng                 | `/gio-hang.php`          | `gio-hang.php`  |
| Gửi yêu cầu liên hệ      | `/lien-he.php`           | `lien-he.php`   |
| Đăng nhập                | `/dang-nhap.php`         | `dang-nhap.php` |
| Đăng xuất                | `/dang-xuat.php`         | `dang-xuat.php` |
| Trang quản trị           | `/quan-tri.php`          | `quan-tri.php`  |
| Trang lỗi không tìm thấy | `/404.php`               | `404.php`       |
| Trang lỗi máy chủ        | `/500.php`               | `500.php`       |

### Các thành phần PHP dùng chung

| Thành phần            | Tệp                        | Vai trò                                          |
| --------------------- | -------------------------- | ------------------------------------------------ |
| Cấu hình ứng dụng     | `inc/config.php`           | Khởi tạo cấu hình, session và xử lý ngoại lệ.    |
| Header                | `inc/header.php`           | Phần đầu giao diện dùng chung.                   |
| Menu                  | `inc/menu.php`             | Thanh điều hướng.                                |
| Footer                | `inc/footer.php`           | Phần chân trang dùng chung.                      |
| Bảo vệ trang quản trị | `inc/bao-ve.php`           | Kiểm tra trạng thái đăng nhập và quyền truy cập. |
| Xử lý tài khoản       | `inc/tai-khoan.php`        | Các xử lý liên quan đến tài khoản.               |
| Hàm dùng chung        | `inc/ham.php`              | Các hàm hỗ trợ và xử lý dữ liệu.                 |
| Mô hình sản phẩm      | `src/Models/SanPham.php`   | Đại diện dữ liệu sản phẩm.                       |
| Kho sản phẩm          | `src/Data/KhoSanPham.php`  | Truy xuất và quản lý dữ liệu sản phẩm.           |
| Dịch vụ giỏ hàng      | `src/Services/GioHang.php` | Xử lý các thao tác với giỏ hàng.                 |

## 7. Dữ liệu và nhật ký

| Đường dẫn               | Mục đích                                                   |
| ----------------------- | ---------------------------------------------------------- |
| `data/`                 | Chứa dữ liệu phục vụ hiển thị sản phẩm.                    |
| `storage/lien-he.jsonl` | Lưu các yêu cầu liên hệ theo định dạng JSON Lines.         |
| `uploads/`              | Dành cho các tệp được tải lên thông qua chức năng liên hệ. |
| `logs/php-error.log`    | Ghi nhật ký lỗi PHP theo cấu hình của ứng dụng.            |

Một số dữ liệu được quản lý trong session hoặc cookie của trình duyệt. Giỏ hàng sử dụng `$_SESSION['gio']`, trạng thái đăng nhập sử dụng `$_SESSION['nguoi_dung']`, còn danh sách yêu thích được lưu bằng `localStorage`.

Không đưa dữ liệu cá nhân, tệp tải lên nhạy cảm, mật khẩu hoặc thông tin xác thực vào kho mã nguồn.

## 8. Kiểm thử các chức năng cơ bản

Sau khi khởi động website, có thể thực hiện các bước kiểm thử:

1. Mở trang chủ và kiểm tra giao diện.
2. Truy cập danh sách sản phẩm, thử tìm kiếm sản phẩm.
3. Mở trang chi tiết một sản phẩm tồn tại.
4. Thêm sản phẩm vào giỏ hàng và kiểm tra số lượng.
5. Thử gửi biểu mẫu liên hệ với dữ liệu thiếu hoặc không hợp lệ.
6. Gửi biểu mẫu liên hệ hợp lệ và kiểm tra dữ liệu trong `storage/lien-he.jsonl`.
7. Đăng nhập bằng tài khoản thử nghiệm của nhóm và kiểm tra trang quản trị.
8. Đăng xuất và kiểm tra khả năng truy cập lại trang quản trị.
9. Kiểm tra nhật ký `logs/php-error.log` nếu phát sinh lỗi.

## 9. Phạm vi sử dụng

StyleHub được xây dựng phục vụ mục đích học tập, thực hành và trình diễn các chức năng của website PHP.

Trước khi triển khai công khai, cần rà soát bảo mật, phân quyền, xử lý dữ liệu đầu vào, mã hóa đầu ra, cấu hình HTTPS, quyền truy cập tệp và thông tin xác thực.

---

**Dự án:** StyleHub – Website bán hàng thời trang  
**Môn học:** Lập trình Web  
**Nhóm:** Nhóm 10
