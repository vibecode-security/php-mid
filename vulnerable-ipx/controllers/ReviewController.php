<?php
namespace Controllers;

use Models\Review;
use Models\User;

class ReviewController {
    private static function getInput(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) return $json;
        }
        return $_POST;
    }

    /**
     * Nhận đánh giá sản phẩm - Điểm kích hoạt Stored XSS
     * Endpoint: POST /api/store/reviews
     */
    public function submit($slugParam = null) {
        $input = self::getInput();
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        $body = $input['body'] ?? '';
        if (empty($body)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nội dung đánh giá không được để trống']);
            return;
        }

        $productSlug = $slugParam ?: ($input['product_slug'] ?? 'americano');
        $rating = $input['rating'] ?? 5;
        $author = $user ? $user['name'] : 'Khách hàng ẩn danh';
        $imageUrl = $input['imageUrl'] ?? null;

        // Lưu trực tiếp vào CSDL không qua lọc bỏ mã độc (Stored XSS)
        $id = Review::create($productSlug, $author, $rating, $body, $imageUrl);

        // Nếu request từ trình duyệt form submit thông thường
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false && !empty($_POST)) {
            header("Location: /product?id=1");
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công và đang chờ Quản trị viên duyệt!',
            'reviewId' => $id
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * API lấy danh sách đánh giá đã duyệt
     * Endpoint: GET /api/store/reviews
     */
    public function apiStoreReviews() {
        header('Content-Type: application/json; charset=utf-8');
        $reviews = Review::getApproved();
        echo json_encode($reviews, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Giao diện Quản trị duyệt bài - Điểm kích hoạt Stored XSS khi Admin xem
     * Endpoint: GET /admin/reviews
     */
    public function adminIndex() {
        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);

        if (!$currentUser) {
            header('Location: /login');
            return;
        }

        if ($currentUser['role'] !== 'admin') {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            return;
        }

        $reviews = Review::getAll();
        require __DIR__ . '/../views/admin/reviews.php';
    }

    /**
     * API quản trị lấy danh sách đánh giá - Kiểm tra quyền Admin
     * Endpoint: GET /api/admin/reviews
     */
    public function apiAdminReviews() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Từ chối truy cập: Bạn không có quyền Quản trị viên (Admin)!']);
            return;
        }

        $reviews = Review::getAll();
        echo json_encode($reviews, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function approve($id) {
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Từ chối truy cập: Yêu cầu quyền Admin']);
            return;
        }

        Review::setStatus($id, 'approved');

        // Nếu request từ fetch/AJAX hoặc yêu cầu JSON
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'status' => 'approved', 'id' => (int)$id]);
            return;
        }

        // Nếu là form POST thông thường, chuyển hướng lại về chính trang quản trị
        header('Location: /admin/reviews');
    }

    public function reject($id) {
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Từ chối truy cập: Yêu cầu quyền Admin']);
            return;
        }

        // Xoá vĩnh viễn khỏi CSDL MariaDB để không bao giờ hiện lại nữa
        Review::delete($id);

        // Nếu request từ fetch/AJAX hoặc yêu cầu JSON
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'status' => 'rejected', 'deleted' => true, 'id' => (int)$id]);
            return;
        }

        // Nếu là form POST thông thường, chuyển hướng lại về chính trang quản trị
        header('Location: /admin/reviews');
    }
}
