<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$posts    = [];
$featured = null;
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 6;
$offset   = ($page - 1) * $per_page;

try {
    $db   = getDB();
    $fres = $db->query("SELECT * FROM blog_posts WHERE status='published' AND is_featured=1 ORDER BY created_at DESC LIMIT 1");
    $featured = $fres ? $fres->fetch_assoc() : null;
    $res  = $db->query("SELECT * FROM blog_posts WHERE status='published' ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
    $posts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) { $posts = []; }

if (empty($posts)) {
    $posts = [
        ['id'=>1,'title'=>'Top 10 Smartphones to Buy in 2024','category'=>'Smartphones','excerpt'=>'Looking to upgrade your phone this year? We\'ve rounded up the absolute best smartphones of 2024, from flagship killers to premium powerhouses.','author'=>'TechEditor','created_at'=>'2024-01-20','image'=>'','read_time'=>5,'views'=>3200,'is_featured'=>1],
        ['id'=>2,'title'=>'AirPods vs Samsung Buds vs Sony WF — Ultimate Comparison','category'=>'Accessories','excerpt'=>'Which wireless earbuds should you buy? We put the top three contenders through their paces in this comprehensive head-to-head comparison.','author'=>'AudioReview','created_at'=>'2024-01-18','image'=>'','read_time'=>7,'views'=>2100,'is_featured'=>0],
        ['id'=>3,'title'=>'How to Choose the Right Smartwatch for Your Lifestyle','category'=>'Smartwatches','excerpt'=>'With dozens of options on the market, choosing a smartwatch can be overwhelming. Here\'s your complete guide to finding the perfect wrist companion.','author'=>'GadgetGuru','created_at'=>'2024-01-15','image'=>'','read_time'=>6,'views'=>1800,'is_featured'=>0],
        ['id'=>4,'title'=>'5G in Nigeria: What It Means for Your Smartphone','category'=>'Technology','excerpt'=>'5G is finally rolling out in Nigeria. We explain what you need to know, which phones support it, and how it will change your mobile experience.','author'=>'TechEditor','created_at'=>'2024-01-12','image'=>'','read_time'=>4,'views'=>4500,'is_featured'=>0],
        ['id'=>5,'title'=>'Gaming on a Budget: Best Gadgets Under ₦100,000','category'=>'Gaming','excerpt'=>'You don\'t need to spend a fortune to have a great gaming setup. Check out our picks for the best gaming gadgets that won\'t break the bank.','author'=>'GameReview','created_at'=>'2024-01-10','image'=>'','read_time'=>5,'views'=>2800,'is_featured'=>0],
        ['id'=>6,'title'=>'Protect Your New Phone: Best Cases and Screen Protectors','category'=>'Accessories','excerpt'=>'Your smartphone is a significant investment. Here\'s everything you need to keep it safe from drops, scratches, and everyday wear and tear.','author'=>'GadgetGuru','created_at'=>'2024-01-08','image'=>'','read_time'=>3,'views'=>1600,'is_featured'=>0],
    ];
    $featured = $posts[0];
}

$cat_colors = ['Smartphones'=>'#3b82f6','Accessories'=>'#8b5cf6','Smartwatches'=>'#14b8a6','Technology'=>'#f59e0b','Gaming'=>'#ef4444','Default'=>'#6366f1'];
$card_bgs   = ['linear-gradient(135deg,#1e3a5f,#2d5f8f)','linear-gradient(135deg,#ff6b35,#ff8c5a)','linear-gradient(135deg,#8b5cf6,#a78bfa)','linear-gradient(135deg,#14b8a6,#2dd4bf)','linear-gradient(135deg,#f59e0b,#fbbf24)','linear-gradient(135deg,#ef4444,#f87171)'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Blog — GadgetZone</title>
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
        .blog-page{padding:40px 0}
        /* Featured */
        .featured-post{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);display:grid;grid-template-columns:1.4fr 1fr;margin-bottom:40px}
        .featured-img{height:320px;background:linear-gradient(135deg,#1e3a5f,#2d5f8f);position:relative;overflow:hidden}
        .featured-img img{width:100%;height:100%;object-fit:cover}
        .featured-overlay{position:absolute;inset:0;background:linear-gradient(to right, transparent, rgba(0,0,0,.3))}
        .featured-content{padding:32px}
        .featured-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;background:#ff6b35;color:#fff;margin-bottom:12px}
        .featured-title{font-size:1.4rem;font-weight:800;color:#1e3a5f;margin-bottom:12px;line-height:1.4}
        .featured-excerpt{color:#666;line-height:1.7;font-size:.95rem;margin-bottom:16px}
        .post-meta{display:flex;align-items:center;gap:12px;font-size:.82rem;color:#888;margin-bottom:16px;flex-wrap:wrap}
        .btn-read{display:inline-flex;align-items:center;gap:6px;background:#ff6b35;color:#fff;padding:10px 22px;border-radius:8px;font-weight:700;transition:background .3s}
        .btn-read:hover{background:#e55a25}
        /* Grid */
        .blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:40px}
        .blog-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:transform .3s}
        .blog-card:hover{transform:translateY(-4px)}
        .blog-img{height:180px;position:relative;overflow:hidden}
        .blog-img img{width:100%;height:100%;object-fit:cover}
        .blog-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem}
        .blog-cat-tag{position:absolute;top:10px;left:10px;padding:4px 10px;border-radius:20px;font-size:.72rem;font-weight:700;color:#fff}
        .blog-content{padding:18px}
        .blog-title{font-size:.95rem;font-weight:700;color:#333;margin-bottom:8px;line-height:1.4}
        .blog-excerpt{font-size:.85rem;color:#666;line-height:1.6;margin-bottom:12px}
        .blog-meta{display:flex;align-items:center;justify-content:space-between;font-size:.78rem;color:#888}
        .read-more{color:#ff6b35;font-weight:700;font-size:.85rem;display:inline-flex;align-items:center;gap:4px}
        .read-more:hover{text-decoration:underline}
        .pagination{display:flex;justify-content:center;gap:8px;margin-top:8px}
        .pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.9rem;font-weight:600}
        .pagination a{background:#fff;color:#1e3a5f;box-shadow:0 1px 4px rgba(0,0,0,.08);transition:all .2s}
        .pagination a:hover{background:#1e3a5f;color:#fff}
        .pagination .current{background:#1e3a5f;color:#fff}
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
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.blog-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.featured-post{grid-template-columns:1fr}.featured-img{height:200px}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.blog-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:1fr}}
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
        <a href="<?= SITE_URL ?>/shop.php?category=smartphones">📱 Smartphones</a>
        <a href="<?= SITE_URL ?>/video.php">🎥 Videos</a><a href="<?= SITE_URL ?>/blog.php">📝 Blog</a>
        <a href="<?= SITE_URL ?>/contact.php">📞 Contact</a><a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>📝 Tech Blog</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Blog</div>
    </div>
</div>

<div class="blog-page">
    <div class="container">
        <!-- Featured Post -->
        <?php if ($featured): ?>
        <div class="featured-post">
            <div class="featured-img" style="<?= empty($featured['image'])?'background:linear-gradient(135deg,#1e3a5f,#2d5f8f)':'' ?>">
                <?php if (!empty($featured['image'])): ?>
                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($featured['image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
                    <div class="featured-overlay"></div>
                <?php else: ?>
                    <span style="font-size:5rem;position:absolute">📱</span>
                <?php endif; ?>
            </div>
            <div class="featured-content">
                <span class="featured-badge">⭐ Featured Post</span>
                <h2 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h2>
                <div class="post-meta">
                    <span>✍️ <?= htmlspecialchars($featured['author'] ?? 'Editor') ?></span>
                    <span>📅 <?= date('M j, Y', strtotime($featured['created_at'])) ?></span>
                    <span>⏱ <?= $featured['read_time'] ?? 5 ?> min read</span>
                    <span>👁 <?= number_format($featured['views'] ?? 0) ?> views</span>
                </div>
                <p class="featured-excerpt"><?= htmlspecialchars($featured['excerpt'] ?? '') ?></p>
                <a href="<?= SITE_URL ?>/blog.php?post=<?= (int)$featured['id'] ?>" class="btn-read">Read Article →</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Blog Grid -->
        <h2 style="font-size:1.4rem;font-weight:700;color:#1e3a5f;margin-bottom:20px">Latest Articles</h2>
        <div class="blog-grid">
            <?php foreach ($posts as $i => $post):
                $bg  = $card_bgs[$i % count($card_bgs)];
                $cclr = $cat_colors[$post['category']??''] ?? $cat_colors['Default'];
            ?>
            <article class="blog-card">
                <div class="blog-img" style="<?= empty($post['image'])?'background:'.$bg:'' ?>">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php else: ?>
                        <div class="blog-img-placeholder">📰</div>
                    <?php endif; ?>
                    <span class="blog-cat-tag" style="background:<?= $cclr ?>"><?= htmlspecialchars($post['category'] ?? 'Tech') ?></span>
                </div>
                <div class="blog-content">
                    <h3 class="blog-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="blog-excerpt"><?= htmlspecialchars(mb_substr($post['excerpt'] ?? '', 0, 110)) ?>...</p>
                    <div class="blog-meta">
                        <div>
                            <span>✍️ <?= htmlspecialchars($post['author'] ?? 'Editor') ?></span>
                            <span style="margin-left:8px">📅 <?= date('M j', strtotime($post['created_at'])) ?></span>
                        </div>
                        <span style="color:#888">⏱ <?= $post['read_time'] ?? 5 ?>m</span>
                    </div>
                    <div style="margin-top:10px">
                        <a href="<?= SITE_URL ?>/blog.php?post=<?= (int)$post['id'] ?>" class="read-more">Read More →</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>">← Prev</a><?php endif; ?>
            <span class="current"><?= $page ?></span>
            <a href="?page=<?= $page+1 ?>">Next →</a>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><h3>⚡ GadgetZone</h3><p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p><div class="social-links"><a href="#" class="social-link">Facebook</a><a href="#" class="social-link">Twitter</a><a href="#" class="social-link">Instagram</a><a href="#" class="social-link">YouTube</a></div></div>
            <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="<?= SITE_URL ?>/index.php">Home</a></li><li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li><li><a href="<?= SITE_URL ?>/video.php">Videos</a></li><li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li><li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li></ul></div>
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
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
</body>
</html>
