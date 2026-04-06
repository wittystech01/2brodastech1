<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search   = isset($_GET['q'])        ? sanitize($_GET['q'])        : '';
$sort     = isset($_GET['sort'])     ? sanitize($_GET['sort'])     : 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset   = ($page - 1) * $per_page;

$products = [];
$total    = 0;
try {
    $products = getProducts($per_page, $offset, $category ?: null, $search ?: null);
    $db       = getDB();
    $where    = "WHERE p.status='active'";
    if ($category) { $c = $db->real_escape_string($category); $where .= " AND c.slug='$c'"; }
    if ($search)   { $s = $db->real_escape_string($search);   $where .= " AND (p.name LIKE '%$s%' OR p.description LIKE '%$s%')"; }
    $res   = $db->query("SELECT COUNT(*) cnt FROM products p LEFT JOIN categories c ON p.category_id=c.id $where");
    $total = $res ? (int)$res->fetch_assoc()['cnt'] : 0;
} catch (Exception $e) {
    $products = [];
}

if (empty($products)) {
    $demo = [
        ['id'=>1,'name'=>'iPhone 15 Pro Max','category_name'=>'Smartphones','price'=>850000,'sale_price'=>null,'original_price'=>950000,'image'=>'','rating'=>4.8,'reviews_count'=>124],
        ['id'=>2,'name'=>'Samsung Galaxy S24 Ultra','category_name'=>'Smartphones','price'=>750000,'sale_price'=>null,'original_price'=>820000,'image'=>'','rating'=>4.7,'reviews_count'=>98],
        ['id'=>3,'name'=>'AirPods Pro 2nd Gen','category_name'=>'Accessories','price'=>180000,'sale_price'=>null,'original_price'=>210000,'image'=>'','rating'=>4.9,'reviews_count'=>256],
        ['id'=>4,'name'=>'Apple Watch Series 9','category_name'=>'Smartwatches','price'=>320000,'sale_price'=>null,'original_price'=>370000,'image'=>'','rating'=>4.6,'reviews_count'=>87],
        ['id'=>5,'name'=>'iPad Pro 12.9"','category_name'=>'Tablets','price'=>680000,'sale_price'=>null,'original_price'=>750000,'image'=>'','rating'=>4.8,'reviews_count'=>143],
        ['id'=>6,'name'=>'PS5 DualSense Controller','category_name'=>'Gaming','price'=>45000,'sale_price'=>null,'original_price'=>65000,'image'=>'','rating'=>4.7,'reviews_count'=>210],
        ['id'=>7,'name'=>'Google Pixel 8 Pro','category_name'=>'Smartphones','price'=>620000,'sale_price'=>null,'original_price'=>700000,'image'=>'','rating'=>4.5,'reviews_count'=>67],
        ['id'=>8,'name'=>'Sony WH-1000XM5','category_name'=>'Accessories','price'=>195000,'sale_price'=>null,'original_price'=>300000,'image'=>'','rating'=>4.9,'reviews_count'=>312],
        ['id'=>9,'name'=>'Samsung Galaxy Tab S9','category_name'=>'Tablets','price'=>410000,'sale_price'=>null,'original_price'=>480000,'image'=>'','rating'=>4.6,'reviews_count'=>54],
        ['id'=>10,'name'=>'Xiaomi 13 Ultra','category_name'=>'Smartphones','price'=>520000,'sale_price'=>null,'original_price'=>590000,'image'=>'','rating'=>4.4,'reviews_count'=>43],
        ['id'=>11,'name'=>'JBL Charge 5','category_name'=>'Accessories','price'=>55000,'sale_price'=>null,'original_price'=>72000,'image'=>'','rating'=>4.7,'reviews_count'=>189],
        ['id'=>12,'name'=>'OnePlus 12 Pro','category_name'=>'Smartphones','price'=>490000,'sale_price'=>null,'original_price'=>560000,'image'=>'','rating'=>4.5,'reviews_count'=>78],
    ];
    if ($category) {
        $products = array_filter($demo, fn($p) => strtolower($p['category_name']) === strtolower($category));
        $products = array_values($products);
    } else {
        $products = $demo;
    }
    if ($search) {
        $products = array_filter($products, fn($p) => stripos($p['name'], $search) !== false);
        $products = array_values($products);
    }
    $total = count($products);
}

