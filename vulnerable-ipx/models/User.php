<?php
namespace Models;

use Config\Database;
use PDO;

class User {
    public static function findByEmailAndPassword($email, $password): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND password = ?');
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByToken($token): ?array {
        if (!$token) return null;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE session_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT id, name, email, role, loyalty_points, created_at FROM users ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    /**
     * Cập nhật thông tin cá nhân - [ĐÃ VÁ CHẶT CHẼ: CHẶN MASS ASSIGNMENT]
     * Chỉ cho phép đổi name và password, dùng Prepared Statement
     */
    public static function updateProfile($userId, ?string $name, ?string $password): ?array {
        $db = Database::getConnection();
        if ($name && $password) {
            $stmt = $db->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?');
            $stmt->execute([$name, $password, $userId]);
        } elseif ($name) {
            $stmt = $db->prepare('UPDATE users SET name = ? WHERE id = ?');
            $stmt->execute([$name, $userId]);
        } elseif ($password) {
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$password, $userId]);
        }

        $stmt = $db->prepare('SELECT id, email, name, role, loyalty_points, created_at FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function register($name, $email, $password): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return null; // Email đã tồn tại
        }

        $token = 'USER_TOKEN_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $insert = $db->prepare('INSERT INTO users (email, password, name, role, loyalty_points, is_vip, session_token) VALUES (?, ?, ?, "customer", 50, 0, ?)');
        $insert->execute([$email, $password, $name, $token]);

        $id = (int)$db->lastInsertId();
        return [
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'role' => 'customer',
            'loyalty_points' => 50,
            'is_vip' => 0,
            'session_token' => $token
        ];
    }
}
