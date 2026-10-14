<?php
namespace Controllers;

use Models\Order;
use Models\User;

class OrderController {
    public function list() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Yêu cầu đăng nhập']);
            return;
        }

        $orders = Order::getAllByUser($user['id']);
        echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Chi tiết đơn hàng - [ĐÃ VÁ CHẶT CHẼ: CHẶN HOÀN TOÀN IDOR]
     * Chỉ chủ sở hữu đơn hàng hoặc Admin mới có quyền xem
     * Endpoint: GET /api/member/orders/:id
     */
    public function detail($id) {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Yêu cầu đăng nhập']);
            return;
        }

        $order = Order::findById($id);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy đơn hàng #' . $id]);
            return;
        }

        // Kiểm tra quyền sở hữu đơn hàng
        if ((int)$order['user_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Từ chối truy cập: Bạn không có quyền xem đơn hàng của người khác!']);
            return;
        }

        echo json_encode($order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
