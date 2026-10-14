<?php
namespace Models;

use Config\Database;
use PDO;

class Review {
    /**
     * Lưu đánh giá người dùng - [CỐ Ý TẠO LỖ HỔNG STORED XSS]
     * Không lọc bỏ thẻ HTML, không encode htmlspecialchars trước khi lưu vào DB
     */
    public static function create($productSlug, $authorName, $rating, $body, $imageUrl = null): int {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO reviews (product_slug, author_name, rating, body, status, image_url) VALUES (?, ?, ?, ?, "pending", ?)'
        );
        $stmt->execute([
            $productSlug ?: 'americano',
            $authorName ?: 'Khách hàng ẩn danh',
            (int)$rating ?: 5,
            $body, // Dữ liệu thô chứa mã XSS được lưu nguyên bản
            $imageUrl
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getApproved($productSlug = null): array {
        $db = Database::getConnection();
        if ($productSlug) {
            $stmt = $db->prepare('SELECT * FROM reviews WHERE status = "approved" AND product_slug = ? ORDER BY id DESC');
            $stmt->execute([$productSlug]);
        } else {
            $stmt = $db->query('SELECT * FROM reviews WHERE status = "approved" ORDER BY id DESC');
        }
        return $stmt->fetchAll();
    }

    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM reviews ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function setStatus($id, $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE reviews SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    /**
     * Xoá vĩnh viễn đánh giá khỏi cơ sở dữ liệu
     * Đảm bảo sau khi xoá sẽ không bao giờ hiển thị lại trên giao diện
     */
    public static function delete($id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM reviews WHERE id = ?');
        return $stmt->execute([(int)$id]);
    }
}
