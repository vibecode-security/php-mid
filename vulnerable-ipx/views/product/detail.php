<?php
$pageTitle = ($product && isset($product['name'])) ? ($product['name'] . ' — BrewCart') : 'Chi tiết sản phẩm — BrewCart';
require __DIR__ . '/../layouts/header.php';

$productImage = $product['image_url'] ?? '/img/americano.jpg';
$productSlug = ($product && isset($product['name'])) ? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $product['name'])) : 'americano';
?>

<section class="section" style="padding-top: 10px;">
    <!-- Nút trở về Cửa hàng (Hỗ trợ cả nút Back trình duyệt và liên kết) -->
    <a class="small" href="/" style="color: var(--coffee-800); font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 20px;">
        &larr; Cửa hàng
    </a>

    <?php if (!empty($sqlError)): ?>
        <!-- ========================================================================= -->
        <!-- LỖI DATABASE SQL INJECTION HIỂN THỊ TRỰC TIẾP TRÊN GIAO DIỆN CHUẨN BREWCART -->
        <!-- Học viên có thể chụp màn hình giao diện hoặc bắt gói tin qua Burp Suite   -->
        <!-- ========================================================================= -->
        <div style="background: #ffffff; border: 2px solid #b91c1c; border-radius: var(--radius); padding: 24px; margin: 10px 0 28px; box-shadow: 0 4px 16px rgba(185, 28, 28, 0.08);">
            <div class="spread" style="margin-bottom: 12px;">
                <div class="row">
                    <span class="pill cancelled" style="font-weight: 800; font-size: 0.8rem; padding: 4px 12px; background: #fee2e2; color: #b91c1c; border-radius: 999px;">
                        ⚠️ LỖI DATABASE (SQL INJECTION)
                    </span>
                    <span class="small muted">MariaDB 10.11 Server</span>
                </div>
            </div>

            <div class="error-box" style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 10px; padding: 16px; color: #991b1b; font-family: monospace; font-size: 0.95rem; line-height: 1.6; word-break: break-all; white-space: pre-wrap;"><strong>[LỖI DATABASE]:</strong><br><?= htmlspecialchars($sqlError) ?></div>

            <div style="margin-top: 16px; font-size: 0.88rem; color: var(--slate-600);">
                <strong>Câu lệnh SQL vừa thực thi:</strong>
                <div style="background: #0f172a; color: #38bdf8; padding: 12px 14px; border-radius: 8px; font-family: monospace; margin-top: 6px; overflow-x: auto;"><?= htmlspecialchars($queryExecuted) ?></div>
            </div>
        </div>

    <?php elseif ($product): ?>
        <!-- ========================================================================= -->
        <!-- HIỂN THỊ CHI TIẾT SẢN PHẨM HOẶC DỮ LIỆU BỊ TRÍCH XUẤT QUA UNION SELECT   -->
        <!-- ========================================================================= -->
        <!-- Debug SQL cho học viên kiểm tra qua F12 Console hoặc Xem nguồn trang -->
        <script>
            console.log("[BrewCart SQL Debug] Query: <?= addslashes($queryExecuted) ?>");
        </script>
        <!-- SQL Query: <?= htmlspecialchars($queryExecuted) ?> -->

        <div class="prod-grid">
            <div class="card thumb" style="aspect-ratio: 1 / 1; position: relative;">
                <span style="position: absolute; top: 12px; left: 12px; background: rgba(30, 20, 15, 0.88); color: #fff; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; z-index: 2;">
                    Mã SP: #<?= htmlspecialchars((string)($product['id'] ?? '')) ?>
                </span>
                <img src="<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Product') ?>">
            </div>
            <div>
                <div class="row" style="gap: 8px; align-items: center; margin-bottom: 8px; flex-wrap: wrap;">
                    <span class="tag" style="background: var(--coffee-700); color: #fff; font-weight: 700; font-size: 0.85rem;">
                        ID: #<?= htmlspecialchars((string)($product['id'] ?? '')) ?>
                    </span>
                    <span class="tag">RANG ĐẬM</span>
                    <span class="tag" style="background: var(--cream-100); border: 1px solid var(--line-200); font-family: monospace;">
                        slug: <?= htmlspecialchars($productSlug) ?>
                    </span>
                </div>

                <!-- Tiêu đề sản phẩm: Nếu UNION SELECT trả về rỗng / NULL thì hiển thị chữ NULL rõ ràng -->
                <h1 style="margin-top: 4px; font-size: 2.2rem;">
                    <?= (isset($product['name']) && trim((string)$product['name']) !== '') ? htmlspecialchars($product['name']) : 'NULL' ?>
                </h1>

                <p class="price" style="font-size: 1.6rem;"><?= number_format((float)($product['price'] ?? 0), 0, ',', '.') ?>₫</p>
                <p class="muted"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                
                <p class="small muted" style="background: var(--cream-100); padding: 9px 13px; border-radius: 8px; margin: 12px 0; border: 1px dashed var(--line-200);">
                    🏷️ <strong>Mã ID: #<?= htmlspecialchars((string)($product['id'] ?? '')) ?></strong> &middot; 
                    📂 Danh mục: Cà phê BrewCart &middot; 
                    📦 Tồn kho: 60 sản phẩm
                </p>

                <div class="row" style="gap: 12px; margin: 16px 0; flex-wrap: wrap;">
                    <div class="qty">
                        <button type="button" aria-label="Giảm" onclick="const i=document.getElementById('detailQty'); i.value=Math.max(1,parseInt(i.value)-1)">&minus;</button>
                        <input type="number" id="detailQty" min="1" max="60" value="1">
                        <button type="button" aria-label="Tăng" onclick="const i=document.getElementById('detailQty'); i.value=parseInt(i.value)+1">&plus;</button>
                    </div>
                    <button class="btn cta" type="button" onclick="alert('Đã thêm sản phẩm #<?= htmlspecialchars((string)($product['id'] ?? '')) ?> vào giỏ hàng!')">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>
        </div>

        <!-- Khu vực Đánh giá sản phẩm & Minh họa Stored XSS -->
        <div style="margin-top: 44px;">
            <h2>Đánh giá từ khách hàng</h2>
            <div id="reviewsList">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="rev">
                            <div class="spread">
                                <b><?= htmlspecialchars($rev['author_name'] ?? 'Khách hàng') ?></b>
                                <span class="muted small">★★★★★ &middot; <?= htmlspecialchars($rev['status'] ?? 'approved') ?></span>
                            </div>
                            <!-- VULNERABLE: Render trực tiếp nội dung đánh giá từ CSDL (Stored XSS) -->
                            <div style="margin-top: 6px;"><?= $rev['body'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="small muted" style="margin: 12px 0;">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên chia sẻ cảm nhận của bạn!</p>
                <?php endif; ?>
            </div>

            <!-- Form gửi đánh giá mới -->
            <form class="card pad" style="margin-top: 24px;" method="POST" action="/api/store/reviews">
                <input type="hidden" name="product_slug" value="<?= htmlspecialchars($productSlug) ?>">
                <h3>Viết đánh giá sản phẩm</h3>
                <p class="small muted">
                    Mọi đánh giá sẽ được gửi tới Ban Quản Trị để duyệt trước khi hiển thị công khai trên website.
                </p>
                <div class="field">
                    <label>Số sao đánh giá</label>
                    <select name="rating" style="width: auto;">
                        <option value="5">5 sao - Rất tuyệt vời</option>
                        <option value="4">4 sao - Hài lòng</option>
                        <option value="3">3 sao - Tạm được</option>
                    </select>
                </div>
                <div class="field">
                    <label>Nội dung nhận xét</label>
                    <textarea name="body" placeholder="Nhập cảm nhận của bạn về sản phẩm..." required></textarea>
                </div>
                <button class="btn" type="submit">Gửi đánh giá</button>
            </form>
        </div>

    <?php elseif ($isFileWriteExecuted): ?>
        <!-- ========================================================================= -->
        <!-- KẾT QUẢ THỰC THI GHI FILE THÀNH CÔNG (SQLi INTO OUTFILE)                  -->
        <!-- ========================================================================= -->
        <div style="background: #ffffff; border: 2px solid #10b981; border-radius: var(--radius); padding: 24px; margin: 20px 0; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.08);">
            <div class="spread" style="margin-bottom: 12px;">
                <div class="row">
                    <span class="pill approved" style="font-weight: 800; font-size: 0.82rem; padding: 4px 12px; background: #dcfce7; color: #15803d; border-radius: 999px;">
                        ✅ THỰC THI THÀNH CÔNG
                    </span>
                    <span class="small muted">SQLi Arbitrary File Write (INTO OUTFILE)</span>
                </div>
            </div>
            <p style="color: #15803d; font-size: 1.05rem; margin: 8px 0; font-weight: 600;">
                Mệnh đề <code>INTO OUTFILE</code> đã được máy chủ MariaDB thực thi thành công! Tệp tin đã được tạo trên hệ thống lưu trữ máy chủ web.
            </p>

            <?php if (!empty($outFileCreated)): ?>
                <div style="background: #f0fdf4; border: 1.5px dashed #22c55e; border-radius: 8px; padding: 14px 18px; margin: 16px 0;">
                    <div style="font-weight: 700; color: #15803d; font-size: 0.95rem; margin-bottom: 4px;">
                        🔗 Kiểm tra và xem trực tiếp tệp vừa tạo trên trình duyệt:
                    </div>
                    <div style="margin-top: 4px;">
                        <a href="<?= htmlspecialchars($outFileCreated['webPath']) ?>" target="_blank" style="font-size: 1.05rem; font-weight: 700; color: #16a34a; text-decoration: underline; font-family: monospace;">
                            http://localhost:3000<?= htmlspecialchars($outFileCreated['webPath']) ?>
                        </a>
                    </div>
                    <div class="small muted" style="margin-top: 6px; color: #166534;">
                        Đường dẫn vật lý trong container MariaDB: <code><?= htmlspecialchars($outFileCreated['fullPath']) ?></code>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 14px; font-size: 0.88rem; color: #475569;">
                <strong>Câu lệnh SQL thực thi:</strong>
                <div style="background: #0f172a; color: #38bdf8; padding: 10px 14px; border-radius: 8px; font-family: monospace; margin-top: 6px; overflow-x: auto;">
                    <?= htmlspecialchars($queryExecuted) ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="card pad" style="text-align: center; padding: 40px 20px;">
            <p style="font-size: 1.1rem; color: var(--slate-600);">
                Vui lòng cung cấp tham số ID sản phẩm hợp lệ, ví dụ: <code><a href="/product?id=1">/product?id=1</a></code>
            </p>
            <a class="btn cta" href="/" style="margin-top: 12px;">&larr; Về trang chủ cửa hàng</a>
        </div>
    <?php endif; ?>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
