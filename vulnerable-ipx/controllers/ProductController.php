<?php
namespace Controllers;

use Models\Product;
use Models\Review;
use Models\User;
use PDOException;

class ProductController {
    /**
     * Hiển thị chi tiết sản phẩm & Điểm kiểm thử SQL Injection chính
     * Endpoint: GET /product?id=...
     */
    public function show() {
        $id = $_GET['id'] ?? null;
        $product = null;
        $sqlError = null;
        $queryExecuted = '';
        $isFileWriteExecuted = false;
        $outFileCreated = null;
        $isSqlInjection = false;
        $reviews = [];

        if ($id !== null && $id !== '') {
            $isSqlInjection = (
                stripos($id, 'union') !== false || 
                stripos($id, 'select') !== false || 
                stripos($id, '--') !== false || 
                stripos($id, '/*') !== false || 
                stripos($id, "'") !== false ||
                stripos($id, 'sleep') !== false ||
                stripos($id, 'benchmark') !== false ||
                stripos($id, 'secure_file_priv') !== false
            );

            try {
                $product = Product::findByIdVulnerable($id, $queryExecuted);

                // Kiểm tra nếu payload là lệnh ghi file INTO OUTFILE thành công
                if (!$product && stripos($id, 'into') !== false && stripos($id, 'outfile') !== false) {
                    $isFileWriteExecuted = true;
                    if (preg_match("/into\s+outfile\s+['\"]([^'\"]+)['\"]/i", $id, $matches)) {
                        $fullPath = $matches[1];
                        $filename = basename($fullPath);
                        $outFileCreated = [
                            'fullPath' => $fullPath,
                            'webPath' => '/uploads/' . $filename,
                            'filename' => $filename
                        ];
                    }
                }
            } catch (PDOException $e) {
                // Nhả lỗi MariaDB chi tiết ra response phục vụ chụp ảnh / Burp Suite
                $sqlError = $e->getMessage();
            }
        }

        if ($product) {
            $productSlug = (!empty($product['name'])) 
                ? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $product['name'])) 
                : 'americano';
            $reviews = Review::getApproved($productSlug);
        }

        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);

        require __DIR__ . '/../views/product/detail.php';
    }

    /**
     * API danh sách sản phẩm - Hỗ trợ Time-based Blind SQLi & UNION SQLi
     * Endpoint: GET /api/store/products
     */
    public function apiProducts() {
        header('Content-Type: application/json; charset=utf-8');
        $category = $_GET['category'] ?? null;
        $id = $_GET['id'] ?? null;

        try {
            $products = Product::getStoreProducts($category, $id);
            echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API danh mục sản phẩm
     * Endpoint: GET /api/store/categories
     */
    public function apiCategories() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $categories = Product::getCategories();
            echo json_encode($categories, JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
