<?php
namespace Controllers;

use Models\Product;
use Models\User;

class HomeController {
    /**
     * Trang chủ (Landing Page)
     * Hiển thị banner tìm kiếm trực tiếp, 3 sản phẩm nổi bật và nút xem tất cả sản phẩm
     */
    public function index() {
        $allProducts = Product::getStoreProducts();
        // Lấy 3 sản phẩm nổi bật bán chạy nhất
        $featuredProducts = array_slice($allProducts, 0, 3);
        $categories = Product::getCategories();

        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);

        require __DIR__ . '/../views/home/index.php';
    }

    /**
     * Trang toàn bộ thực đơn / Tất cả sản phẩm
     * Hiển thị đầy đủ sản phẩm kèm bộ lọc danh mục và thanh tìm kiếm
     */
    public function allProducts() {
        $category = $_GET['category'] ?? null;
        $products = Product::getStoreProducts($category);
        $categories = Product::getCategories();
        $selectedCategory = $category;

        $token = $_COOKIE['session_token'] ?? null;
        $currentUser = User::findByToken($token);

        require __DIR__ . '/../views/product/list.php';
    }
}