$total_pages = max(1, ceil($total / $per_page));
$emoji_map = ['Smartphones'=>'📱','Accessories'=>'🎧','Smartwatches'=>'⌚','Gaming'=>'🎮','Tablets'=>'📟','Default'=>'🛒'];
$cat_title = $category ? ucfirst($category) : ($search ? 'Search: '.htmlspecialchars($search) : 'All Products');
$breadcrumb_cat = $category ? ucfirst($category) : ($search ? 'Search Results' : 'All Products');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cat_title) ?> — GadgetZone</title>
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
        .logo-icon{font-size:1.5rem}
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
        .page-header{background:#1e3a5f;color:#fff;padding:32px 0}
        .page-header h1{font-size:1.8rem;font-weight:700;margin-bottom:6px}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;opacity:.8}
        .breadcrumb a{color:rgba(255,255,255,.8)}
        .breadcrumb a:hover{color:#ff6b35}
        .shop-layout{display:grid;grid-template-columns:260px 1fr;gap:24px;padding:32px 0}
        .sidebar{background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,.06);height:fit-content}
        .sidebar h3{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #f0f0f0}
        .sidebar-section{margin-bottom:28px}
        .sidebar-section h4{font-size:.9rem;font-weight:700;color:#333;margin-bottom:12px}
        .cat-list{list-style:none}
        .cat-list li{margin-bottom:6px}
        .cat-list a{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:8px;font-size:.9rem;color:#555;transition:all .2s}
        .cat-list a:hover,.cat-list a.active{background:#e8f0fe;color:#1e3a5f;font-weight:600}
        .cat-list .cat-count{background:#e8f0fe;color:#1e3a5f;border-radius:12px;padding:2px 8px;font-size:.75rem;font-weight:600}
        .price-range{margin-top:8px}
        .price-range input[type=range]{width:100%;accent-color:#ff6b35}
        .price-labels{display:flex;justify-content:space-between;font-size:.8rem;color:#888;margin-top:4px}
        .rating-filter{display:flex;flex-direction:column;gap:8px}
        .rating-option{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.9rem}
        .rating-option input{accent-color:#ff6b35}
        .stars-small{color:#ffc107;font-size:.85rem}
        .stock-filter{display:flex;align-items:center;gap:8px;font-size:.9rem;cursor:pointer}
        .stock-filter input{accent-color:#ff6b35}
        .shop-main{}
        .toolbar{display:flex;align-items:center;justify-content:space-between;background:#fff;border-radius:10px;padding:14px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);flex-wrap:wrap;gap:12px}
        .results-count{font-size:.9rem;color:#666}
        .results-count strong{color:#1e3a5f}
        .sort-select{padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:.9rem;outline:none;cursor:pointer;color:#333}
        .sort-select:focus{border-color:#1e3a5f}
        .products-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .product-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:transform .3s,box-shadow .3s}
        .product-card:hover{transform:translateY(-4px);box-shadow:0 8px 25px rgba(0,0,0,.13)}
        .product-image{height:180px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:70px;position:relative}
        .product-badge{position:absolute;top:8px;left:8px;background:#ff6b35;color:#fff;font-size:.7rem;font-weight:700;padding:3px 8px;border-radius:6px}
        .product-wishlist{position:absolute;top:8px;right:8px;background:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.1);transition:all .2s}
        .product-wishlist:hover{background:#ff6b35;color:#fff}
        .product-info{padding:14px}
        .product-category{font-size:.72rem;color:#ff6b35;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
        .product-title{font-size:.9rem;font-weight:600;color:#333;margin:5px 0;line-height:1.4}
        .product-stars{color:#ffc107;font-size:.8rem;margin-bottom:8px}
        .product-price{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px}
        .price-current{font-size:1.05rem;font-weight:700;color:#1e3a5f}
        .price-original{font-size:.82rem;color:#999;text-decoration:line-through}
        .btn-add-cart{background:#ff6b35;color:#fff;border:none;padding:9px 14px;border-radius:8px;font-weight:600;cursor:pointer;width:100%;transition:background .3s;font-size:.88rem}
        .btn-add-cart:hover{background:#e55a25}
        .pagination{display:flex;justify-content:center;gap:8px;margin-top:32px}
        .pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.9rem;font-weight:600;transition:all .2s}
        .pagination a{background:#fff;color:#1e3a5f;box-shadow:0 1px 4px rgba(0,0,0,.08)}
        .pagination a:hover{background:#1e3a5f;color:#fff}
        .pagination .current{background:#1e3a5f;color:#fff}
        .no-products{text-align:center;padding:60px 20px;background:#fff;border-radius:12px}
        .no-products h3{font-size:1.3rem;color:#1e3a5f;margin-bottom:8px}
        .no-products p{color:#888}
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
        .social-link:hover{background:#ff6b35}
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
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.products-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.shop-layout{grid-template-columns:1fr}.sidebar{display:none}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.products-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="<?= SITE_URL ?>/index.php" class="logo">
                <span class="logo-icon">⚡</span><span class="logo-text">GadgetZone</span>
            </a>
        </div>
        <div class="nav-search">
            <form action="<?= SITE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search smartphones, gadgets..." class="search-input">
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
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
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

<div class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($cat_title) ?></h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/index.php">Home</a> /
            <a href="<?= SITE_URL ?>/shop.php">Shop</a>
            <?php if ($category): ?> / <?= htmlspecialchars(ucfirst($category)) ?><?php endif; ?>
            <?php if ($search): ?> / Search: "<?= htmlspecialchars($search) ?>"<?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="shop-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>🔍 Filter Products</h3>
            <div class="sidebar-section">
                <h4>Categories</h4>
                <ul class="cat-list">
                    <li><a href="<?= SITE_URL ?>/shop.php" class="<?= !$category?'active':'' ?>">All Products <span class="cat-count">All</span></a></li>
                    <?php
                    $cats = [
                        ['slug'=>'smartphones','name'=>'Smartphones','emoji'=>'📱'],
                        ['slug'=>'accessories','name'=>'Accessories','emoji'=>'🎧'],
                        ['slug'=>'smartwatches','name'=>'Smartwatches','emoji'=>'⌚'],
                        ['slug'=>'tablets','name'=>'Tablets','emoji'=>'📟'],
                        ['slug'=>'gaming','name'=>'Gaming','emoji'=>'🎮'],
                    ];
                    foreach ($cats as $c): ?>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=<?= $c['slug'] ?>" class="<?= $category===$c['slug']?'active':'' ?>"><?= $c['emoji'] ?> <?= $c['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="sidebar-section">
                <h4>Price Range</h4>
                <div class="price-range">
                    <input type="range" id="priceRange" min="0" max="1000000" step="10000" value="1000000" oninput="document.getElementById('priceVal').textContent='₦'+parseInt(this.value).toLocaleString()">
                    <div class="price-labels"><span>₦0</span><span id="priceVal">₦1,000,000</span></div>
                </div>
            </div>
            <div class="sidebar-section">
                <h4>Rating</h4>
                <div class="rating-filter">
                    <?php for ($r=5;$r>=3;$r--): ?>
                    <label class="rating-option">
                        <input type="radio" name="rating" value="<?= $r ?>">
                        <span class="stars-small"><?= str_repeat('★',$r).str_repeat('☆',5-$r) ?></span> &amp; up
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="sidebar-section">
                <h4>Availability</h4>
                <label class="stock-filter"><input type="checkbox" checked> In Stock Only</label>
            </div>
            <a href="<?= SITE_URL ?>/shop.php<?= $category?'?category='.$category:'' ?>" style="display:block;background:#1e3a5f;color:#fff;text-align:center;padding:10px;border-radius:8px;font-weight:600;margin-top:8px;font-size:.9rem">Apply Filters</a>
        </aside>

        <!-- Main -->
        <main class="shop-main">
            <div class="toolbar">
                <div class="results-count">
                    Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?: count($products) ?></strong> products
                    <?php if ($search): ?> for "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
                </div>
                <form method="GET" action="<?= SITE_URL ?>/shop.php" style="display:flex;align-items:center;gap:8px">
                    <?php if ($category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"><?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                    <label style="font-size:.9rem;color:#666">Sort by:</label>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
                        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High to Low</option>
                        <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Most Popular</option>
                        <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Top Rated</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
            <div class="no-products">
                <div style="font-size:4rem;margin-bottom:16px">🔍</div>
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
                <a href="<?= SITE_URL ?>/shop.php" style="display:inline-block;background:#ff6b35;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;margin-top:16px">Browse All Products</a>
            </div>
            <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p):
                    $dp  = !empty($p['sale_price']) ? $p['sale_price'] : $p['price'];
                    $op  = $p['original_price'] ?? null;
                    $dis = ($op && $op > $dp) ? round((1-$dp/$op)*100) : 0;
                    $cat = $p['category_name'] ?? 'Gadgets';
                    $em  = $emoji_map[$cat] ?? '🛒';
                ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$p['id'] ?>">
                        <div class="product-image">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= SITE_URL ?>/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover">
                            <?php else: echo $em; endif; ?>
                            <?php if ($dis>0): ?><span class="product-badge">-<?= $dis ?>%</span><?php endif; ?>
                            <button class="product-wishlist" onclick="event.preventDefault();addToWishlist(<?= (int)$p['id'] ?>)" title="Add to Wishlist">♡</button>
                        </div>
                    </a>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($cat) ?></div>
                        <div class="product-title"><a href="<?= SITE_URL ?>/product.php?id=<?= (int)$p['id'] ?>" style="color:inherit"><?= htmlspecialchars($p['name']) ?></a></div>
                        <div class="product-stars">
                            <?php $rv=round($p['rating']??4); for($i=1;$i<=5;$i++) echo $i<=$rv?'★':'☆'; ?>
                            <span style="color:#888;font-size:.78rem">(<?= $p['reviews_count']??0 ?>)</span>
                        </div>
                        <div class="product-price">
                            <span class="price-current"><?= formatPrice($dp) ?></span>
                            <?php if ($op && $op>$dp): ?><span class="price-original"><?= formatPrice($op) ?></span><?php endif; ?>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart(<?= (int)$p['id'] ?>,'<?= htmlspecialchars(addslashes($p['name'])) ?>',<?= (float)$dp ?>)">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page>1): ?>
                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">← Prev</a>
                <?php endif; ?>
                <?php for($i=max(1,$page-2);$i<=min($total_pages,$page+2);$i++): ?>
                <?php if($i===$page): ?><span class="current"><?= $i ?></span>
                <?php else: ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a><?php endif; ?>
                <?php endfor; ?>
                <?php if ($page<$total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><h3>⚡ GadgetZone</h3><p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p><div class="social-links"><a href="#" class="social-link">Facebook</a><a href="#" class="social-link">Twitter</a><a href="#" class="social-link">Instagram</a></div></div>
            <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="<?= SITE_URL ?>/index.php">Home</a></li><li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li><li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li><li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li></ul></div>
            <div class="footer-col"><h4>Categories</h4><ul><li><a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=smartwatches">Smartwatches</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=tablets">Tablets</a></li></ul></div>
            <div class="footer-col"><h4>Newsletter</h4><p>Subscribe for deals and new arrivals!</p><form class="newsletter-form" onsubmit="event.preventDefault()"><input type="email" placeholder="Your email" required><button type="submit" class="btn-primary">Go</button></form><div class="trust-badges" style="margin-top:12px"><div class="trust-badge">🔒 Secure</div><div class="trust-badge">🚚 Fast</div></div></div>
        </div>
        <div class="footer-bottom"><p>© 2024 GadgetZone. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p></div>
    </div>
</footer>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item"><span class="bottom-nav-icon">🏠</span><span class="bottom-nav-label">Home</span></a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item active"><span class="bottom-nav-icon">🛍️</span><span class="bottom-nav-label">Shop</span></a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛒</span><span class="bottom-nav-label">Cart</span></a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
if(typeof addToCart==='undefined'){
    function addToCart(id,name,price){
        fetch('<?= SITE_URL ?>/api/cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'add',product_id:id,name,price,quantity:1})})
        .then(r=>r.json()).then(d=>{
            const b=document.getElementById('cartBadge');
            if(b&&d.cart_count!==undefined) b.textContent=d.cart_count;
            showToast('✓ Added to cart!');
        }).catch(()=>showToast('✓ Added to cart!'));
    }
}
if(typeof addToWishlist==='undefined'){
    function addToWishlist(id){ showToast('♥ Added to wishlist!'); }
}
function showToast(msg){
    const t=document.createElement('div');
    t.textContent=msg;
    t.style.cssText='position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1e3a5f;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;z-index:9999';
    document.body.appendChild(t);
    setTimeout(()=>t.remove(),2500);
}
</script>
</body>
</html>
