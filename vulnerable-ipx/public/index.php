<?php
// =========================================================================
// BREWCART E-COMMERCE LAB — FRONT CONTROLLER & ROUTER
// Phục vụ chuyên đề thực hành: SQL Injection (SQLi) & Cross-Site Scripting (XSS)
// =========================================================================

// Autoloader đơn giản cho các Namespace: Config, Models, Controllers
spl_autoload_register(function ($class) {
    $prefix = [
        'Config\\' => __DIR__ . '/../config/',
        'Models\\' => __DIR__ . '/../models/',
        'Controllers\\' => __DIR__ . '/../controllers/',
    ];

    foreach ($prefix as $ns => $dir) {
        if (strpos($class, $ns) === 0) {
            $relativeClass = substr($class, strlen($ns));
            $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// Chuẩn hóa path loại bỏ trailing slash nếu không phải root
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// =========================================================================
// BẢNG ĐIỀU HƯỚNG ROUTING
// =========================================================================

// 1. Phục vụ Trang chủ (Landing Page với sản phẩm nổi bật & tìm kiếm)
if ($path === '' || $path === '/') {
    (new Controllers\HomeController())->index();
    exit;
}

// 1.1 Phục vụ Toàn bộ Thực đơn / Tất cả sản phẩm
if ($path === '/products' || $path === '/menu') {
    (new Controllers\HomeController())->allProducts();
    exit;
}

// 2. Chi tiết sản phẩm & Điểm kiểm thử SQL Injection (GET /product?id=...)
if ($path === '/product' || $path === '/product.php') {
    (new Controllers\ProductController())->show();
    exit;
}

// 3. Tìm kiếm sản phẩm (Reflected XSS)
if ($path === '/search') {
    (new Controllers\SearchController())->index();
    exit;
}

// 4. Panel Quản trị duyệt bài (Stored XSS)
if ($path === '/admin/reviews') {
    (new Controllers\ReviewController())->adminIndex();
    exit;
}

// 5. Đăng nhập, Đăng ký & Đăng xuất
if ($path === '/login') {
    if ($method === 'POST') {
        (new Controllers\AuthController())->login();
    } else {
        (new Controllers\AuthController())->showLogin();
    }
    exit;
}

if ($path === '/register') {
    if ($method === 'POST') {
        (new Controllers\AuthController())->register();
    } else {
        (new Controllers\AuthController())->showRegister();
    }
    exit;
}

if ($path === '/logout') {
    (new Controllers\AuthController())->logout();
    exit;
}

// =========================================================================
// API ENDPOINTS (Hỗ trợ kịch bản kiểm thử bảo mật & AJAX)
// =========================================================================

// Xác thực & Tài khoản
if (($path === '/api/auth/login' || $path === '/login') && $method === 'POST') {
    (new Controllers\AuthController())->login();
    exit;
}
if (($path === '/api/auth/register' || $path === '/register') && $method === 'POST') {
    (new Controllers\AuthController())->register();
    exit;
}
if (($path === '/api/auth/logout' && $method === 'POST') || $path === '/logout') {
    (new Controllers\AuthController())->logout();
    exit;
}
if (($path === '/api/member/profile' || $path === '/api/member/me')) {
    if ($method === 'PATCH' || $method === 'POST') {
        (new Controllers\AuthController())->updateProfile();
    } else {
        (new Controllers\AuthController())->profile();
    }
    exit;
}

// API Cửa hàng sản phẩm (Hỗ trợ Blind SQLi / UNION SQLi)
if ($path === '/api/store/products' && $method === 'GET') {
    (new Controllers\ProductController())->apiProducts();
    exit;
}
if ($path === '/api/store/categories' && $method === 'GET') {
    (new Controllers\ProductController())->apiCategories();
    exit;
}
if ($path === '/api/store/search' && $method === 'GET') {
    (new Controllers\SearchController())->apiSearch();
    exit;
}

// Đánh giá sản phẩm (Stored XSS)
if ($path === '/api/store/reviews') {
    if ($method === 'POST') {
        (new Controllers\ReviewController())->submit();
    } else {
        (new Controllers\ReviewController())->apiStoreReviews();
    }
    exit;
}
if (preg_match('#^/api/member/products/([^/]+)/reviews$#', $path, $matches) && $method === 'POST') {
    (new Controllers\ReviewController())->submit($matches[1]);
    exit;
}
if ($path === '/api/admin/reviews' && $method === 'GET') {
    (new Controllers\ReviewController())->apiAdminReviews();
    exit;
}
if (preg_match('#^/api/admin/reviews/(\d+)/approve$#', $path, $matches) && $method === 'POST') {
    (new Controllers\ReviewController())->approve($matches[1]);
    exit;
}
if (preg_match('#^/api/admin/reviews/(\d+)/reject$#', $path, $matches) && $method === 'POST') {
    (new Controllers\ReviewController())->reject($matches[1]);
    exit;
}

// Quản trị người dùng (Kiểm soát Broken Access Control)
if (($path === '/api/admin/customers' || $path === '/api/admin/users') && $method === 'GET') {
    (new Controllers\AdminController())->customers();
    exit;
}

// Quản lý đơn hàng (Kiểm soát IDOR)
if ($path === '/api/member/orders' && $method === 'GET') {
    (new Controllers\OrderController())->list();
    exit;
}
if (preg_match('#^/api/member/orders/(\d+)$#', $path, $matches) && $method === 'GET') {
    (new Controllers\OrderController())->detail($matches[1]);
    exit;
}

// Hỗ trợ tickets & Tải tệp XSS
if ($path === '/api/member/support/tickets') {
    if ($method === 'POST') {
        (new Controllers\TicketController())->create();
    } else {
        (new Controllers\TicketController())->index();
    }
    exit;
}
if (preg_match('#^/api/member/support/tickets/(\d+)/attachments$#', $path, $matches) && $method === 'POST') {
    (new Controllers\TicketController())->uploadAttachment($matches[1]);
    exit;
}

// Phục vụ tệp trong thư mục uploads (Tệp tạo bởi INTO OUTFILE hoặc Upload Attachment)
if (strpos($path, '/uploads/') === 0) {
    $filename = basename($path);
    $possiblePaths = [
        __DIR__ . '/uploads/' . $filename,
        __DIR__ . '/../uploads/' . $filename,
        '/app/uploads/' . $filename,
        '/var/www/html/public/uploads/' . $filename,
        '/var/www/html/uploads/' . $filename,
    ];

    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $contentType = 'text/plain; charset=utf-8';
            if ($ext === 'html' || $ext === 'htm') {
                $contentType = 'text/html; charset=utf-8';
            } elseif ($ext === 'jpg' || $ext === 'jpeg') {
                $contentType = 'image/jpeg';
            } elseif ($ext === 'png') {
                $contentType = 'image/png';
            }
            header('Content-Type: ' . $contentType);
            readfile($filePath);
            exit;
        }
    }

    // Nếu tệp chưa tồn tại: Trả về 404 rõ ràng, TUYỆT ĐỐI KHÔNG redirect về localhost
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>404 Tệp không tồn tại</title></head><body style='font-family: sans-serif; padding: 40px; text-align: center;'>";
    echo "<h2 style='color: #b91c1c;'>404 - Tệp tin chưa tồn tại trên máy chủ</h2>";
    echo "<p>Tệp <code>" . htmlspecialchars($filename) . "</code> chưa được tạo trong thư mục <code>/uploads</code>.</p>";
    echo "<p style='color: #64748b;'>Vui lòng thực thi lệnh SQL Injection với <code>INTO OUTFILE '/app/uploads/" . htmlspecialchars($filename) . "'</code> trước khi truy cập!</p>";
    echo "<a href='/' style='color: #2563eb;'>&larr; Về trang chủ</a>";
    echo "</body></html>";
    exit;
}

// Fallback trang không tìm thấy: Trả về HTTP 404 thay vì redirect về trang chủ
http_response_code(404);
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h2>404 - Trang không tồn tại</h2><p><a href='/'>&larr; Về trang chủ</a></p></body></html>";
exit;
