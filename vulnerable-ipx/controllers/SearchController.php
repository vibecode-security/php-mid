<?php
namespace Controllers;

use Models\Product;
use Models\User;
use PDOException;

class SearchController {
    /**
     * Giao diện tìm kiếm sản phẩm - Điểm kích hoạt Reflected XSS
     * Endpoint: GET /search?q=...
     */
    public function index() {
        $q = $_GET['q'] ?? '';
        $results = [];

        if ($q !== '') {
            try {
                $results = Product::search($q);
            } catch (PDOException $e) {
                $results = [];
            }
        }

        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);

        require __DIR__ . '/../views/search/index.php';
    }

    /**
     * API tìm kiếm
     * Endpoint: GET /api/store/search?q=...
     */
    public function apiSearch() {
        header('Content-Type: application/json; charset=utf-8');
        $q = $_GET['q'] ?? '';
        if ($q === '') {
            echo json_encode([]);
            return;
        }

        try {
            $results = Product::search($q);
            echo json_encode($results, JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
