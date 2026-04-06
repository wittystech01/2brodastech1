<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$trending_products = [];
$db_ok = false;
try {
    $trending_products = getTrendingProducts(8);
    $db_ok = true;
} catch (Exception $e) {
    $trending_products = [];
}

if (empty($trending_products)) {
    $trending_products = [
        ['id'=>1,'name'=>'iPhone 15 Pro Max','category_name'=>'Smartphones','price'=>850000,'sale_price'=>null,'original_price'=>950000,'image'=>'','rating'=>4.8,'reviews_count'=>124,'featured'=>1],
        ['id'=>2,'name'=>'Samsung Galaxy S24 Ultra','category_name'=>'Smartphones','price'=>750000,'sale_price'=>null,'original_price'=>820000,'image'=>'','rating'=>4.7,'reviews_count'=>98,'featured'=>1],
        ['id'=>3,'name'=>'AirPods Pro 2nd Gen','category_name'=>'Accessories','price'=>180000,'sale_price'=>null,'original_price'=>210000,'image'=>'','rating'=>4.9,'reviews_count'=>256,'featured'=>1],
        ['id'=>4,'name'=>'Apple Watch Series 9','category_name'=>'Smartwatches','price'=>320000,'sale_price'=>null,'original_price'=>370000,'image'=>'','rating'=>4.6,'reviews_count'=>87,'featured'=>1],
        ['id'=>5,'name'=>'iPad Pro 12.9"','category_name'=>'Tablets','price'=>680000,'sale_price'=>null,'original_price'=>750000,'image'=>'','rating'=>4.8,'reviews_count'=>143,'featured'=>1],
        ['id'=>6,'name'=>'PlayStation 5 Controller','category_name'=>'Gaming','price'=>45000,'sale_price'=>null,'original_price'=>65000,'image'=>'','rating'=>4.7,'reviews_count'=>210,'featured'=>1],
        ['id'=>7,'name'=>'Google Pixel 8 Pro','category_name'=>'Smartphones','price'=>620000,'sale_price'=>null,'original_price'=>700000,'image'=>'','rating'=>4.5,'reviews_count'=>67,'featured'=>1],
        ['id'=>8,'name'=>'Sony WH-1000XM5','category_name'=>'Accessories','price'=>195000,'sale_price'=>null,'original_price'=>300000,'image'=>'','rating'=>4.9,'reviews_count'=>312,'featured'=>1],
    ];
}

