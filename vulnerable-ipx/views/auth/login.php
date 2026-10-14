<?php
$pageTitle = 'Đăng nhập — BrewCart';
require __DIR__ . '/../layouts/header.php';
$error = $error ?? null;
?>

<section class="section" style="padding-top: 20px; display: flex; justify-content: center;">
    <div style="width: 100%; max-width: 440px;">
        <div class="card pad" style="background: #ffffff; box-shadow: var(--shadow);">
            <div style="text-align: center; margin-bottom: 24px;">
                <span class="tag" style="background: var(--cream-100); color: var(--coffee-800); font-weight: 700;">TÀI KHOẢN HỆ THỐNG</span>
                <h2 style="margin: 8px 0 4px; font-size: 1.8rem;">Đăng Nhập</h2>
                <p class="small muted">Chào mừng bạn quay trở lại với BrewCart</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px 14px; color: #991b1b; font-size: 0.9rem; margin-bottom: 18px;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="field">
                    <label for="loginEmail">Địa chỉ Email</label>
                    <input type="email" id="loginEmail" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>

                <div class="field">
                    <label for="loginPassword">Mật khẩu</label>
                    <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                </div>

                <div style="margin-top: 22px;">
                    <button class="btn cta" style="width: 100%; padding: 11px;" type="submit">Đăng nhập</button>
                </div>
            </form>

            <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;" class="muted">
                Chưa có tài khoản? <a href="/register" style="color: var(--coffee-800); font-weight: 700;">Đăng ký ngay</a>
            </div>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--line-200); font-size: 0.82rem; color: var(--slate-600);">
                <strong>Tài khoản có sẵn:</strong><br>
                &bull; Quản trị viên: <code>admin@gmail.com</code> / <code>Admin123!</code><br>
                &bull; Khách hàng: <code>huy@gmail.com</code> / <code>user123</code>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
