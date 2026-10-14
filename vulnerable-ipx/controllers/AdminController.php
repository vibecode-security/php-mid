<?php
namespace Controllers;

use Models\User;

class AdminController {
    /**
     * Danh sách người dùng & khách hàng - [ĐÃ VÁ CHẶT CHẼ: CHẶN BROKEN ACCESS CONTROL]
     * Chỉ cho phép tài khoản có vai trò 'admin' truy cập
     * Endpoint: GET /api/admin/customers hoặc GET /api/admin/users
     */
    public function customers() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Yêu cầu đăng nhập tài khoản Quản trị viên']);
            return;
        }

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Từ chối truy cập: Bạn không có quyền Quản trị viên (Admin)!']);
            return;
        }

        $users = User::getAll();
        echo json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
