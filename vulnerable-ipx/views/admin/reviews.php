<?php
$pageTitle = 'Quản trị duyệt đánh giá — BrewCart';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section" style="padding-top: 15px;">
    <div class="spread" style="margin-bottom: 20px;">
        <div>
            <h2>⚙️ Bảng Quản Trị: Duyệt Đánh Giá Sản Phẩm</h2>
            <p class="small muted">
                Khu vực dành riêng cho Quản trị viên kiểm duyệt đánh giá của khách hàng trước khi xuất bản lên website.
            </p>
        </div>
        <div>
            <span class="pill admin">QUẢN TRỊ VIÊN</span>
        </div>
    </div>

    <div class="card pad" style="background: #ffffff;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--line-200); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Sản phẩm</th>
                    <th style="padding: 10px;">Người gửi</th>
                    <th style="padding: 10px; width: 45%;">Nội dung đánh giá</th>
                    <th style="padding: 10px;">Trạng thái</th>
                    <th style="padding: 10px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="reviewsTableBody">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr id="review-row-<?= $rev['id'] ?>" style="border-bottom: 1px solid var(--line-200); transition: opacity 0.3s ease, transform 0.3s ease;">
                            <td style="padding: 10px;">#<?= htmlspecialchars((string)$rev['id']) ?></td>
                            <td style="padding: 10px;"><code><?= htmlspecialchars($rev['product_slug'] ?? '') ?></code></td>
                            <td style="padding: 10px;"><strong><?= htmlspecialchars($rev['author_name'] ?? '') ?></strong></td>
                            
                            <!-- VULNERABLE: Render nguyên vẹn chuỗi đánh giá từ CSDL (Stored XSS) -->
                            <td style="padding: 10px; line-height: 1.5;">
                                <?= $rev['body'] ?>
                            </td>

                            <td style="padding: 10px;">
                                <?php if (($rev['status'] ?? '') === 'approved'): ?>
                                    <span id="status-badge-<?= $rev['id'] ?>" class="pill approved">Đã duyệt</span>
                                <?php else: ?>
                                    <span id="status-badge-<?= $rev['id'] ?>" class="pill cancelled">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <div class="row" style="gap: 6px; justify-content: center;">
                                    <button class="btn sm cta" type="button" onclick="approveReview(<?= $rev['id'] ?>)">Duyệt</button>
                                    <button class="btn sm danger" type="button" onclick="deleteReview(<?= $rev['id'] ?>)">Xoá</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="noReviewsRow">
                        <td colspan="6" style="padding: 28px; text-align: center;" class="muted">
                            Hiện không có đánh giá nào cần duyệt.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// Thao tác duyệt đánh giá qua AJAX — Không điều hướng trang
async function approveReview(id) {
    try {
        const res = await fetch('/api/admin/reviews/' + id + '/approve', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            const badge = document.getElementById('status-badge-' + id);
            if (badge) {
                badge.className = 'pill approved';
                badge.textContent = 'Đã duyệt';
            }
        } else {
            alert('Lỗi: ' + (data.error || 'Không thể duyệt'));
        }
    } catch (err) {
        alert('Lỗi kết nối khi gửi yêu cầu duyệt');
    }
}

// Thao tác xoá vĩnh viễn đánh giá qua AJAX — Biến mất ngay lập tức và không bao giờ hiện lại
async function deleteReview(id) {
    if (!confirm('Xác nhận xoá vĩnh viễn đánh giá #' + id + '? Sau khi xoá sẽ không hiển thị lại nữa.')) {
        return;
    }

    try {
        const res = await fetch('/api/admin/reviews/' + id + '/reject', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('review-row-' + id);
            if (row) {
                // Hiệu ứng mờ dần và trượt sang bên trước khi xoá khỏi DOM
                row.style.opacity = '0';
                row.style.transform = 'translateX(30px)';
                setTimeout(() => {
                    row.remove();
                    // Nếu đã xoá hết các hàng thì hiện dòng thông báo trống
                    const tbody = document.getElementById('reviewsTableBody');
                    if (tbody && tbody.querySelectorAll('tr').length === 0) {
                        tbody.innerHTML = '<tr id="noReviewsRow"><td colspan="6" style="padding: 28px; text-align: center;" class="muted">Hiện không có đánh giá nào cần duyệt.</td></tr>';
                    }
                }, 300);
            }
        } else {
            alert('Lỗi: ' + (data.error || 'Không thể xoá'));
        }
    } catch (err) {
        alert('Lỗi kết nối khi gửi yêu cầu xoá');
    }
}
</script>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
