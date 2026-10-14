FROM php:7.4-apache

# Cài đặt extension pdo_mysql, mod_rewrite, headers và OpenSSH server
RUN sed -i '/bullseye-security/d' /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        openssh-server \
        sudo \
        curl \
        cron \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình SSH Server & Phân cấp tài khoản phục vụ Leo Thang Đặc Quyền (PrivEsc)
RUN mkdir -p /var/run/sshd /root/.ssh \
    && echo 'root:RootUltraSecurePass2026!' | chpasswd \
    && useradd -m -s /bin/bash admin \
    && echo 'admin:AdminSecretPass2026!' | chpasswd \
    && usermod -aG sudo admin \
    && useradd -m -s /bin/bash developer \
    && echo 'developer:123456789' | chpasswd \
    && echo 'developer ALL=(admin) NOPASSWD: /usr/bin/find, /bin/cat' > /etc/sudoers.d/developer_to_admin \
    && echo 'admin ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/admin_to_root \
    && chmod 440 /etc/sudoers.d/* \
    && chmod 4755 /usr/bin/env \
    && sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config \
    && sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication yes/' /etc/ssh/sshd_config \
    && echo 'StrictModes no' >> /etc/ssh/sshd_config \
    && chmod 777 /root/.ssh

# Cấu hình DocumentRoot trỏ vào /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cho phép file .htaccess override cấu hình
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Cấu hình mime types toàn cục cho Apache phục vụ Stored XSS
RUN echo 'AddType text/html .html .shtml .xhtml' >> /etc/apache2/apache2.conf \
    && echo 'AddType image/svg+xml .svg .svgz' >> /etc/apache2/apache2.conf

# Tạo thư mục uploads và phân quyền
RUN mkdir -p /var/www/html/public/uploads /app/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /app/uploads /var/www/html/public/uploads

WORKDIR /var/www/html

EXPOSE 80 22

# Script khởi động tự động SSH daemon rồi chạy Apache foreground
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
