<?php
namespace Config;

use PDO;
use PDOException;

/**
 * Class Database
 * Quản lý kết nối cơ sở dữ liệu MariaDB / MySQL qua PDO
 * Cấu hình ERRMODE_EXCEPTION để hỗ trợ hiển thị lỗi phục vụ bài lab SQL Injection (Error-Based SQLi)
 */
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'mysql';
            $db   = getenv('DB_NAME') ?: 'brewcart';
            $user = getenv('DB_USER') ?: 'brewcart_user';
            $pass = getenv('DB_PASSWORD') ?: 'brewcart_secret_db_2026';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Fallback nếu kết nối lỗi
                throw new PDOException("Lỗi kết nối CSDL: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}
