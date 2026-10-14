<?php
namespace Models;

use Config\Database;
use PDO;

class Order {
    public static function getAllByUser($userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findById($orderId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function create($userId, $customerName, $totalAmount, $items, $notes = ''): int {
        $db = Database::getConnection();
        $itemsStr = is_string($items) ? $items : json_encode($items ?: []);
        $stmt = $db->prepare('INSERT INTO orders (user_id, customer_name, total_amount, status, items, notes) VALUES (?, ?, ?, "pending", ?, ?)');
        $stmt->execute([$userId, $customerName, (int)$totalAmount, $itemsStr, $notes]);
        return (int)$db->lastInsertId();
    }
}
