<?php
namespace Models;

use Config\Database;
use PDO;

class Ticket {
    public static function getAll(?string $email = null): array {
        $db = Database::getConnection();
        if ($email) {
            $stmt = $db->prepare('SELECT * FROM tickets WHERE email = ? ORDER BY id DESC');
            $stmt->execute([$email]);
        } else {
            $stmt = $db->query('SELECT * FROM tickets ORDER BY id DESC');
        }
        return $stmt->fetchAll();
    }

    public static function create($subject, $email, $body): int {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO tickets (subject, email, status, body) VALUES (?, ?, "open", ?)');
        $stmt->execute([$subject, $email, $body]);
        return (int)$db->lastInsertId();
    }

    public static function updateAttachment($ticketId, $attachmentUrl): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE tickets SET attachment_path = ? WHERE id = ?');
        return $stmt->execute([$attachmentUrl, $ticketId]);
    }
}
