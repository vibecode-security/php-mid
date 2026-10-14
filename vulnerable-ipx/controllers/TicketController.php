<?php
namespace Controllers;

use Models\Ticket;
use Models\User;

class TicketController {
    private static function getInput(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) return $json;
        }
        return $_POST;
    }

    public function index() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Yêu cầu đăng nhập']);
            return;
        }

        $emailFilter = ($user['role'] === 'admin') ? null : $user['email'];
        $tickets = Ticket::getAll($emailFilter);
        echo json_encode($tickets, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function create() {
        header('Content-Type: application/json; charset=utf-8');
        $input = self::getInput();
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        $subject = $input['subject'] ?? 'Hỗ trợ khách hàng';
        $body = $input['body'] ?? '';
        $email = $user ? $user['email'] : ($input['email'] ?? 'guest@gmail.com');

        $ticketId = Ticket::create($subject, $email, $body);
        echo json_encode(['success' => true, 'ticketId' => $ticketId]);
    }

    /**
     * Tải tệp đính kèm lên ticket - [LỖ HỔNG XSS FILE UPLOAD]
     * Không kiểm tra định dạng đuôi file (.html, .svg), cho phép thực thi mã độc trên trình duyệt
     * Endpoint: POST /api/member/support/tickets/:id/attachments
     */
    public function uploadAttachment($ticketId) {
        header('Content-Type: application/json; charset=utf-8');
        $input = self::getInput();

        $filename = $input['filename'] ?? null;
        $content = $input['content'] ?? null;

        if (!$filename || $content === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Filename và content là bắt buộc']);
            return;
        }

        $safeName = basename($filename);
        $publicUploadsDir = '/var/www/html/public/uploads';
        $appUploadsDir = '/app/uploads';

        if (!is_dir($publicUploadsDir)) {
            mkdir($publicUploadsDir, 0777, true);
        }
        if (!is_dir($appUploadsDir)) {
            mkdir($appUploadsDir, 0777, true);
        }

        $targetPath = $publicUploadsDir . '/' . $safeName;
        file_put_contents($targetPath, $content);

        // Đồng bộ sang /app/uploads nếu hai thư mục tách biệt
        if ($targetPath !== ($appUploadsDir . '/' . $safeName)) {
            @file_put_contents($appUploadsDir . '/' . $safeName, $content);
        }

        $relativeUrl = '/uploads/' . $safeName;
        Ticket::updateAttachment($ticketId, $relativeUrl);

        echo json_encode([
            'success' => true,
            'attachmentUrl' => $relativeUrl
        ]);
    }
}
