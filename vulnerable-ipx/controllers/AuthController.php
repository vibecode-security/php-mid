<?php
namespace Controllers;

use Models\User;

class AuthController {
    private static function getInput(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) return $json;
        }
        return $_POST;
    }

    private static function isJsonRequest(): bool {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return (strpos($contentType, 'application/json') !== false || strpos($accept, 'application/json') !== false);
    }

    public function showLogin() {
        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);
        if ($currentUser) {
            header('Location: /');
            return;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister() {
        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);
        if ($currentUser) {
            header('Location: /');
            return;
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login() {
        $input = self::getInput();
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        $user = User::findByEmailAndPassword($email, $password);
        if (!$user) {
            if (self::isJsonRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['error' => 'Email hoặc mật khẩu không chính xác!']);
                return;
            }

            $error = 'Email hoặc mật khẩu không chính xác!';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        // Cố ý không bật cờ HttpOnly để phục vụ minh họa XSS đánh cắp Session qua document.cookie
        setcookie('session_token', $user['session_token'], time() + 86400 * 7, '/', '', false, false);

        if (self::isJsonRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'user' => $user]);
            return;
        }

        header('Location: /');
    }

    public function register() {
        $input = self::getInput();
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? $password;

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Vui lòng điền đầy đủ tất cả các trường!';
            if (self::isJsonRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => $error]);
                return;
            }
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($password !== $confirm) {
            $error = 'Mật khẩu xác nhận không khớp!';
            if (self::isJsonRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => $error]);
                return;
            }
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        $user = User::register($name, $email, $password);
        if (!$user) {
            $error = 'Địa chỉ email này đã được sử dụng!';
            if (self::isJsonRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => $error]);
                return;
            }
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        setcookie('session_token', $user['session_token'], time() + 86400 * 7, '/', '', false, false);

        if (self::isJsonRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'user' => $user]);
            return;
        }

        header('Location: /');
    }

    public function logout() {
        setcookie('session_token', '', time() - 3600, '/');
        if (self::isJsonRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            return;
        }
        header('Location: /');
    }

    public function profile() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if ($user) {
            echo json_encode(['loggedIn' => true, 'user' => $user]);
        } else {
            echo json_encode(['loggedIn' => false, 'user' => null]);
        }
    }

    /**
     * Cập nhật thông tin cá nhân - [ĐÃ VÁ CHẶT CHẼ: CHẶN HOÀN TOÀN MASS ASSIGNMENT]
     * Chỉ cho phép đổi name và password, không thể can thiệp role hay loyalty_points
     * Endpoint: PATCH /api/member/profile
     */
    public function updateProfile() {
        header('Content-Type: application/json; charset=utf-8');
        $token = $_COOKIE['session_token'] ?? null;
        $user = User::findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Yêu cầu đăng nhập']);
            return;
        }

        $input = self::getInput();
        $name = $input['name'] ?? null;
        $password = $input['password'] ?? null;

        $updatedUser = User::updateProfile($user['id'], $name, $password);
        echo json_encode(['success' => true, 'user' => $updatedUser ?: $user]);
    }
}
