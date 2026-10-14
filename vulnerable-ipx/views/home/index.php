<?php
$pageTitle = 'BrewCart — Cà phê rang mộc nguyên chất';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section" style="padding-top: 10px;">
    <!-- Banner Hero & Thanh Tìm Kiếm Trực Tiếp -->
    <div style="background: linear-gradient(135deg, #451A03 0%, #78350F 100%); color: #fff; padding: 48px 40px; border-radius: var(--radius); margin-bottom: 40px; box-shadow: var(--shadow);">
        <div style="max-width: 760px;">
            <span class="tag" style="background: #FBBF24; color: #451A03; font-weight: 800; margin-bottom: 14px; letter-spacing: 0.08em;">PREMIUM ARTISAN COFFEE</span>
            <h1 style="color: #fff; font-size: 2.6rem; margin: 10px 0 14px; line-height: 1.15;">Cà phê rang mộc nguyên chất</h1>
            <p style="color: #FEF3C7; font-size: 1.1rem; line-height: 1.6; margin: 0 0 24px;">
                Hạt cà phê thượng hạng tuyển chọn từ cao nguyên Đắk Lắk, Cầu Đất &amp; phong cách Ý. Rang mộc thủ công, giữ trọn vẹn hương vị tự nhiên và hậu vị sâu lắng.
            </p>

            <!-- Thanh tìm kiếm trực tiếp ngay trên trang chủ -->
            <form method="GET" action="/search" style="max-width: 620px;">
                <div class="row" style="background: #ffffff; padding: 6px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.2);">
                    <span style="padding-left: 14px; font-size: 1.2rem; color: var(--coffee-800);">🔍</span>
                    <input type="text" name="q" placeholder="Tìm kiếm loại cà phê yêu thích (Americano, Mocha, Arabica...)" style="border: none; outline: none; box-shadow: none; padding: 10px 12px; flex: 1; font-size: 0.98rem; background: transparent;" required>
                    <button class="btn cta" type="submit" style="padding: 10px 22px; border-radius: 8px;">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Khu vực Sản phẩm nổi bật (3 sản phẩm tiêu biểu) -->
    <div style="margin-bottom: 28px;">
        <div class="spread" style="margin-bottom: 24px;">
            <div>
                <h2 style="font-size: 1.85rem; margin-bottom: 4px;">⭐ Sản phẩm nổi bật</h2>
                <p class="small muted">Những hương vị cà phê hảo hạng được khách hàng yêu thích và đặt mua nhiều nhất</p>
            </div>
            <a class="btn sm ghost" href="/products" style="font-weight: 700;">
                Xem tất cả thực đơn &rarr;
            </a>
        </div>

        <div class="grid">
            <?php foreach ($featuredProducts as $p): ?>
                <div class="card" style="overflow: hidden; display: flex; flex-direction: column;">
                    <a href="/product?id=<?= $p['id'] ?>" class="thumb" style="position: relative; text-decoration: none;">
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    </a>
                    <div class="card pad" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div class="row" style="justify-content: space-between; margin-bottom: 6px;">
                                <span class="tag"><?= htmlspecialchars($p['roast_label'] ?? 'RANG VỪA') ?></span>
                                <span class="small muted"><?= htmlspecialchars($p['category'] ?? 'Cà phê') ?></span>
                            </div>
                            <h3 style="margin: 4px 0;">
                                <a href="/product?id=<?= $p['id'] ?>" style="color: var(--coffee-900);">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h3>
                            <p class="small muted" style="margin: 4px 0 14px; line-height: 1.45;">
                                <?= htmlspecialchars($p['description'] ?? '') ?>
                            </p>
                        </div>
                        <div>
                            <div class="spread" style="margin-bottom: 14px; align-items: baseline;">
                                <span class="price" style="font-size: 1.3rem;">
                                    <?= number_format($p['price'], 0, ',', '.') ?>₫
                                </span>
                                <span class="small muted">Kho: <?= (int)$p['stock'] ?></span>
                            </div>
                            <a class="btn sm cta" style="width: 100%;" href="/product?id=<?= $p['id'] ?>">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Nút xem tất cả sản phẩm chính giữa -->
    <div style="text-align: center; margin: 44px 0 48px;">
        <a class="btn cta" href="/products" style="padding: 14px 40px; font-size: 1.05rem; font-weight: 700; border-radius: 999px; box-shadow: 0 4px 16px rgba(232, 163, 23, 0.3);">
            Xem tất cả sản phẩm &rarr;
        </a>
    </div>

    <!-- Cam kết thương hiệu BrewCart -->
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 24px;">
        <div class="card pad" style="background: #ffffff; text-align: center; padding: 28px 20px;">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">🌿</div>
            <h3 style="font-size: 1.15rem; margin-bottom: 6px;">100% Rang Mộc</h3>
            <p class="small muted" style="margin: 0;">Không pha trộn bột bắp, đậu nành hay hương liệu nhân tạo, giữ trọn vẹn vị mộc tinh khiết.</p>
        </div>
        <div class="card pad" style="background: #ffffff; text-align: center; padding: 28px 20px;">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">☕</div>
            <h3 style="font-size: 1.15rem; margin-bottom: 6px;">Hạt Cà Phê Tuyển Chọn</h3>
            <p class="small muted" style="margin: 0;">100% hái chín thủ công từ các vùng trồng nổi tiếng như Cầu Đất, Buôn Ma Thuột &amp; Đắk Nông.</p>
        </div>
        <div class="card pad" style="background: #ffffff; text-align: center; padding: 28px 20px;">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">🚀</div>
            <h3 style="font-size: 1.15rem; margin-bottom: 6px;">Giao Hàng 24 Giờ</h3>
            <p class="small muted" style="margin: 0;">Đóng gói van một chiều bảo quản và giao hàng hoả tốc nội thành trong ngày.</p>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