$emoji_map = ['Smartphones'=>'📱','Accessories'=>'🎧','Smartwatches'=>'⌚','Gaming'=>'🎮','Tablets'=>'📟','Default'=>'🛒'];
function getCategoryEmoji($cat, $map) { return $map[$cat] ?? $map['Default']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GadgetZone - Your one-stop shop for the latest smartphones, gadgets, and accessories.">
    <title>GadgetZone - Next-Gen Gadgets &amp; Smartphones</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/mobile.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;color:#333;background:#fff}
        a{text-decoration:none;color:inherit}
        .container{max-width:1200px;margin:0 auto;padding:0 20px}
        /* Hero */
        .hero{background:linear-gradient(135deg,#1e3a5f 0%,#2d5f8f 60%,#1a3a5c 100%);color:#fff;padding:80px 0 60px;position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;top:-30%;right:-5%;width:500px;height:500px;background:rgba(255,107,53,.08);border-radius:50%;pointer-events:none}
        .hero-content{display:flex;align-items:center;justify-content:space-between;gap:40px}
        .hero-text{flex:1}
        .hero-text h1{font-size:3rem;font-weight:800;margin-bottom:16px;line-height:1.2}
        .hero-text p{font-size:1.2rem;color:rgba(255,255,255,.85);margin-bottom:32px}
        .hero-buttons{display:flex;gap:16px;flex-wrap:wrap}
        .btn-cta{background:#ff6b35;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;font-size:1rem;transition:background .3s;display:inline-block}
        .btn-cta:hover{background:#e55a25}
        .btn-outline{border:2px solid #fff;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;font-size:1rem;transition:all .3s;display:inline-block}
        .btn-outline:hover{background:#fff;color:#1e3a5f}
        .hero-image{font-size:150px;text-align:center;flex-shrink:0;animation:float 3s ease-in-out infinite}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
        /* Promo */
        .promo-banners{background:#f8f9fa;padding:24px 0}
        .promo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .promo-card{background:#fff;border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:16px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        .promo-icon{font-size:36px}
        .promo-card h3{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:2px}
        .promo-card p{font-size:.85rem;color:#666}
        /* Sections */
        .section{padding:60px 0}
        .section-alt{background:#f8f9fa}
        .section-title{text-align:center;font-size:2rem;font-weight:700;color:#1e3a5f;margin-bottom:8px}
        .section-subtitle{text-align:center;color:#666;margin-bottom:40px}
        /* Categories */
        .categories-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:20px}
        .category-card{text-align:center;cursor:pointer;color:inherit}
        .category-circle{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#e8f0fe,#d0e4ff);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:40px;transition:all .3s;box-shadow:0 4px 15px rgba(30,58,95,.1)}
        .category-card:hover .category-circle{background:linear-gradient(135deg,#1e3a5f,#2d5f8f);transform:scale(1.1);box-shadow:0 8px 25px rgba(30,58,95,.3)}
        .category-card span{font-weight:600;color:#333}
        /* Products */
        .products-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px}
        .product-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);transition:transform .3s,box-shadow .3s}
        .product-card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,.15)}
        .product-image{height:200px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:80px;position:relative}
        .product-badge{position:absolute;top:10px;left:10px;background:#ff6b35;color:#fff;font-size:.7rem;font-weight:700;padding:4px 8px;border-radius:6px}
        .product-info{padding:16px}
        .product-category{font-size:.75rem;color:#ff6b35;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
        .product-title{font-size:.95rem;font-weight:600;color:#333;margin:6px 0;line-height:1.4}
        .product-stars{color:#ffc107;font-size:.85rem;margin-bottom:8px}
        .product-price{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px}
        .price-current{font-size:1.1rem;font-weight:700;color:#1e3a5f}
        .price-original{font-size:.85rem;color:#999;text-decoration:line-through}
        .btn-add-cart{background:#ff6b35;color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;width:100%;transition:background .3s;font-size:.9rem}
        .btn-add-cart:hover{background:#e55a25}
        /* Flash Sale */
        .flash-sale{background:linear-gradient(135deg,#1e3a5f,#2d5f8f);padding:60px 0;color:#fff}
        .flash-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:30px;flex-wrap:wrap;gap:16px}
        .flash-header h2{font-size:2rem;font-weight:800}
        .countdown{display:flex;gap:12px}
        .countdown-item{background:rgba(255,255,255,.15);border-radius:10px;padding:12px 16px;text-align:center;min-width:70px}
        .countdown-item .num{font-size:2rem;font-weight:800;display:block}
        .countdown-item .lbl{font-size:.7rem;text-transform:uppercase;opacity:.8}
        .flash-card{background:rgba(255,255,255,.1);border-radius:16px;overflow:hidden;transition:transform .3s}
        .flash-card:hover{transform:translateY(-5px)}
        .flash-card .product-image{background:rgba(255,255,255,.05)}
        .flash-card .product-category{color:#ff6b35}
        .flash-card .product-title{color:#fff}
        .flash-card .price-current{color:#ff6b35}
        /* Videos */
        .videos-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .video-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);cursor:pointer;transition:transform .3s}
        .video-card:hover{transform:translateY(-5px)}
        .video-thumbnail{height:180px;background:linear-gradient(135deg,#1e3a5f,#2d5f8f);position:relative;display:flex;align-items:center;justify-content:center}
        .play-btn{width:60px;height:60px;background:rgba(255,107,53,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff}
        .video-duration{position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.7);color:#fff;padding:2px 8px;border-radius:4px;font-size:.75rem}
        .video-info{padding:14px}
        .video-title{font-weight:600;font-size:.9rem;color:#333;margin-bottom:6px}
        .video-meta{font-size:.8rem;color:#888}
        /* Testimonials */
        .testimonials{background:#f8f9fa}
        .testimonials-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .testimonial-card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
        .testimonial-stars{color:#ffc107;margin-bottom:12px;font-size:1.1rem}
        .testimonial-text{color:#555;font-style:italic;margin-bottom:16px;line-height:1.6}
        .testimonial-author{display:flex;align-items:center;gap:12px}
        .author-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#ff6b35);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0}
        .author-name{font-weight:600;color:#333;font-size:.9rem}
        .author-title{font-size:.8rem;color:#888}
        /* Newsletter */
        .newsletter-section{background:#ff6b35;padding:60px 0;color:#fff;text-align:center}
        .newsletter-section h2{font-size:2rem;font-weight:700;margin-bottom:8px}
        .newsletter-section p{margin-bottom:24px;opacity:.9}
        .newsletter-input-group{display:flex;max-width:480px;margin:0 auto;gap:8px}
        .newsletter-input-group input{flex:1;padding:14px 18px;border-radius:8px;border:none;font-size:1rem}
        .newsletter-input-group button{background:#1e3a5f;color:#fff;padding:14px 24px;border:none;border-radius:8px;font-weight:700;cursor:pointer;white-space:nowrap}
        .newsletter-input-group button:hover{background:#0f2840}
        /* Trust */
        .trust-badges-section{padding:40px 0}
        .trust-badges-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
        .trust-badge-card{text-align:center;padding:24px;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
        .trust-badge-icon{font-size:40px;margin-bottom:12px}
        .trust-badge-card h4{font-size:1rem;color:#1e3a5f;font-weight:700;margin-bottom:6px}
        .trust-badge-card p{font-size:.85rem;color:#666}
        /* App */
        .app-section{background:linear-gradient(135deg,#1e3a5f,#0f2840);padding:60px 0;color:#fff}
        .app-content{display:flex;align-items:center;justify-content:space-between;gap:40px}
        .app-text h2{font-size:2rem;font-weight:700;margin-bottom:12px}
        .app-text p{opacity:.85;margin-bottom:24px;line-height:1.6}
        .app-buttons{display:flex;gap:16px;flex-wrap:wrap}
        .app-btn{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:12px 20px;border-radius:10px;color:#fff;transition:background .3s}
        .app-btn:hover{background:rgba(255,255,255,.2)}
        .app-btn-sub{font-size:.7rem;opacity:.7}
        .app-btn-main{font-weight:700}
        .app-image{font-size:120px}
        /* Footer */
        .footer{background:#1e3a5f;color:#fff;padding:60px 0 20px}
        .footer-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:40px;margin-bottom:40px}
        .footer h3,.footer h4{margin-bottom:16px}
        .footer p{opacity:.8;line-height:1.6;font-size:.9rem}
        .footer ul{list-style:none}
        .footer ul li{margin-bottom:8px}
        .footer ul a{opacity:.8;font-size:.9rem;transition:opacity .3s}
        .footer ul a:hover{opacity:1;color:#ff6b35}
        .social-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .social-link{background:rgba(255,255,255,.1);padding:6px 14px;border-radius:20px;font-size:.8rem;transition:background .3s}
        .social-link:hover{background:#ff6b35}
        .newsletter-form{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
        .newsletter-form input{flex:1;padding:10px 14px;border-radius:6px;border:none;font-size:.9rem;min-width:140px}
        .newsletter-form .btn-primary{background:#ff6b35;color:#fff;padding:10px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer;white-space:nowrap}
        .trust-badges{display:flex;flex-wrap:wrap;gap:8px}
        .trust-badge{background:rgba(255,255,255,.1);padding:4px 10px;border-radius:12px;font-size:.75rem}
        .footer-bottom{border-top:1px solid rgba(255,255,255,.1);padding-top:20px;text-align:center;font-size:.85rem;opacity:.7}
        .footer-bottom a{opacity:.8;margin:0 4px}
        .footer-bottom a:hover{color:#ff6b35;opacity:1}
        /* Bottom Nav */
        .bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0}
        .bottom-nav{display:flex;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem;text-decoration:none;transition:color .2s}
        .bottom-nav-item:hover,.bottom-nav-item.active{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
        /* Navbar */
        .navbar{background:#1e3a5f;padding:0;position:sticky;top:0;z-index:999;box-shadow:0 2px 10px rgba(0,0,0,.15)}
        .navbar .container{display:flex;align-items:center;gap:20px;height:64px}
        .nav-brand .logo{display:flex;align-items:center;gap:8px;color:#fff;font-size:1.4rem;font-weight:800}
        .logo-icon{font-size:1.5rem}
        .nav-search{flex:1;max-width:400px}
        .search-form{display:flex;background:rgba(255,255,255,.12);border-radius:8px;overflow:hidden}
        .search-input{flex:1;background:transparent;border:none;padding:10px 14px;color:#fff;font-size:.9rem;outline:none}
        .search-input::placeholder{color:rgba(255,255,255,.6)}
        .search-btn{background:transparent;border:none;color:#fff;padding:0 14px;cursor:pointer;font-size:1rem}
        .nav-links{display:flex;gap:4px}
        .nav-links a{color:rgba(255,255,255,.85);padding:8px 12px;border-radius:6px;font-size:.9rem;transition:all .2s}
        .nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
        .nav-icons{display:flex;align-items:center;gap:4px}
        .nav-icon{color:rgba(255,255,255,.85);padding:8px 10px;border-radius:6px;font-size:1rem;position:relative;transition:all .2s}
        .nav-icon:hover{background:rgba(255,255,255,.1);color:#fff}
        .cart-badge{background:#ff6b35;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;position:absolute;top:4px;right:4px}
        .hamburger{display:none;background:none;border:none;cursor:pointer;padding:4px}
        .hamburger span{display:block;width:24px;height:2px;background:#fff;margin:5px 0;transition:all .3s}
        .mobile-menu{display:none;position:fixed;top:0;right:-100%;width:280px;height:100vh;background:#fff;z-index:1001;transition:right .3s;box-shadow:-5px 0 20px rgba(0,0,0,.2);overflow-y:auto}
        .mobile-menu.open{right:0}
        .mobile-menu-header{background:#1e3a5f;color:#fff;padding:20px;display:flex;justify-content:space-between;align-items:center}
        .mobile-menu-header .logo-text{font-size:1.3rem;font-weight:800}
        .close-menu{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer}
        .mobile-nav-links{padding:16px 0}
        .mobile-nav-links a{display:block;padding:14px 20px;color:#333;border-bottom:1px solid #f0f0f0;font-weight:500;transition:background .2s}
        .mobile-nav-links a:hover{background:#f8f9fa;color:#1e3a5f}
        .mobile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000}
        .mobile-overlay.open{display:block}
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}}
        @media(max-width:768px){
            .hero-text h1{font-size:2rem}
            .hero-image{font-size:80px}
            .promo-grid{grid-template-columns:1fr}
            .categories-grid{grid-template-columns:repeat(3,1fr)}
            .products-grid{grid-template-columns:repeat(2,1fr)}
            .videos-grid{grid-template-columns:1fr}
            .testimonials-grid{grid-template-columns:1fr}
            .trust-badges-grid{grid-template-columns:repeat(2,1fr)}
            .app-content{flex-direction:column;text-align:center}
            .app-image{font-size:80px}
            .flash-header{flex-direction:column}
            .footer-grid{grid-template-columns:repeat(2,1fr)}
            .bottom-nav{padding-bottom:env(safe-area-inset-bottom)}
        }
        @media(max-width:480px){
            .categories-grid{grid-template-columns:repeat(2,1fr)}
            .products-grid{grid-template-columns:1fr}
            .hero-content{flex-direction:column;text-align:center}
            .hero-image{display:none}
            .footer-grid{grid-template-columns:1fr}
            .trust-badges-grid{grid-template-columns:1fr}
            .nav-search{display:none}
        }
        .view-all-btn{display:inline-block;background:#1e3a5f;color:#fff;padding:14px 40px;border-radius:8px;font-weight:700;font-size:1rem;margin-top:40px;transition:background .3s}
        .view-all-btn:hover{background:#0f2840}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="<?= SITE_URL ?>/index.php" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text">GadgetZone</span>
            </a>
        </div>
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
            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon cart-icon" title="Cart">
                🛒 <span class="cart-badge" id="cartBadge"><?= getCartCount() ?></span>
            </a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <span class="logo-text">GadgetZone</span>
        <button class="close-menu" id="closeMenu">✕</button>
    </div>
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

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Next-Gen Gadgets &amp; Smartphones</h1>
                <p>Explore the latest tech deals — smartphones, accessories, smartwatches and more</p>
                <div class="hero-buttons">
                    <a href="<?= SITE_URL ?>/shop.php" class="btn-cta">Shop Now</a>
                    <a href="<?= SITE_URL ?>/shop.php?sale=1" class="btn-outline">View Deals</a>
                </div>
            </div>
            <div class="hero-image">📱</div>
        </div>
    </div>
</section>

<!-- Promo Banners -->
<section class="promo-banners">
    <div class="container">
        <div class="promo-grid">
            <div class="promo-card">
                <div class="promo-icon">🔥</div>
                <div><h3>Flash Sale! Up to 50% OFF</h3><p>Limited time only</p></div>
            </div>
            <div class="promo-card">
                <div class="promo-icon">🚚</div>
                <div><h3>Free Shipping</h3><p>On orders over ₦50,000</p></div>
            </div>
            <div class="promo-card">
                <div class="promo-icon">⭐</div>
                <div><h3>New Arrivals</h3><p>Fresh gadgets every week</p></div>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Browse our wide range of tech categories</p>
        <div class="categories-grid">
            <a href="<?= SITE_URL ?>/shop.php?category=smartphones" class="category-card">
                <div class="category-circle">📱</div><span>Smartphones</span>
            </a>
            <a href="<?= SITE_URL ?>/shop.php?category=accessories" class="category-card">
                <div class="category-circle">🎧</div><span>Accessories</span>
            </a>
            <a href="<?= SITE_URL ?>/shop.php?category=smartwatches" class="category-card">
                <div class="category-circle">⌚</div><span>Smartwatches</span>
            </a>
            <a href="<?= SITE_URL ?>/shop.php?category=gaming" class="category-card">
                <div class="category-circle">🎮</div><span>Gaming</span>
            </a>
            <a href="<?= SITE_URL ?>/shop.php?category=tablets" class="category-card">
                <div class="category-circle">📟</div><span>Tablets</span>
            </a>
        </div>
    </div>
</section>

<!-- Trending Products -->
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">Trending Products</h2>
        <p class="section-subtitle">Most popular gadgets right now</p>
        <div class="products-grid">
            <?php foreach (array_slice($trending_products, 0, 4) as $p):
                $display_price = !empty($p['sale_price']) ? $p['sale_price'] : $p['price'];
                $orig_price    = $p['original_price'] ?? null;
                $discount      = ($orig_price && $orig_price > $display_price) ? round((1 - $display_price/$orig_price)*100) : 0;
                $cat_label     = $p['category_name'] ?? 'Gadgets';
                $pEmoji        = getCategoryEmoji($cat_label, $emoji_map);
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <?= $pEmoji ?>
                    <?php endif; ?>
                    <?php if ($discount > 0): ?><span class="product-badge">-<?= $discount ?>%</span><?php endif; ?>
                </div>
                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($cat_label) ?></div>
                    <div class="product-title"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-stars">
                        <?php $r = round($p['rating'] ?? 4); for($i=1;$i<=5;$i++) echo $i<=$r?'★':'☆'; ?>
                        <span style="color:#888;font-size:.8rem;">(<?= $p['reviews_count'] ?? 0 ?>)</span>
                    </div>
                    <div class="product-price">
                        <span class="price-current"><?= formatPrice($display_price) ?></span>
                        <?php if ($orig_price && $orig_price > $display_price): ?>
                        <span class="price-original"><?= formatPrice($orig_price) ?></span>
                        <?php endif; ?>
                    </div>
                    <button class="btn-add-cart" onclick="addToCart(<?= (int)$p['id'] ?>,'<?= htmlspecialchars(addslashes($p['name'])) ?>',<?= (float)$display_price ?>)">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center"><a href="<?= SITE_URL ?>/shop.php" class="view-all-btn">View All Products</a></div>
    </div>
</section>

<!-- Flash Sale -->
<section class="flash-sale">
    <div class="container">
        <div class="flash-header">
            <h2>⚡ Flash Sale</h2>
            <div class="countdown" id="flashCountdown">
                <div class="countdown-item"><span class="num" id="fcHours">06</span><span class="lbl">Hours</span></div>
                <div class="countdown-item"><span class="num" id="fcMins">00</span><span class="lbl">Mins</span></div>
                <div class="countdown-item"><span class="num" id="fcSecs">00</span><span class="lbl">Secs</span></div>
            </div>
        </div>
        <div class="products-grid">
            <?php
            $flash_items = [
                ['emoji'=>'📱','cat'=>'Smartphones','name'=>'iPhone 14 128GB','rating'=>5,'price'=>580000,'orig'=>950000],
                ['emoji'=>'🎧','cat'=>'Accessories','name'=>'Sony WH-1000XM5','rating'=>5,'price'=>195000,'orig'=>300000],
                ['emoji'=>'⌚','cat'=>'Smartwatches','name'=>'Samsung Galaxy Watch 6','rating'=>4,'price'=>150000,'orig'=>200000],
                ['emoji'=>'🎮','cat'=>'Gaming','name'=>'PS5 DualSense Controller','rating'=>5,'price'=>45000,'orig'=>65000],
            ];
            foreach ($flash_items as $fi):
                $fd = round((1-$fi['price']/$fi['orig'])*100);
            ?>
            <div class="product-card flash-card">
                <div class="product-image" style="background:rgba(255,255,255,.05)">
                    <?= $fi['emoji'] ?>
                    <span class="product-badge">-<?= $fd ?>%</span>
                </div>
                <div class="product-info">
                    <div class="product-category"><?= $fi['cat'] ?></div>
                    <div class="product-title"><?= $fi['name'] ?></div>
                    <div class="product-stars"><?= str_repeat('★',$fi['rating']).str_repeat('☆',5-$fi['rating']) ?></div>
                    <div class="product-price">
                        <span class="price-current"><?= formatPrice($fi['price']) ?></span>
                        <span class="price-original"><?= formatPrice($fi['orig']) ?></span>
                    </div>
                    <button class="btn-add-cart" onclick="addToCart(0,'<?= $fi['name'] ?>',<?= $fi['price'] ?>)">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Video Reviews -->
<section class="section">
    <div class="container">
        <h2 class="section-title">📹 Video Reviews</h2>
        <p class="section-subtitle">Watch honest reviews from our tech experts</p>
        <div class="videos-grid">
            <div class="video-card">
                <div class="video-thumbnail">
                    <div class="play-btn">▶</div>
                    <span class="video-duration">12:45</span>
                </div>
                <div class="video-info">
                    <div class="video-title">iPhone 15 Pro Max - Full Review 2024</div>
                    <div class="video-meta">GadgetZone Reviews · 245K views</div>
                </div>
            </div>
            <div class="video-card">
                <div class="video-thumbnail" style="background:linear-gradient(135deg,#ff6b35,#ff8c5a)">
                    <div class="play-btn">▶</div>
                    <span class="video-duration">8:32</span>
                </div>
                <div class="video-info">
                    <div class="video-title">Samsung S24 Ultra Unboxing &amp; First Look</div>
                    <div class="video-meta">TechUnbox · 189K views</div>
                </div>
            </div>
            <div class="video-card">
                <div class="video-thumbnail" style="background:linear-gradient(135deg,#2d5f8f,#4a7fb5)">
                    <div class="play-btn">▶</div>
                    <span class="video-duration">15:20</span>
                </div>
                <div class="video-info">
                    <div class="video-title">Best Wireless Earbuds Under ₦50,000</div>
                    <div class="video-meta">GadgetZone Reviews · 127K views</div>
                </div>
            </div>
        </div>
        <div style="text-align:center;margin-top:30px">
            <a href="<?= SITE_URL ?>/video.php" class="view-all-btn">View All Videos</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section testimonials">
    <div class="container">
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="section-subtitle">Trusted by thousands of happy customers</p>
        <div class="testimonials-grid">
            <?php
            $testimonials = [
                ['init'=>'AJ','name'=>'Adaeze Johnson','role'=>'Verified Buyer','stars'=>5,'text'=>'"Amazing experience! Got my iPhone 15 delivered in 24 hours. The product was genuine and the price was the best I found online."'],
                ['init'=>'KO','name'=>'Kelechi Okonkwo','role'=>'Verified Buyer','stars'=>5,'text'=>'"GadgetZone has the best collection of accessories. My AirPods came quickly and customer support was super helpful. Will definitely shop again!"'],
                ['init'=>'FM','name'=>'Fatima Musa','role'=>'Verified Buyer','stars'=>4,'text'=>'"Great prices, excellent packaging and fast delivery. I ordered a Samsung Galaxy and received it in perfect condition. Highly recommend!"'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="testimonial-card">
                <div class="testimonial-stars"><?= str_repeat('★',$t['stars']).str_repeat('☆',5-$t['stars']) ?></div>
                <p class="testimonial-text"><?= $t['text'] ?></p>
                <div class="testimonial-author">
                    <div class="author-avatar"><?= $t['init'] ?></div>
                    <div>
                        <div class="author-name"><?= $t['name'] ?></div>
                        <div class="author-title"><?= $t['role'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section">
    <div class="container">
        <h2>📬 Stay Updated!</h2>
        <p>Subscribe to get exclusive deals, new arrivals, and tech tips straight to your inbox.</p>
        <form class="newsletter-input-group" onsubmit="handleNewsletterSubmit(event)">
            <input type="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section>

<!-- Trust Badges -->
<section class="trust-badges-section">
    <div class="container">
        <div class="trust-badges-grid">
            <div class="trust-badge-card"><div class="trust-badge-icon">✅</div><h4>Genuine Products</h4><p>100% authentic from authorized dealers</p></div>
            <div class="trust-badge-card"><div class="trust-badge-icon">🔒</div><h4>Secure Payments</h4><p>SSL encrypted transactions</p></div>
            <div class="trust-badge-card"><div class="trust-badge-icon">🚀</div><h4>Fast Delivery</h4><p>Same-day in Lagos, 3-5 days nationwide</p></div>
            <div class="trust-badge-card"><div class="trust-badge-icon">💬</div><h4>24/7 Support</h4><p>Expert team always ready to help</p></div>
        </div>
    </div>
</section>

<!-- App Download -->
<section class="app-section">
    <div class="container">
        <div class="app-content">
            <div class="app-text">
                <h2>📱 Shop on the Go!</h2>
                <p>Download the GadgetZone app for exclusive app-only deals, order tracking, and a seamless shopping experience.</p>
                <div class="app-buttons">
                    <a href="#" class="app-btn">
                        <span style="font-size:1.5rem">🍎</span>
                        <div><div class="app-btn-sub">Download on the</div><div class="app-btn-main">App Store</div></div>
                    </a>
                    <a href="#" class="app-btn">
                        <span style="font-size:1.5rem">🤖</span>
                        <div><div class="app-btn-sub">Get it on</div><div class="app-btn-main">Google Play</div></div>
                    </a>
                </div>
            </div>
            <div class="app-image">📲</div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>⚡ GadgetZone</h3>
                <p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p>
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Twitter</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">YouTube</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
                    <li><a href="<?= SITE_URL ?>/video.php">Videos</a></li>
                    <li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=smartwatches">Smartwatches</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=tablets">Tablets</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=gaming">Gaming Gadgets</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Newsletter</h4>
                <p>Subscribe for deals and new arrivals!</p>
                <form class="newsletter-form footer-newsletter" onsubmit="handleNewsletterSubmit(event)">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="btn-primary">Subscribe</button>
                </form>
                <div class="trust-badges" style="margin-top:16px">
                    <div class="trust-badge">🔒 Secure</div>
                    <div class="trust-badge">🚚 Fast Delivery</div>
                    <div class="trust-badge">💬 24/7 Support</div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2024 GadgetZone. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </div>
</footer>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item active">
        <span class="bottom-nav-icon">🏠</span><span class="bottom-nav-label">Home</span>
    </a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item">
        <span class="bottom-nav-icon">🛍️</span><span class="bottom-nav-label">Shop</span>
    </a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item">
        <span class="bottom-nav-icon">🛒</span><span class="bottom-nav-label">Cart</span>
    </a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item">
        <span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span>
    </a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
(function(){
    const end = new Date(Date.now() + 6*3600*1000);
    function tick(){
        const d = end - Date.now();
        if(d<=0) return;
        const h=Math.floor(d/3600000), m=Math.floor((d%3600000)/60000), s=Math.floor((d%60000)/1000);
        document.getElementById('fcHours').textContent = String(h).padStart(2,'0');
        document.getElementById('fcMins').textContent  = String(m).padStart(2,'0');
        document.getElementById('fcSecs').textContent  = String(s).padStart(2,'0');
    }
    tick(); setInterval(tick, 1000);
})();

function handleNewsletterSubmit(e){
    e.preventDefault();
    const btn = e.target.querySelector('button');
    btn.textContent = '✓ Subscribed!';
    btn.style.background = '#1e3a5f';
    setTimeout(()=>{ btn.textContent='Subscribe'; e.target.reset(); }, 3000);
}

if(typeof addToCart==='undefined'){
    function addToCart(id, name, price){
        fetch('<?= SITE_URL ?>/api/cart.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({action:'add',product_id:id,name,price,quantity:1})
        }).then(r=>r.json()).then(d=>{
            const badge=document.getElementById('cartBadge');
            if(badge && d.cart_count !== undefined) badge.textContent = d.cart_count;
            const msg=document.createElement('div');
            msg.textContent='✓ Added to cart!';
            msg.style.cssText='position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1e3a5f;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;z-index:9999;animation:fadeInUp .3s';
            document.body.appendChild(msg);
            setTimeout(()=>msg.remove(), 2500);
        }).catch(()=>{
            alert('Added to cart!');
        });
    }
}
</script>
</body>
</html>
