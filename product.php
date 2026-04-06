<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$id      = (int)($_GET['id'] ?? 0);
$product = null;
$related = [];
try {
    if ($id > 0) {
        $product = getProduct($id);
        if ($product) {
            $db    = getDB();
            $cid   = (int)($product['category_id'] ?? 0);
            $res   = $db->query("SELECT p.*,c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.status='active' AND p.category_id=$cid AND p.id!=$id LIMIT 4");
            $related = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
    }
} catch (Exception $e) {
    $product = null;
}

// Demo product if none found
if (!$product) {
    $product = [
        'id'           => $id ?: 1,
        'name'         => 'iPhone 15 Pro Max 256GB',
        'category_name'=> 'Smartphones',
        'category_id'  => 1,
        'price'        => 850000,
        'sale_price'   => null,
        'original_price'=> 950000,
        'image'        => '',
        'images'       => '',
        'rating'       => 4.8,
        'reviews_count'=> 124,
        'stock'        => 15,
        'sku'          => 'IP15PM-256',
        'description'  => 'The iPhone 15 Pro Max is Apple\'s most powerful smartphone yet, featuring the A17 Pro chip, a titanium design, and a revolutionary camera system with 5x optical zoom.',
        'short_description' => 'Apple\'s flagship with A17 Pro chip, titanium build, and 5x optical zoom.',
        'specifications' => '',
    ];
    $related = [
        ['id'=>2,'name'=>'Samsung Galaxy S24 Ultra','category_name'=>'Smartphones','price'=>750000,'sale_price'=>null,'original_price'=>820000,'image'=>'','rating'=>4.7,'reviews_count'=>98],
        ['id'=>3,'name'=>'Google Pixel 8 Pro','category_name'=>'Smartphones','price'=>620000,'sale_price'=>null,'original_price'=>700000,'image'=>'','rating'=>4.5,'reviews_count'=>67],
        ['id'=>4,'name'=>'OnePlus 12 Pro','category_name'=>'Smartphones','price'=>490000,'sale_price'=>null,'original_price'=>560000,'image'=>'','rating'=>4.5,'reviews_count'=>78],
    ];
}

$dp  = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];
$op  = $product['original_price'] ?? null;
$dis = ($op && $op > $dp) ? round((1-$dp/$op)*100) : 0;
$emoji_map = ['Smartphones'=>'📱','Accessories'=>'🎧','Smartwatches'=>'⌚','Gaming'=>'🎮','Tablets'=>'📟','Default'=>'🛒'];
$pEmoji = $emoji_map[$product['category_name']] ?? $emoji_map['Default'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> — GadgetZone</title>
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
        .nav-links a{color:rgba(255,255,255,.85);padding:8px 12px;border-radius:6px;font-size:.9rem;transition:all .2s}
        .nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
        .nav-icons{display:flex;align-items:center;gap:4px}
        .nav-icon{color:rgba(255,255,255,.85);padding:8px 10px;border-radius:6px;font-size:1rem;position:relative;transition:all .2s}
        .nav-icon:hover{background:rgba(255,255,255,.1);color:#fff}
        .cart-badge{background:#ff6b35;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;position:absolute;top:4px;right:4px}
        .hamburger{display:none;background:none;border:none;cursor:pointer;padding:4px}
        .hamburger span{display:block;width:24px;height:2px;background:#fff;margin:5px 0}
        .mobile-menu{display:none;position:fixed;top:0;right:-100%;width:280px;height:100vh;background:#fff;z-index:1001;transition:right .3s;box-shadow:-5px 0 20px rgba(0,0,0,.2);overflow-y:auto}
        .mobile-menu.open{right:0}
        .mobile-menu-header{background:#1e3a5f;color:#fff;padding:20px;display:flex;justify-content:space-between;align-items:center}
        .mobile-menu-header .logo-text{font-size:1.3rem;font-weight:800}
        .close-menu{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer}
        .mobile-nav-links{padding:16px 0}
        .mobile-nav-links a{display:block;padding:14px 20px;color:#333;border-bottom:1px solid #f0f0f0;font-weight:500}
        .mobile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000}
        .mobile-overlay.open{display:block}
        .page-header{background:#1e3a5f;color:#fff;padding:20px 0}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;opacity:.8}
        .breadcrumb a{color:rgba(255,255,255,.8)}
        .breadcrumb a:hover{color:#ff6b35}
        .product-page{padding:40px 0}
        .product-layout{display:grid;grid-template-columns:1fr 1fr;gap:48px;background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:40px}
        .gallery{}
        .main-image{height:400px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:120px;margin-bottom:12px;overflow:hidden;cursor:zoom-in;position:relative}
        .main-image img{width:100%;height:100%;object-fit:contain}
        .thumbnails{display:flex;gap:10px}
        .thumb{width:80px;height:80px;border-radius:8px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:30px;cursor:pointer;border:2px solid transparent;transition:all .2s}
        .thumb.active,.thumb:hover{border-color:#1e3a5f}
        .product-details{}
        .product-category-tag{font-size:.78rem;color:#ff6b35;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px}
        .product-name{font-size:1.8rem;font-weight:800;color:#1e3a5f;margin-bottom:12px;line-height:1.3}
        .product-rating{display:flex;align-items:center;gap:10px;margin-bottom:16px}
        .stars{color:#ffc107;font-size:1.1rem}
        .rating-text{font-size:.9rem;color:#666}
        .product-pricing{margin-bottom:20px;padding:16px;background:#f8f9fa;border-radius:10px}
        .current-price{font-size:2rem;font-weight:800;color:#1e3a5f}
        .original-price{font-size:1rem;color:#999;text-decoration:line-through;margin-left:12px}
        .discount-badge{background:#ff6b35;color:#fff;font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:20px;margin-left:8px}
        .savings{font-size:.85rem;color:#22c55e;margin-top:4px}
        .product-short-desc{color:#555;line-height:1.7;margin-bottom:24px;font-size:.95rem}
        .option-group{margin-bottom:20px}
        .option-label{font-size:.9rem;font-weight:700;color:#333;margin-bottom:10px}
        .storage-options,.color-options{display:flex;gap:10px;flex-wrap:wrap}
        .storage-btn{padding:8px 16px;border:2px solid #ddd;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:600;background:#fff;transition:all .2s}
        .storage-btn:hover,.storage-btn.active{border-color:#1e3a5f;background:#1e3a5f;color:#fff}
        .color-circle{width:36px;height:36px;border-radius:50%;cursor:pointer;border:3px solid transparent;transition:all .2s;position:relative}
        .color-circle:hover,.color-circle.active{border-color:#1e3a5f;transform:scale(1.15)}
        .color-circle[title="Space Gray"]{background:#3c3c3c}
        .color-circle[title="Silver"]{background:#e8e8e8;border:3px solid #ddd}
        .color-circle[title="Gold"]{background:#f0c060}
        .color-circle[title="Midnight"]{background:#1a1a2e}
        .qty-selector{display:flex;align-items:center;gap:0;border:2px solid #ddd;border-radius:8px;overflow:hidden;width:fit-content}
        .qty-btn{background:#f8f9fa;border:none;padding:10px 16px;font-size:1.2rem;cursor:pointer;font-weight:700;transition:background .2s}
        .qty-btn:hover{background:#e0e0e0}
        .qty-input{border:none;width:50px;text-align:center;font-size:1rem;font-weight:700;outline:none;padding:10px 0}
        .action-buttons{display:flex;gap:12px;margin-top:20px;flex-wrap:wrap}
        .btn-cart{flex:1;background:#ff6b35;color:#fff;border:none;padding:14px 24px;border-radius:10px;font-weight:700;font-size:1rem;cursor:pointer;transition:background .3s;min-width:140px}
        .btn-cart:hover{background:#e55a25}
        .btn-buy{flex:1;background:#1e3a5f;color:#fff;border:none;padding:14px 24px;border-radius:10px;font-weight:700;font-size:1rem;cursor:pointer;transition:background .3s;min-width:140px}
        .btn-buy:hover{background:#0f2840}
        .btn-wish{background:#fff;border:2px solid #ddd;padding:14px 18px;border-radius:10px;cursor:pointer;font-size:1.2rem;transition:all .2s}
        .btn-wish:hover{border-color:#ff6b35;color:#ff6b35}
        .stock-info{display:flex;align-items:center;gap:8px;margin-top:12px;font-size:.85rem}
        .stock-dot{width:8px;height:8px;border-radius:50%;background:#22c55e}
        .stock-dot.low{background:#f59e0b}
        .product-tabs{background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:40px}
        .tab-nav{display:flex;gap:0;border-bottom:2px solid #f0f0f0;margin-bottom:24px}
        .tab-btn{padding:12px 24px;border:none;background:transparent;font-size:.95rem;font-weight:600;cursor:pointer;color:#888;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s}
        .tab-btn.active,.tab-btn:hover{color:#1e3a5f;border-bottom-color:#ff6b35}
        .tab-content{display:none}
        .tab-content.active{display:block}
        .spec-table{width:100%;border-collapse:collapse}
        .spec-table tr{border-bottom:1px solid #f0f0f0}
        .spec-table td{padding:12px 16px;font-size:.9rem}
        .spec-table td:first-child{font-weight:600;color:#1e3a5f;width:40%;background:#f8f9fa}
        .related-section h2{font-size:1.5rem;font-weight:700;color:#1e3a5f;margin-bottom:24px}
        .related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
        .product-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:transform .3s}
        .product-card:hover{transform:translateY(-4px)}
        .product-image{height:160px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:60px}
        .product-info{padding:14px}
        .product-category{font-size:.72rem;color:#ff6b35;font-weight:600;text-transform:uppercase}
        .product-title{font-size:.88rem;font-weight:600;color:#333;margin:5px 0;line-height:1.4}
        .product-stars{color:#ffc107;font-size:.8rem;margin-bottom:6px}
        .product-price{display:flex;align-items:center;gap:8px;margin-bottom:10px}
        .price-current{font-size:1rem;font-weight:700;color:#1e3a5f}
        .price-original{font-size:.8rem;color:#999;text-decoration:line-through}
        .btn-add-cart{background:#ff6b35;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-weight:600;cursor:pointer;width:100%;transition:background .3s;font-size:.85rem}
        .btn-add-cart:hover{background:#e55a25}
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
        .bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem}
        .bottom-nav-item:hover,.bottom-nav-item.active{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.related-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.product-layout{grid-template-columns:1fr}.main-image{height:280px}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.footer-grid{grid-template-columns:1fr}.related-grid{grid-template-columns:1fr}}
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
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/index.php">Home</a> /
            <a href="<?= SITE_URL ?>/shop.php">Shop</a> /
            <a href="<?= SITE_URL ?>/shop.php?category=<?= urlencode(strtolower($product['category_name'])) ?>"><?= htmlspecialchars($product['category_name']) ?></a> /
            <span><?= htmlspecialchars($product['name']) ?></span>
        </div>
    </div>
</div>

<div class="product-page">
    <div class="container">
        <!-- Product Main -->
        <div class="product-layout">
            <!-- Gallery -->
            <div class="gallery">
                <div class="main-image" id="mainImage">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="mainImg">
                    <?php else: echo $pEmoji; endif; ?>
                </div>
                <div class="thumbnails">
                    <div class="thumb active" onclick="setThumb(this)"><?= $pEmoji ?></div>
                    <div class="thumb" onclick="setThumb(this)" style="font-size:25px">🔍</div>
                    <div class="thumb" onclick="setThumb(this)" style="font-size:25px">📦</div>
                    <div class="thumb" onclick="setThumb(this)" style="font-size:25px">🔋</div>
                </div>
            </div>

            <!-- Details -->
            <div class="product-details">
                <div class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></div>
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-rating">
                    <span class="stars"><?php $r=round($product['rating']??4.5); for($i=1;$i<=5;$i++) echo $i<=$r?'★':'☆'; ?></span>
                    <span class="rating-text"><?= number_format($product['rating']??4.5,1) ?> (<?= $product['reviews_count']??0 ?> reviews)</span>
                    <span style="color:#888;font-size:.85rem">|</span>
                    <span style="font-size:.85rem;color:#22c55e">SKU: <?= htmlspecialchars($product['sku']??'N/A') ?></span>
                </div>
                <div class="product-pricing">
                    <div>
                        <span class="current-price"><?= formatPrice($dp) ?></span>
                        <?php if ($op && $op>$dp): ?>
                        <span class="original-price"><?= formatPrice($op) ?></span>
                        <span class="discount-badge">-<?= $dis ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($op && $op>$dp): ?>
                    <div class="savings">You save <?= formatPrice($op-$dp) ?> 🎉</div>
                    <?php endif; ?>
                </div>
                <p class="product-short-desc"><?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? '') ?></p>

                <!-- Storage Options -->
                <div class="option-group">
                    <div class="option-label">Storage: <span id="selectedStorage">256GB</span></div>
                    <div class="storage-options">
                        <?php foreach(['128GB','256GB','512GB','1TB'] as $s): ?>
                        <button class="storage-btn <?= $s==='256GB'?'active':'' ?>" onclick="selectStorage(this,'<?= $s ?>')"><?= $s ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Color Options -->
                <div class="option-group">
                    <div class="option-label">Color: <span id="selectedColor">Space Gray</span></div>
                    <div class="color-options">
                        <div class="color-circle active" title="Space Gray" onclick="selectColor(this,'Space Gray')"></div>
                        <div class="color-circle" title="Silver" onclick="selectColor(this,'Silver')"></div>
                        <div class="color-circle" title="Gold" onclick="selectColor(this,'Gold')"></div>
                        <div class="color-circle" title="Midnight" onclick="selectColor(this,'Midnight')"></div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="option-group">
                    <div class="option-label">Quantity:</div>
                    <div class="qty-selector">
                        <button class="qty-btn" onclick="changeQty(-1)">−</button>
                        <input type="number" id="qty" class="qty-input" value="1" min="1" max="<?= (int)($product['stock']??99) ?>">
                        <button class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-cart" onclick="addProductToCart()">🛒 Add to Cart</button>
                    <button class="btn-buy" onclick="buyNow()">⚡ Buy Now</button>
                    <button class="btn-wish" onclick="addToWishlist(<?= (int)$product['id'] ?>)" title="Add to Wishlist">♡</button>
                </div>

                <div class="stock-info">
                    <?php $stock = (int)($product['stock']??15); ?>
                    <div class="stock-dot <?= $stock<5?'low':'' ?>"></div>
                    <span style="color:#22c55e;font-weight:600"><?= $stock>0?"In Stock ($stock units)":"Out of Stock" ?></span>
                    <span style="color:#888">· Fast Delivery Available</span>
                </div>

                <div style="margin-top:16px;padding:12px;background:#f0f4ff;border-radius:8px;font-size:.85rem;color:#1e3a5f">
                    🚚 <strong>Free shipping</strong> on this order · 🔄 <strong>30-day returns</strong> · ✅ <strong>Genuine product</strong>
                </div>
            </div>
        </div>

        <!-- Tabs: Description / Specs -->
        <div class="product-tabs">
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab(this,'desc')">Description</button>
                <button class="tab-btn" onclick="switchTab(this,'specs')">Specifications</button>
                <button class="tab-btn" onclick="switchTab(this,'reviews')">Reviews (<?= $product['reviews_count']??0 ?>)</button>
            </div>
            <div id="tab-desc" class="tab-content active">
                <p style="line-height:1.8;color:#555"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
            </div>
            <div id="tab-specs" class="tab-content">
                <table class="spec-table">
                    <tr><td>Brand</td><td><?= explode(' ',$product['name'])[0] ?></td></tr>
                    <tr><td>Model</td><td><?= htmlspecialchars($product['name']) ?></td></tr>
                    <tr><td>Category</td><td><?= htmlspecialchars($product['category_name']) ?></td></tr>
                    <tr><td>SKU</td><td><?= htmlspecialchars($product['sku']??'N/A') ?></td></tr>
                    <tr><td>Availability</td><td><?= ($product['stock']??1)>0?'In Stock':'Out of Stock' ?></td></tr>
                    <tr><td>Warranty</td><td>1 Year Manufacturer Warranty</td></tr>
                    <tr><td>In the Box</td><td>Device, Cable, Documentation</td></tr>
                </table>
            </div>
            <div id="tab-reviews" class="tab-content">
                <div style="text-align:center;padding:40px;color:#888">
                    <div style="font-size:3rem;margin-bottom:12px">⭐</div>
                    <h3 style="color:#1e3a5f;margin-bottom:8px"><?= number_format($product['rating']??4.5,1) ?>/5.0</h3>
                    <p>Based on <?= $product['reviews_count']??0 ?> verified reviews</p>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)): ?>
        <div class="related-section">
            <h2>Related Products</h2>
            <div class="related-grid">
                <?php foreach ($related as $rp):
                    $rdp = !empty($rp['sale_price']) ? $rp['sale_price'] : $rp['price'];
                    $rop = $rp['original_price'] ?? null;
                    $rem = $emoji_map[$rp['category_name']??''] ?? '🛒';
                ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$rp['id'] ?>">
                        <div class="product-image"><?= $rem ?></div>
                    </a>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($rp['category_name']??'') ?></div>
                        <div class="product-title"><a href="<?= SITE_URL ?>/product.php?id=<?= (int)$rp['id'] ?>" style="color:inherit"><?= htmlspecialchars($rp['name']) ?></a></div>
                        <div class="product-stars"><?php $rv=round($rp['rating']??4); for($i=1;$i<=5;$i++) echo $i<=$rv?'★':'☆'; ?></div>
                        <div class="product-price">
                            <span class="price-current"><?= formatPrice($rdp) ?></span>
                            <?php if ($rop && $rop>$rdp): ?><span class="price-original"><?= formatPrice($rop) ?></span><?php endif; ?>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart(<?= (int)$rp['id'] ?>,'<?= htmlspecialchars(addslashes($rp['name'])) ?>',<?= (float)$rdp ?>)">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
const PRODUCT_ID   = <?= (int)$product['id'] ?>;
const PRODUCT_NAME = <?= json_encode($product['name']) ?>;
const PRODUCT_PRICE = <?= (float)$dp ?>;

function setThumb(el){document.querySelectorAll('.thumb').forEach(t=>t.classList.remove('active'));el.classList.add('active')}
function selectStorage(el,val){document.querySelectorAll('.storage-btn').forEach(b=>b.classList.remove('active'));el.classList.add('active');document.getElementById('selectedStorage').textContent=val}
function selectColor(el,val){document.querySelectorAll('.color-circle').forEach(c=>c.classList.remove('active'));el.classList.add('active');document.getElementById('selectedColor').textContent=val}
function changeQty(d){const i=document.getElementById('qty');const v=Math.max(1,parseInt(i.value)+d);i.value=v}
function switchTab(btn,id){document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));btn.classList.add('active');document.getElementById('tab-'+id).classList.add('active')}
function addProductToCart(){const q=parseInt(document.getElementById('qty').value);addToCart(PRODUCT_ID,PRODUCT_NAME,PRODUCT_PRICE,q)}
function buyNow(){addProductToCart();window.location.href='<?= SITE_URL ?>/checkout.php'}

if(typeof addToCart==='undefined'){
    function addToCart(id,name,price,qty=1){
        fetch('<?= SITE_URL ?>/api/cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'add',product_id:id,name,price,quantity:qty})})
        .then(r=>r.json()).then(d=>{const b=document.getElementById('cartBadge');if(b&&d.cart_count!==undefined)b.textContent=d.cart_count;showToast('✓ Added to cart!');}).catch(()=>showToast('✓ Added to cart!'));
    }
}
if(typeof addToWishlist==='undefined'){function addToWishlist(id){showToast('♥ Added to wishlist!')}}
function showToast(msg){const t=document.createElement('div');t.textContent=msg;t.style.cssText='position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1e3a5f;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;z-index:9999';document.body.appendChild(t);setTimeout(()=>t.remove(),2500)}
</script>
</body>
</html>
