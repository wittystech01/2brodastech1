<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$user_id    = (int)($_SESSION['user_id'] ?? 0);
$user       = [];
$orders     = [];
$order_count = 0;
$wish_count  = 0;
$sub_count   = 0;

if ($user_id > 0) {
    try {
        $db = getDB();
        $res = $db->query("SELECT * FROM users WHERE id=$user_id AND status='active' LIMIT 1");
        $user = $res ? $res->fetch_assoc() : [];

        $ores = $db->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 5");
        $orders = $ores ? $ores->fetch_all(MYSQLI_ASSOC) : [];

        $cnt = $db->query("SELECT COUNT(*) c FROM orders WHERE user_id=$user_id");
        $order_count = $cnt ? (int)$cnt->fetch_assoc()['c'] : 0;

        $wc = $db->query("SELECT COUNT(*) c FROM wishlists WHERE user_id=$user_id");
        $wish_count = $wc ? (int)$wc->fetch_assoc()['c'] : 0;

        $sc = $db->query("SELECT COUNT(*) c FROM subscriptions WHERE user_id=$user_id AND status='active'");
        $sub_count = $sc ? (int)$sc->fetch_assoc()['c'] : 0;
    } catch (Exception $e) {
        $user = [];
    }
}

$user_name  = $user['full_name'] ?? $_SESSION['user_name'] ?? 'Guest';
$user_email = $user['email']     ?? $_SESSION['user_email'] ?? '';
$is_logged  = $user_id > 0;

