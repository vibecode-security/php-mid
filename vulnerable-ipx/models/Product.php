<?php
namespace Models;

use Config\Database;
use PDO;
use PDOException;

class Product {
    public static array $imageMap = [
        1 => '/img/americano.jpg',
        2 => '/img/mocha.jpg',
        3 => '/img/latte.jpg',
        4 => '/img/macchiato.jpg',
        5 => '/img/cappuccino.jpg',
        6 => '/img/espresso.jpg',
        7 => '/img/moka.jpg',
        8 => '/img/cherry.jpg',
        9 => '/img/culi.jpg',
        10 => '/img/robusta.jpg',
        11 => '/img/arabica.jpg'
    ];

    /**
     * Truy vấn chi tiết sản phẩm - [CỐ Ý TẠO LỖ HỔNG SQL INJECTION]
     * Nối chuỗi trực tiếp tham số $id vào câu lệnh SQL
     * Khi có lỗi cú pháp, sẽ ném PDOException để Controller bắt và hiển thị
     */
    public static function findByIdVulnerable($id, &$queryExecuted = ''): ?array {
        $queryExecuted = "SELECT id, name, price, description FROM products WHERE id = " . $id;
        $db = Database::getConnection();
        
        $stmt = $db->query($queryExecuted);
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            $p = $rows[0];
            $p['image_url'] = self::$imageMap[$p['id']] ?? '/img/americano.jpg';
            return $p;
        }
        return null;
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục hoặc id - [CỐ Ý TẠO LỖ HỔNG SQL INJECTION]
     * Hỗ trợ Time-based Blind SQLi (SLEEP) và UNION SQLi
     */
    public static function getStoreProducts($category = null, $id = null): array {
        $db = Database::getConnection();
        $query = "SELECT p.*, c.name as category, c.slug as category_slug,
             CASE p.roast 
               WHEN 'dark' THEN 'rang đậm' 
               WHEN 'medium' THEN 'rang vừa' 
               ELSE 'rang nhạt' 
             END as roast_label
             FROM products p 
             JOIN categories c ON p.category_id = c.id";

        if ($id !== null && $id !== '') {
            $query .= " WHERE p.id = " . $id;
        } elseif ($category !== null && $category !== '') {
            $query .= " WHERE c.slug = '{$category}' OR p.category_id = '{$category}'";
        }

        $query .= " ORDER BY p.id ASC";
        $stmt = $db->query($query);
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            if (isset(self::$imageMap[$p['id']])) {
                $p['image_url'] = self::$imageMap[$p['id']];
            }
        }
        return $products;
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public static function search($q): array {
        $db = Database::getConnection();
        $sql = "SELECT id, name, price, description, image_url FROM products WHERE name LIKE '%{$q}%' OR description LIKE '%{$q}%'";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách danh mục
     */
    public static function getCategories(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
