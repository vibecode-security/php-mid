<?php
// Layout Header chuẩn của ứng dụng BrewCart
// Chứa toàn bộ CSS, hệ màu sắc, Typography và thanh điều hướng (Navbar)
$currentUser = $currentUser ?? null;
$pageTitle = $pageTitle ?? 'BrewCart — Cà phê rang mộc nguyên chất';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --coffee-900: #451A03;
            --coffee-800: #78350F;
            --coffee-700: #92400E;
            --gold-400: #FBBF24;
            --gold-500: #E8A317;
            --cream-100: #FEF3C7;
            --cream-50: #FFFBEB;
            --paper: #FFFFFF;
            --ink-900: #171717;
            --slate-600: #475569;
            --line-200: #E7E2D8;
            --danger: #B91C1C;
            --ok: #15803D;
            --shadow: 0 1px 2px rgba(69, 26, 3, .04), 0 8px 24px rgba(69, 26, 3, .06);
            --radius: 14px;
            --maxw: 1120px;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', system-ui, sans-serif;
            color: var(--ink-900);
            background: var(--cream-50);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4 {
            font-family: 'Nunito', system-ui, sans-serif;
            letter-spacing: -.03em;
            line-height: 1.1;
            color: var(--coffee-900);
            margin: 0 0 .4em;
        }
        a { color: var(--coffee-800); text-decoration: none; cursor: pointer; }
        a:hover { color: var(--coffee-700); }
        img { max-width: 100%; display: block; }
        .container { max-width: var(--maxw); margin: 0 auto; padding: 0 20px; }
        .row { display: flex; gap: 12px; align-items: center; }
        .spread { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .muted { color: var(--slate-600); }
        .small { font-size: .875rem; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            font: 600 .95rem 'Nunito', sans-serif;
            border: 1px solid var(--coffee-800);
            background: var(--coffee-800);
            color: #fff;
            padding: 9px 18px;
            border-radius: 10px;
            transition: background .2s, color .2s, border-color .2s;
            text-align: center;
        }
        .btn:hover { background: var(--coffee-900); color: #fff; }
        .btn.cta { background: var(--gold-400); border-color: var(--gold-400); color: var(--coffee-900); }
        .btn.cta:hover { background: var(--gold-500); border-color: var(--gold-500); color: var(--coffee-900); }
        .btn.ghost { background: transparent; color: var(--coffee-800); }
        .btn.ghost:hover { background: var(--cream-100); color: var(--coffee-900); }
        .btn.danger { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
        .btn.danger:hover { background: #fecaca; }
        .btn.sm { padding: 6px 14px; font-size: .84rem; }

        label { display: block; font: 600 .85rem 'Nunito', sans-serif; color: var(--coffee-900); margin: 0 0 6px; }
        input, select, textarea {
            width: 100%;
            font: 400 .95rem 'Nunito', sans-serif;
            color: var(--ink-900);
            background: var(--paper);
            border: 1px solid var(--line-200);
            border-radius: 10px;
            padding: 10px 12px;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--coffee-700);
            box-shadow: 0 0 0 3px #78350f1f;
        }
        .field { margin-bottom: 14px; }
        textarea { min-height: 96px; resize: vertical; }

        .card {
            background: var(--paper);
            border: 1px solid var(--line-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .card.pad { padding: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .prod-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 12px; align-items: start; }
        @media (max-width: 760px) { .prod-grid { grid-template-columns: 1fr; } }

        .thumb {
            aspect-ratio: 1 / 1;
            background: var(--cream-100);
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: var(--radius);
        }
        .thumb img { width: 100%; height: 100%; object-fit: cover; }

        .qty {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line-200);
            border-radius: 10px;
            overflow: hidden;
            background: var(--paper);
        }
        .qty button {
            width: 40px;
            height: 40px;
            border: 0;
            background: var(--cream-100);
            color: var(--coffee-900);
            font: 700 1.15rem 'Nunito', sans-serif;
            cursor: pointer;
            transition: background .2s;
        }
        .qty button:hover { background: var(--gold-400); }
        .qty input { width: 52px; text-align: center; border: 0; border-radius: 0; padding: 9px 0; }

        .price { font: 700 1.05rem 'Nunito'; color: var(--coffee-800); }
        .tag {
            display: inline-block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--coffee-700);
            background: var(--cream-100);
            padding: 3px 8px;
            border-radius: 999px;
            width: fit-content;
        }

        .nav {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #fffbebe6;
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--line-200);
        }
        .nav .inner { display: flex; align-items: center; gap: 20px; height: 64px; overflow-x: auto; }
        .brand { font: 700 1.35rem 'Nunito'; color: var(--coffee-900); letter-spacing: -.04em; cursor: pointer; }
        .brand span { color: var(--gold-500); }
        .nav a.link { font: 600 .92rem 'Nunito'; color: var(--coffee-900); padding: 6px 4px; white-space: nowrap; flex-shrink: 0; }
        .nav .brand, .nav .push { flex-shrink: 0; }
        .nav a.link:hover, .nav a.link.active { color: var(--coffee-700); font-weight: 700; }
        .nav .push { margin-left: auto; }
        .badge { background: var(--coffee-800); color: #fff; border-radius: 999px; font-size: .72rem; padding: 1px 7px; margin-left: 4px; }
        .section { padding: 36px 0; }
        .pill { font: 600 .72rem 'Nunito'; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; flex-shrink: 0; }
        .pill.approved { background: #dcfce7; color: #15803d; }
        .pill.cancelled { background: #fee2e2; color: #b91c1c; }
        .pill.admin { background: #fee2e2; color: #b91c1c; font-weight: 700; }
        .pill.customer { background: #e0f2fe; color: #0369a1; font-weight: 700; }
        .rev { border: 1px solid var(--line-200); border-radius: 10px; padding: 14px 16px; margin: 12px 0; background: var(--paper); }
        .footer { border-top: 1px solid var(--line-200); padding: 32px 0; color: var(--slate-600); font-size: .9rem; margin-top: 48px; }

        @media (max-width: 700px) {
            .nav .inner { gap: 12px; overflow-x: auto; }
            .hide-sm { display: none; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="container inner">
            <a class="brand" href="/">Brew<span>Cart</span></a>
            <a class="link" href="/">Trang chủ</a>
            <a class="link" href="/products">Thực đơn</a>
            <a class="link" href="/search">🔍 Tìm kiếm</a>
            
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <a class="link hide-sm" href="/admin/reviews" style="color:var(--danger); font-weight:700;">⚙️ Quản trị</a>
            <?php endif; ?>

            <div class="push row">
                <?php if ($currentUser): ?>
                    <div class="row" style="gap: 8px;">
                        <span class="small" style="font-weight: 700; color: var(--coffee-900);">
                            ☕ <?= htmlspecialchars($currentUser['name']) ?>
                        </span>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <span class="pill admin">Admin</span>
                        <?php else: ?>
                            <span class="pill customer">Khách hàng</span>
                        <?php endif; ?>
                    </div>
                    <a class="btn ghost sm" href="/logout">Đăng xuất</a>
                <?php else: ?>
                    <a class="link small" href="/login" style="font-weight: 600;">Đăng nhập</a>
                    <a class="btn sm cta" href="/register">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="container" style="min-height: 72vh; padding-top: 20px; padding-bottom: 24px;">
