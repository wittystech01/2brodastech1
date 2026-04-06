<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php?redirect=' . urlencode('/orders.php'));
}

$user_id = (int)$_SESSION['user_id'];
$orders  = [];
$page    = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;
$total    = 0;

try {
    $db  = getDB();
    $res = $db->query("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) as item_count FROM orders o WHERE o.user_id=$user_id ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset");
    $orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $cnt    = $db->query("SELECT COUNT(*) c FROM orders WHERE user_id=$user_id");
    $total  = $cnt ? (int)$cnt->fetch_assoc()['c'] : 0;
} catch (Exception $e) { $orders = []; }

$total_pages = max(1, ceil($total / $per_page));
$status_cls  = [
    'pending'   => ['class'=>'status-pending',  'label'=>'Pending',   'color'=>'#d97706'],
    'confirmed' => ['class'=>'status-confirmed', 'label'=>'Confirmed', 'color'=>'#1d4ed8'],
    'processing'=> ['class'=>'status-confirmed', 'label'=>'Processing','color'=>'#1d4ed8'],
    'shipped'   => ['class'=>'status-shipped',   'label'=>'Shipped',   'color'=>'#4f46e5'],
    'delivered' => ['class'=>'status-delivered', 'label'=>'Delivered', 'color'=>'#15803d'],
    'cancelled' => ['class'=>'status-cancelled', 'label'=>'Cancelled', 'color'=>'#dc2626'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — GadgetZone</title>
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
        .orders-page{padding:40px 0}
        .orders-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
        .orders-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:2px solid #f0f0f0}
        .orders-header h2{font-size:1.1rem;font-weight:700;color:#1e3a5f}
        .orders-table{width:100%;border-collapse:collapse}
        .orders-table th{padding:14px 20px;text-align:left;font-size:.82rem;font-weight:700;color:#888;background:#f8f9fa;text-transform:uppercase;letter-spacing:.5px}
        .orders-table td{padding:16px 20px;border-bottom:1px solid #f8f9fa;font-size:.9rem;vertical-align:middle}
        .orders-table tr:hover td{background:#fafafa}
        .orders-table tr:last-child td{border-bottom:none}
        .order-num{font-weight:700;color:#1e3a5f;font-size:.95rem}
        .order-date{color:#888;font-size:.85rem}
        .order-items-count{color:#555}
        .order-total{font-weight:700;color:#333}
        .status-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:.78rem;font-weight:700}
        .status-pending{background:#fef3c7;color:#d97706}
        .status-confirmed,.status-processing{background:#dbeafe;color:#1d4ed8}
        .status-shipped{background:#e0e7ff;color:#4f46e5}
        .status-delivered{background:#dcfce7;color:#15803d}
        .status-cancelled{background:#fee2e2;color:#dc2626}
        .btn-view-order{background:#1e3a5f;color:#fff;border:none;padding:7px 16px;border-radius:7px;font-size:.82rem;cursor:pointer;font-weight:600;transition:background .2s}
        .btn-view-order:hover{background:#0f2840}
        .empty-state{text-align:center;padding:80px 20px}
        .empty-icon{font-size:5rem;margin-bottom:16px}
        .empty-state h2{font-size:1.5rem;color:#1e3a5f;margin-bottom:8px}
        .empty-state p{color:#888;margin-bottom:24px}
        .pagination{display:flex;justify-content:center;gap:8px;padding:20px}
        .pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.9rem;font-weight:600}
        .pagination a{background:#f0f4ff;color:#1e3a5f;transition:all .2s}
        .pagination a:hover{background:#1e3a5f;color:#fff}
        .pagination .current{background:#1e3a5f;color:#fff}
        .order-success-banner{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border-radius:12px;padding:16px 20px;margin-bottom:24px;font-weight:600;display:flex;align-items:center;gap:12px}
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
        @media(max-width:768px){.orders-table .hide-sm{display:none}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.footer-grid{grid-template-columns:1fr}}
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
        <a href="<?= SITE_URL ?>/index.php">🏠 Home</a><a href="<?= SITE_URL ?>/shop.php">🛍️ Shop</a>
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a><a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>📦 My Orders</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a> / Orders</div>
    </div>
</div>

<div class="orders-page">
    <div class="container">
        <?php if (isset($_GET['order_success'])): ?>
        <div class="order-success-banner">
            🎉 Your order has been placed successfully! We'll send you a confirmation email shortly.
        </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="orders-card">
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h2>No orders yet</h2>
                <p>You haven't placed any orders. Start shopping now!</p>
                <a href="<?= SITE_URL ?>/shop.php" style="display:inline-block;background:#ff6b35;color:#fff;padding:14px 32px;border-radius:10px;font-weight:700">🛍️ Browse Products</a>
            </div>
        </div>
        <?php else: ?>
        <div class="orders-card">
            <div class="orders-header">
                <h2>All Orders (<?= $total ?>)</h2>
                <a href="<?= SITE_URL ?>/shop.php" style="background:#ff6b35;color:#fff;padding:8px 20px;border-radius:8px;font-weight:600;font-size:.85rem">+ New Order</a>
            </div>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th class="hide-sm">Date</th>
                        <th class="hide-sm">Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order):
                    $status = strtolower($order['status'] ?? 'pending');
                    $sc     = $status_cls[$status] ?? $status_cls['pending'];
                ?>
                <tr>
                    <td><span class="order-num">#<?= str_pad($order['id'],6,'0',STR_PAD_LEFT) ?></span></td>
                    <td class="hide-sm"><span class="order-date"><?= date('M j, Y', strtotime($order['created_at'])) ?></span></td>
                    <td class="hide-sm"><span class="order-items-count"><?= $order['item_count'] ?? '—' ?> item<?= ($order['item_count']??0)!==1?'s':'' ?></span></td>
                    <td><span class="order-total"><?= formatPrice($order['total'] ?? $order['total_amount'] ?? 0) ?></span></td>
                    <td><span class="status-badge <?= $sc['class'] ?>"><?= $sc['label'] ?></span></td>
                    <td><a href="<?= SITE_URL ?>/orders.php?id=<?= (int)$order['id'] ?>"><button class="btn-view-order">View →</button></a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>">← Prev</a><?php endif; ?>
                <?php for($i=1;$i<=$total_pages;$i++): ?>
                    <?php if($i===$page): ?><span class="current"><?= $i ?></span>
                    <?php else: ?><a href="?page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
                <?php endfor; ?>
                <?php if ($page<$total_pages): ?><a href="?page=<?= $page+1 ?>">Next →</a><?php endif; ?>
            </div>
            <?php endif; ?>
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
