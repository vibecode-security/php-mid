<?php
$pageTitle = 'Đăng ký tài khoản — BrewCart';
require __DIR__ . '/../layouts/header.php';
$error = $error ?? null;
?>

<section class="section" style="padding-top: 20px; display: flex; justify-content: center;">
    <div style="width: 100%; max-width: 480px;">
        <div class="card pad" style="background: #ffffff; box-shadow: var(--shadow);">
            <div style="text-align: center; margin-bottom: 24px;">
                <span class="tag" style="background: var(--cream-100); color: var(--coffee-800); font-weight: 700;">THÀNH VIÊN MỚI</span>
                <h2 style="margin: 8px 0 4px; font-size: 1.8rem;">Đăng Ký Tài Khoản</h2>
                <p class="small muted">Trở thành thành viên của BrewCart để nhận ưu đãi</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px 14px; color: #991b1b; font-size: 0.9rem; margin-bottom: 18px;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <div class="field">
                    <label for="regName">Họ và tên</label>
                    <input type="text" id="regName" name="name" placeholder="Nguyễn Văn A" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus>
                </div>

                <div class="field">
                    <label for="regEmail">Địa chỉ Email</label>
                    <input type="email" id="regEmail" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label for="regPassword">Mật khẩu</label>
                    <input type="password" id="regPassword" name="password" placeholder="Tối thiểu 6 ký tự" required>
                </div>

                <div class="field">
                    <label for="regConfirmPassword">Xác nhận mật khẩu</label>
                    <input type="password" id="regConfirmPassword" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                </div>

                <div style="margin-top: 22px;">
                    <button class="btn cta" style="width: 100%; padding: 11px;" type="submit">Tạo tài khoản</button>
                </div>
            </form>

            <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;" class="muted">
                Đã có tài khoản? <a href="/login" style="color: var(--coffee-800); font-weight: 700;">Đăng nhập ngay</a>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
