<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$filter   = isset($_GET['filter']) ? sanitize($_GET['filter']) : 'all';
$videos   = [];
$featured = null;
try {
    $videos   = getVideos(12, 0);
    $db       = getDB();
    $fres     = $db->query("SELECT v.*,c.name as channel_name FROM videos v LEFT JOIN channels c ON v.channel_id=c.id WHERE v.is_featured=1 AND v.status='active' LIMIT 1");
    $featured = $fres ? $fres->fetch_assoc() : null;
} catch (Exception $e) { $videos = []; }

if (empty($videos)) {
    $videos = [
        ['id'=>1,'title'=>'iPhone 15 Pro Max Full Review 2024','channel_name'=>'GadgetZone Reviews','views'=>245000,'duration'=>'12:45','category'=>'Reviews','is_featured'=>1,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>2,'title'=>'Samsung Galaxy S24 Ultra Unboxing','channel_name'=>'TechUnbox','views'=>189000,'duration'=>'8:32','category'=>'Unboxings','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>3,'title'=>'Best Wireless Earbuds Under ₦50,000','channel_name'=>'GadgetZone Reviews','views'=>127000,'duration'=>'15:20','category'=>'Reviews','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>4,'title'=>'iPad Pro 2024 vs Samsung Tab S9 Ultra','channel_name'=>'TechVersus','views'=>98000,'duration'=>'18:05','category'=>'Reviews','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>5,'title'=>'Apple Watch Ultra 2 Unboxing','channel_name'=>'GadgetZone Reviews','views'=>76000,'duration'=>'6:50','category'=>'Unboxings','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>6,'title'=>'GadgetZone New Arrivals — January 2024','channel_name'=>'GadgetZone Official','views'=>55000,'duration'=>'3:20','category'=>'Ads','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>7,'title'=>'Google Pixel 8 Pro Camera Test','channel_name'=>'CameraLab','views'=>143000,'duration'=>'22:15','category'=>'Reviews','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>8,'title'=>'OnePlus 12 Pro Unboxing & Setup','channel_name'=>'TechUnbox','views'=>61000,'duration'=>'10:48','category'=>'Unboxings','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
        ['id'=>9,'title'=>'Top 5 Gaming Gadgets 2024','channel_name'=>'GadgetZone Reviews','views'=>205000,'duration'=>'14:30','category'=>'Reviews','is_featured'=>0,'thumbnail_url'=>'','embed_url'=>''],
    ];
    $featured = $videos[0];
}

$filtered = ($filter === 'all') ? $videos : array_values(array_filter($videos, fn($v) => strtolower($v['category']) === strtolower($filter)));
$thumb_colors = ['#1e3a5f','#2d5f8f','#ff6b35','#8b5cf6','#0ea5e9','#14b8a6','#f59e0b','#ef4444','#6366f1'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Gallery — GadgetZone</title>
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
        .video-page{padding:40px 0}
        /* Featured */
        .featured-section{margin-bottom:40px}
        .featured-section h2{font-size:1.3rem;font-weight:700;color:#1e3a5f;margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .featured-video{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);display:grid;grid-template-columns:1.5fr 1fr}
        .featured-thumb{height:300px;background:linear-gradient(135deg,#1e3a5f,#2d5f8f);position:relative;display:flex;align-items:center;justify-content:center;cursor:pointer}
        .play-overlay{width:80px;height:80px;background:rgba(255,107,53,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff;transition:transform .2s}
        .featured-thumb:hover .play-overlay{transform:scale(1.1)}
        .featured-duration{position:absolute;bottom:12px;right:12px;background:rgba(0,0,0,.8);color:#fff;padding:3px 10px;border-radius:5px;font-size:.8rem}
        .featured-badge{position:absolute;top:12px;left:12px;background:#ff6b35;color:#fff;padding:4px 10px;border-radius:6px;font-size:.75rem;font-weight:700}
        .featured-info{padding:28px}
        .featured-category{font-size:.75rem;color:#ff6b35;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px}
        .featured-title{font-size:1.2rem;font-weight:700;color:#1e3a5f;margin-bottom:12px;line-height:1.4}
        .featured-channel{font-size:.9rem;color:#666;margin-bottom:8px;display:flex;align-items:center;gap:6px}
        .featured-views{font-size:.85rem;color:#888}
        .featured-desc{margin-top:12px;font-size:.9rem;color:#666;line-height:1.6}
        .btn-watch{display:inline-flex;align-items:center;gap:8px;background:#ff6b35;color:#fff;padding:12px 24px;border-radius:8px;font-weight:700;margin-top:16px;transition:background .3s}
        .btn-watch:hover{background:#e55a25}
        /* Filter */
        .filter-tabs{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
        .filter-tab{padding:8px 20px;border-radius:20px;font-size:.9rem;font-weight:600;cursor:pointer;border:2px solid #eee;background:#fff;color:#555;transition:all .2s}
        .filter-tab:hover,.filter-tab.active{background:#1e3a5f;color:#fff;border-color:#1e3a5f}
        /* Grid */
        .videos-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .video-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.07);cursor:pointer;transition:transform .3s}
        .video-card:hover{transform:translateY(-4px)}
        .video-thumb{height:180px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
        .video-thumb img{width:100%;height:100%;object-fit:cover}
        .play-btn{width:52px;height:52px;background:rgba(255,107,53,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;transition:transform .2s;z-index:1}
        .video-card:hover .play-btn{transform:scale(1.1)}
        .video-duration{position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.75);color:#fff;padding:2px 8px;border-radius:4px;font-size:.72rem}
        .video-cat-badge{position:absolute;top:8px;left:8px;padding:3px 8px;border-radius:5px;font-size:.7rem;font-weight:700;color:#fff;background:rgba(30,58,95,.8)}
        .video-info{padding:14px}
        .video-title{font-weight:600;font-size:.9rem;color:#333;margin-bottom:6px;line-height:1.4}
        .video-meta{display:flex;justify-content:space-between;font-size:.78rem;color:#888}
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
        /* Modal */
        .video-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center}
        .video-modal.open{display:flex}
        .modal-inner{background:#000;border-radius:12px;overflow:hidden;width:90%;max-width:800px;position:relative}
        .modal-close{position:absolute;top:10px;right:14px;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer;z-index:1}
        .modal-inner iframe{width:100%;height:450px;display:block;border:none}
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.videos-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.featured-video{grid-template-columns:1fr}.featured-thumb{height:220px}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.videos-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:1fr}}
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
        <h1>🎥 Video Gallery</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Videos</div>
    </div>
</div>

<div class="video-page">
    <div class="container">
        <!-- Featured Video -->
        <?php if ($featured): ?>
        <div class="featured-section">
            <h2>⭐ Featured Video</h2>
            <div class="featured-video">
                <div class="featured-thumb" onclick="playVideo(<?= (int)$featured['id'] ?>, '<?= htmlspecialchars(addslashes($featured['embed_url']??'')) ?>')">
                    <?php if (!empty($featured['thumbnail_url'])): ?>
                        <img src="<?= htmlspecialchars($featured['thumbnail_url']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php endif; ?>
                    <div class="play-overlay">▶</div>
                    <span class="featured-duration"><?= htmlspecialchars($featured['duration'] ?? '00:00') ?></span>
                    <span class="featured-badge">⭐ Featured</span>
                </div>
                <div class="featured-info">
                    <div class="featured-category"><?= htmlspecialchars($featured['category'] ?? 'Reviews') ?></div>
                    <div class="featured-title"><?= htmlspecialchars($featured['title']) ?></div>
                    <div class="featured-channel">📺 <?= htmlspecialchars($featured['channel_name'] ?? '') ?></div>
                    <div class="featured-views">👁 <?= number_format($featured['views'] ?? 0) ?> views</div>
                    <a onclick="playVideo(<?= (int)$featured['id'] ?>, '<?= htmlspecialchars(addslashes($featured['embed_url']??'')) ?>')" class="btn-watch" href="#">▶ Watch Now</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <?php foreach(['all'=>'All','Reviews'=>'Reviews','Unboxings'=>'Unboxings','Ads'=>'Ads'] as $key=>$label): ?>
            <a href="?filter=<?= $key ?>" class="filter-tab <?= $filter===$key?'active':'' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Videos Grid -->
        <?php if (empty($filtered)): ?>
        <div style="text-align:center;padding:60px;background:#fff;border-radius:12px">
            <div style="font-size:4rem;margin-bottom:16px">🎬</div>
            <h3 style="color:#1e3a5f;margin-bottom:8px">No videos found</h3>
            <p style="color:#888">Try a different category filter.</p>
        </div>
        <?php else: ?>
        <div class="videos-grid">
            <?php foreach ($filtered as $i => $vid):
                $bg = $thumb_colors[$i % count($thumb_colors)];
            ?>
            <div class="video-card" onclick="playVideo(<?= (int)$vid['id'] ?>, '<?= htmlspecialchars(addslashes($vid['embed_url']??'')) ?>')">
                <div class="video-thumb" style="background:<?= $bg ?>">
                    <?php if (!empty($vid['thumbnail_url'])): ?>
                        <img src="<?= htmlspecialchars($vid['thumbnail_url']) ?>" alt="<?= htmlspecialchars($vid['title']) ?>">
                    <?php endif; ?>
                    <div class="play-btn">▶</div>
                    <span class="video-duration"><?= htmlspecialchars($vid['duration'] ?? '0:00') ?></span>
                    <span class="video-cat-badge"><?= htmlspecialchars($vid['category'] ?? '') ?></span>
                </div>
                <div class="video-info">
                    <div class="video-title"><?= htmlspecialchars($vid['title']) ?></div>
                    <div class="video-meta">
                        <span>📺 <?= htmlspecialchars($vid['channel_name'] ?? '') ?></span>
                        <span>👁 <?= number_format($vid['views'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Video Modal -->
<div class="video-modal" id="videoModal" onclick="closeModal(event)">
    <div class="modal-inner">
        <button class="modal-close" onclick="closeVideoModal()">✕</button>
        <iframe id="videoFrame" src="" allowfullscreen allow="autoplay"></iframe>
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
<script>
function playVideo(id, url) {
    const modal = document.getElementById('videoModal');
    const frame = document.getElementById('videoFrame');
    if (url) {
        frame.src = url + '?autoplay=1';
    } else {
        frame.src = 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1';
    }
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    document.getElementById('videoFrame').src = '';
    modal.classList.remove('open');
    document.body.style.overflow = '';
}
function closeModal(e) {
    if (e.target === document.getElementById('videoModal')) closeVideoModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeVideoModal(); });
</script>
</body>
</html>
