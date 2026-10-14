<?php
$pageTitle = 'Thực đơn cà phê — BrewCart';
require __DIR__ . '/../layouts/header.php';
$selectedCategory = $selectedCategory ?? null;
?>

<section class="section" style="padding-top: 10px;">
    <!-- Thanh điều hướng & Tìm kiếm nhanh trên đầu trang Thực đơn -->
    <div class="card pad" style="background: #ffffff; margin-bottom: 28px; box-shadow: var(--shadow);">
        <div class="spread">
            <div>
                <h1 style="font-size: 2rem; margin-bottom: 4px;">Thực đơn Cà phê BrewCart</h1>
                <p class="small muted" style="margin: 0;">Khám phá toàn bộ các dòng cà phê rang mộc nguyên chất và cà phê pha máy phong cách Ý</p>
            </div>
            <!-- Ô tìm kiếm nhanh trên trang Thực đơn -->
            <form method="GET" action="/search" style="min-width: 300px;">
                <div class="row" style="background: var(--cream-50); border: 1px solid var(--line-200); border-radius: 10px; padding: 4px 6px;">
                    <input type="text" name="q" placeholder="Tìm kiếm sản phẩm..." style="border: none; background: transparent; padding: 6px 10px; font-size: 0.9rem;" required>
                    <button class="btn sm cta" type="submit">Tìm</button>
                </div>
            </form>
        </div>

        <!-- Bộ lọc danh mục -->
        <div class="row" style="gap: 8px; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line-200); flex-wrap: wrap;">
            <span class="small muted" style="font-weight: 700; margin-right: 4px;">Danh mục:</span>
            <a class="btn sm <?= empty($selectedCategory) ? 'cta' : 'ghost' ?>" href="/products">Tất cả (<?= count($products) ?>)</a>
            <?php foreach ($categories as $cat): ?>
                <a class="btn sm <?= ($selectedCategory === $cat['slug']) ? 'cta' : 'ghost' ?>" href="/products?category=<?= urlencode($cat['slug']) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lưới toàn bộ sản phẩm -->
    <div class="grid">
        <?php foreach ($products as $p): ?>
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
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