$status_classes = [
    'pending'   => 'status-pending',
    'confirmed' => 'status-confirmed',
    'shipped'   => 'status-shipped',
    'delivered' => 'status-delivered',
    'cancelled' => 'status-cancelled',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — GadgetZone</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/mobile.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;color:#333;background:#f8f9fa}
        a{text-decoration:none;color:inherit}
        .container{max-width:1200px;margin:0 auto;padding:0 20px}
        .navbar{background:#1e3a5f;padding:0;position:sticky;top:0;z-index:999;box-shadow:0 2px 10px rgba(0,0,0,.15)}
        .navbar .container{display:flex;align-items:center;gap:20px;height:64px}
        .nav-brand .logo{display:flex;align-items:center;gap:8px;color:#fff;font-size:1.4rem;font-weight:800}
        .nav-search{flex:1;max-width:400px}
        .search-form{display:flex;background:rgba(255,255,255,.12);border-radius:8px;overflow:hidden}
        .search-input{flex:1;background:transparent;border:none;padding:10px 14px;color:#fff;font-size:.9rem;outline:none}
        .search-input::placeholder{color:rgba(255,255,255,.6)}
        .search-btn{background:transparent;border:none;color:#fff;padding:0 14px;cursor:pointer}
        .nav-links{display:flex;gap:4px}
        .nav-links a{color:rgba(255,255,255,.85);padding:8px 12px;border-radius:6px;font-size:.9rem}
        .nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
        .nav-icons{display:flex;align-items:center;gap:4px}
        .nav-icon{color:rgba(255,255,255,.85);padding:8px 10px;border-radius:6px;font-size:1rem;position:relative}
        .nav-icon:hover{background:rgba(255,255,255,.1);color:#fff}
        .cart-badge{background:#ff6b35;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;position:absolute;top:4px;right:4px}
        .hamburger{display:none;background:none;border:none;cursor:pointer;padding:4px}
        .hamburger span{display:block;width:24px;height:2px;background:#fff;margin:5px 0}
        .mobile-menu{display:none;position:fixed;top:0;right:-100%;width:280px;height:100vh;background:#fff;z-index:1001;transition:right .3s;overflow-y:auto}
        .mobile-menu.open{right:0}
        .mobile-menu-header{background:#1e3a5f;color:#fff;padding:20px;display:flex;justify-content:space-between;align-items:center}
        .mobile-menu-header .logo-text{font-size:1.3rem;font-weight:800}
        .close-menu{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer}
        .mobile-nav-links{padding:16px 0}
        .mobile-nav-links a{display:block;padding:14px 20px;color:#333;border-bottom:1px solid #f0f0f0;font-weight:500}
        .mobile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000}
        .mobile-overlay.open{display:block}
        .page-header{background:#1e3a5f;color:#fff;padding:32px 0}
        .page-header h1{font-size:1.8rem;font-weight:700;margin-bottom:6px}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;opacity:.8}
        .breadcrumb a{color:rgba(255,255,255,.8)}
        .dashboard-page{padding:40px 0}
        .dashboard-layout{display:grid;grid-template-columns:280px 1fr;gap:24px}
        /* Sidebar */
        .dash-sidebar{height:fit-content;position:sticky;top:80px}
        .user-card{background:#fff;border-radius:14px;padding:24px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:16px}
        .user-avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#ff6b35);display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 14px}
        .user-name{font-size:1.1rem;font-weight:700;color:#1e3a5f;margin-bottom:4px}
        .user-email{font-size:.85rem;color:#888}
        .user-since{font-size:.78rem;color:#aaa;margin-top:4px}
        .dash-nav{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
        .dash-nav a{display:flex;align-items:center;gap:12px;padding:14px 20px;color:#555;font-size:.9rem;font-weight:500;border-bottom:1px solid #f8f9fa;transition:all .2s}
        .dash-nav a:last-child{border-bottom:none}
        .dash-nav a:hover,.dash-nav a.active{background:#f0f4ff;color:#1e3a5f;font-weight:600}
        .dash-nav a.logout{color:#ef4444}
        .dash-nav a.logout:hover{background:#fef2f2}
        .dash-nav-icon{font-size:1.1rem}
        /* Main */
        .dash-main{display:flex;flex-direction:column;gap:24px}
        .welcome-banner{background:linear-gradient(135deg,#1e3a5f,#2d5f8f);color:#fff;border-radius:14px;padding:28px;display:flex;justify-content:space-between;align-items:center}
        .welcome-text h2{font-size:1.5rem;font-weight:700;margin-bottom:8px}
        .welcome-text p{opacity:.85;font-size:.95rem}
        .welcome-emoji{font-size:4rem}
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .stat-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;transition:transform .2s}
        .stat-card:hover{transform:translateY(-3px)}
        .stat-icon{font-size:2.5rem;margin-bottom:12px}
        .stat-value{font-size:2rem;font-weight:800;color:#1e3a5f;margin-bottom:4px}
        .stat-label{font-size:.85rem;color:#888}
        .stat-card.orders{border-top:4px solid #3b82f6}
        .stat-card.wishlist{border-top:4px solid #ec4899}
        .stat-card.subs{border-top:4px solid #8b5cf6}
        /* Orders Table */
        .section-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
        .section-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:2px solid #f0f0f0}
        .section-header h3{font-size:1rem;font-weight:700;color:#1e3a5f}
        .view-all{font-size:.85rem;color:#ff6b35;font-weight:600}
        .view-all:hover{text-decoration:underline}
        .orders-table{width:100%;border-collapse:collapse}
        .orders-table th{padding:12px 20px;text-align:left;font-size:.82rem;font-weight:700;color:#888;background:#f8f9fa;text-transform:uppercase;letter-spacing:.5px}
        .orders-table td{padding:14px 20px;border-bottom:1px solid #f8f9fa;font-size:.9rem;vertical-align:middle}
        .orders-table tr:last-child td{border-bottom:none}
        .order-num{font-weight:700;color:#1e3a5f}
        .status-badge{padding:4px 10px;border-radius:20px;font-size:.75rem;font-weight:700}
        .status-pending{background:#fef3c7;color:#d97706}
        .status-confirmed,.status-processing{background:#dbeafe;color:#1d4ed8}
        .status-shipped{background:#e0e7ff;color:#4f46e5}
        .status-delivered{background:#dcfce7;color:#15803d}
        .status-cancelled{background:#fee2e2;color:#dc2626}
        .btn-view{background:#1e3a5f;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:.8rem;cursor:pointer;font-weight:600}
        .btn-view:hover{background:#0f2840}
        .empty-state{text-align:center;padding:40px}
        .empty-state .empty-icon{font-size:3rem;margin-bottom:12px}
        .empty-state p{color:#888;font-size:.9rem}
        /* Guest state */
        .guest-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:48px 32px;text-align:center}
        .guest-card h2{font-size:1.5rem;color:#1e3a5f;margin-bottom:12px}
        .guest-card p{color:#888;margin-bottom:24px;line-height:1.6}
        .auth-buttons{display:flex;justify-content:center;gap:16px;flex-wrap:wrap}
        .btn-login{background:#ff6b35;color:#fff;padding:12px 28px;border-radius:10px;font-weight:700;font-size:1rem}
        .btn-login:hover{background:#e55a25}
        .btn-register{background:#1e3a5f;color:#fff;padding:12px 28px;border-radius:10px;font-weight:700;font-size:1rem}
        .btn-register:hover{background:#0f2840}
        .footer{background:#1e3a5f;color:#fff;padding:60px 0 20px}
        .footer-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:40px;margin-bottom:40px}
        .footer h3,.footer h4{margin-bottom:16px}
        .footer p{opacity:.8;line-height:1.6;font-size:.9rem}
        .footer ul{list-style:none}
        .footer ul li{margin-bottom:8px}
        .footer ul a{opacity:.8;font-size:.9rem}
        .footer ul a:hover{opacity:1;color:#ff6b35}
        .social-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .social-link{background:rgba(255,255,255,.1);padding:6px 14px;border-radius:20px;font-size:.8rem}
        .newsletter-form{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
        .newsletter-form input{flex:1;padding:10px 14px;border-radius:6px;border:none;font-size:.9rem;min-width:140px}
        .newsletter-form .btn-primary{background:#ff6b35;color:#fff;padding:10px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer}
        .trust-badges{display:flex;flex-wrap:wrap;gap:8px}
        .trust-badge{background:rgba(255,255,255,.1);padding:4px 10px;border-radius:12px;font-size:.75rem}
        .footer-bottom{border-top:1px solid rgba(255,255,255,.1);padding-top:20px;text-align:center;font-size:.85rem;opacity:.7}
        .footer-bottom a{opacity:.8;margin:0 4px}
        .bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem}
        .bottom-nav-item:hover,.bottom-nav-item.active{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}}
        @media(max-width:768px){.dashboard-layout{grid-template-columns:1fr}.dash-sidebar{position:static}.stats-grid{grid-template-columns:1fr 1fr}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.stats-grid{grid-template-columns:1fr}.welcome-banner{flex-direction:column;text-align:center}.welcome-emoji{display:none}.footer-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-brand"><a href="<?= SITE_URL ?>/index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">GadgetZone</span></a></div>
        <div class="nav-search">
            <form action="<?= SITE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search smartphones, gadgets..." class="search-input">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php">Home</a>
            <a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a>
            <a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a>
            <a href="<?= SITE_URL ?>/shop.php">Gadgets</a>
            <a href="<?= SITE_URL ?>/blog.php">Blog</a>
            <a href="<?= SITE_URL ?>/contact.php">Contact</a>
        </div>
        <div class="nav-icons">
            <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon" title="Wishlist">♡</a>
            <a href="<?= SITE_URL ?>/dashboard.php" class="nav-icon" title="Profile">👤</a>
            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon cart-icon" title="Cart">🛒 <span class="cart-badge" id="cartBadge"><?= getCartCount() ?></span></a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header"><span class="logo-text">GadgetZone</span><button class="close-menu" id="closeMenu">✕</button></div>
    <nav class="mobile-nav-links">
        <a href="<?= SITE_URL ?>/index.php">🏠 Home</a>
        <a href="<?= SITE_URL ?>/shop.php">🛍️ Shop</a>
        <a href="<?= SITE_URL ?>/shop.php?category=smartphones">📱 Smartphones</a>
        <a href="<?= SITE_URL ?>/shop.php?category=accessories">🎧 Accessories</a>
        <a href="<?= SITE_URL ?>/video.php">🎥 Video Gallery</a>
        <a href="<?= SITE_URL ?>/blog.php">📝 Blog</a>
        <a href="<?= SITE_URL ?>/contact.php">📞 Contact</a>
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
        <a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>👤 My Dashboard</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Dashboard</div>
    </div>
</div>

<div class="dashboard-page">
    <div class="container">
        <?php if (!$is_logged): ?>
        <!-- Guest State -->
        <div class="guest-card">
            <div style="font-size:4rem;margin-bottom:16px">🔐</div>
            <h2>Sign In to View Your Dashboard</h2>
            <p>Access your orders, wishlist, and account settings by signing in to your GadgetZone account.</p>
            <div class="auth-buttons">
                <a href="<?= SITE_URL ?>/login.php" class="btn-login">Sign In</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn-register">Create Account</a>
            </div>
            <p style="margin-top:20px;font-size:.85rem;color:#aaa">New to GadgetZone? Join thousands of happy customers!</p>
        </div>
        <?php else: ?>
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <aside class="dash-sidebar">
                <div class="user-card">
                    <div class="user-avatar">👤</div>
                    <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                    <div class="user-email"><?= htmlspecialchars($user_email) ?></div>
                    <div class="user-since">Member since <?= isset($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'Today' ?></div>
                </div>
                <nav class="dash-nav">
                    <a href="<?= SITE_URL ?>/dashboard.php" class="active"><span class="dash-nav-icon">🏠</span> Dashboard</a>
                    <a href="<?= SITE_URL ?>/orders.php"><span class="dash-nav-icon">📦</span> My Orders</a>
                    <a href="<?= SITE_URL ?>/wishlist.php"><span class="dash-nav-icon">❤️</span> Wishlist</a>
                    <a href="<?= SITE_URL ?>/video.php"><span class="dash-nav-icon">🎥</span> Videos</a>
                    <a href="#"><span class="dash-nav-icon">🔔</span> Subscriptions</a>
                    <a href="<?= SITE_URL ?>/profile.php"><span class="dash-nav-icon">⚙️</span> Profile Settings</a>
                    <a href="<?= SITE_URL ?>/api/auth.php?action=logout" class="logout"><span class="dash-nav-icon">🚪</span> Sign Out</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="dash-main">
                <?php if (isset($_GET['welcome'])): ?>
                <div style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border-radius:14px;padding:20px 24px;font-weight:600">
                    🎉 Welcome to GadgetZone, <?= htmlspecialchars($user_name) ?>! Your account is ready.
                </div>
                <?php endif; ?>

                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h2>Welcome back, <?= htmlspecialchars(explode(' ',$user_name)[0]) ?>! 👋</h2>
                        <p>Here's what's happening with your account today.</p>
                    </div>
                    <div class="welcome-emoji">⚡</div>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card orders">
                        <div class="stat-icon">📦</div>
                        <div class="stat-value"><?= $order_count ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card wishlist">
                        <div class="stat-icon">❤️</div>
                        <div class="stat-value"><?= $wish_count ?></div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                    <div class="stat-card subs">
                        <div class="stat-icon">🔔</div>
                        <div class="stat-value"><?= $sub_count ?></div>
                        <div class="stat-label">Subscriptions</div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>📦 Recent Orders</h3>
                        <a href="<?= SITE_URL ?>/orders.php" class="view-all">View All →</a>
                    </div>
                    <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?= SITE_URL ?>/shop.php" style="display:inline-block;background:#ff6b35;color:#fff;padding:10px 24px;border-radius:8px;font-weight:600;margin-top:12px;font-size:.9rem">Start Shopping</a>
                    </div>
                    <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span class="order-num">#<?= str_pad($order['id'],6,'0',STR_PAD_LEFT) ?></span></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><?= formatPrice($order['total'] ?? $order['total_amount'] ?? 0) ?></td>
                            <td><span class="status-badge <?= $status_classes[$order['status']] ?? 'status-pending' ?>"><?= ucfirst($order['status']) ?></span></td>
                            <td><a href="<?= SITE_URL ?>/orders.php?id=<?= $order['id'] ?>"><button class="btn-view">View</button></a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
                    <a href="<?= SITE_URL ?>/shop.php" style="background:#fff;border-radius:12px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:transform .2s;color:inherit;display:block">
                        <div style="font-size:2rem;margin-bottom:8px">🛍️</div>
                        <div style="font-weight:600;color:#1e3a5f;font-size:.9rem">Browse Shop</div>
                    </a>
                    <a href="<?= SITE_URL ?>/wishlist.php" style="background:#fff;border-radius:12px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:transform .2s;color:inherit;display:block">
                        <div style="font-size:2rem;margin-bottom:8px">❤️</div>
                        <div style="font-weight:600;color:#1e3a5f;font-size:.9rem">My Wishlist</div>
                    </a>
                    <a href="<?= SITE_URL ?>/profile.php" style="background:#fff;border-radius:12px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:transform .2s;color:inherit;display:block">
                        <div style="font-size:2rem;margin-bottom:8px">⚙️</div>
                        <div style="font-weight:600;color:#1e3a5f;font-size:.9rem">Settings</div>
                    </a>
                </div>
            </main>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><h3>⚡ GadgetZone</h3><p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p><div class="social-links"><a href="#" class="social-link">Facebook</a><a href="#" class="social-link">Twitter</a><a href="#" class="social-link">Instagram</a></div></div>
            <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="<?= SITE_URL ?>/index.php">Home</a></li><li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li><li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li><li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li></ul></div>
            <div class="footer-col"><h4>Categories</h4><ul><li><a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=smartwatches">Smartwatches</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=tablets">Tablets</a></li></ul></div>
            <div class="footer-col"><h4>Newsletter</h4><p>Subscribe for deals!</p><form class="newsletter-form" onsubmit="event.preventDefault()"><input type="email" placeholder="Your email" required><button type="submit" class="btn-primary">Go</button></form><div class="trust-badges" style="margin-top:12px"><div class="trust-badge">🔒 Secure</div><div class="trust-badge">🚚 Fast</div></div></div>
        </div>
        <div class="footer-bottom"><p>© 2024 GadgetZone. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p></div>
    </div>
</footer>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item"><span class="bottom-nav-icon">🏠</span><span class="bottom-nav-label">Home</span></a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛍️</span><span class="bottom-nav-label">Shop</span></a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛒</span><span class="bottom-nav-label">Cart</span></a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item active"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
</body>
</html>
