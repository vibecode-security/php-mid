<?php
$pageTitle = 'Tìm kiếm sản phẩm — BrewCart';
require __DIR__ . '/../layouts/header.php';
$q = $q ?? '';
?>

<section class="section" style="padding-top: 15px;">
    <div style="max-width: 760px; margin: 0 auto;">
        <div class="card pad" style="background: #ffffff;">
            <h2>🔍 Tìm kiếm Cà phê BrewCart</h2>
            <p class="small muted">
                Tìm kiếm các dòng cà phê rang mộc thượng hạng tại BrewCart theo tên hoặc hương vị đặc trưng.
            </p>

            <form method="GET" action="/search" style="margin-top: 16px;">
                <div class="row" style="gap: 10px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nhập tên sản phẩm (ví dụ: Americano, Mocha, Latte...)" style="flex: 1;" required>
                    <button class="btn cta" type="submit">Tìm kiếm</button>
                </div>
            </form>

            <?php if ($q !== ''): ?>
                <div style="margin-top: 24px; padding: 18px; background: var(--cream-50); border: 1px solid var(--line-200); border-radius: 10px;">
                    <h3 style="margin: 0 0 10px;">Kết quả tìm kiếm cho: <?= $q ?></h3>

                    <?php if (!empty($results)): ?>
                        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; margin-top: 14px;">
                            <?php foreach ($results as $item): ?>
                                <div class="card pad" style="padding: 12px; background: #fff;">
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <div class="price" style="font-size: 1rem; margin: 6px 0;"><?= number_format((float)$item['price'], 0, ',', '.') ?>₫</div>
                                    <a class="btn sm ghost" href="/product?id=<?= $item['id'] ?>">Xem chi tiết</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="small muted" style="margin: 0;">Không tìm thấy sản phẩm nào khớp với từ khoá trên.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 20px;">
                <a class="small" href="/">&larr; Về trang chủ BrewCart</a>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
