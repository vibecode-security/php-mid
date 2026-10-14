<?php
$pageTitle = '403 Forbidden — Từ chối truy cập';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section" style="padding-top: 30px; display: flex; justify-content: center;">
    <div style="width: 100%; max-width: 520px; text-align: center;">
        <div class="card pad" style="background: #ffffff; padding: 40px 30px;">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">🚫</div>
            <span class="pill cancelled" style="font-size: 0.85rem; padding: 4px 14px;">403 FORBIDDEN</span>
            <h2 style="margin: 14px 0 8px; color: var(--danger);">Từ Chối Truy Cập</h2>
            <p class="muted" style="line-height: 1.6; margin-bottom: 24px;">
                Trang này được bảo vệ nghiêm ngặt và chỉ dành riêng cho tài khoản có vai trò <strong>Quản trị viên (Admin)</strong>. Bạn hiện không có đủ đặc quyền để truy cập tài nguyên này.
            </p>
            <div class="row" style="gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a class="btn cta" href="/login">Đăng nhập tài khoản Admin</a>
                <a class="btn ghost" href="/">&larr; Về trang chủ</a>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
