<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php?redirect=' . urlencode('/wishlist.php'));
}

$user_id  = (int)$_SESSION['user_id'];
$items    = [];
$msg      = '';

try {
    $db  = getDB();
    $res = $db->query("SELECT w.id as wid, p.*, c.name as category_name FROM wishlists w JOIN products p ON w.product_id=p.id LEFT JOIN categories c ON p.category_id=c.id WHERE w.user_id=$user_id AND p.status='active' ORDER BY w.created_at DESC");
    $items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) { $items = []; }

$emoji_map = ['Smartphones'=>'📱','Accessories'=>'🎧','Smartwatches'=>'⌚','Gaming'=>'🎮','Tablets'=>'📟','Default'=>'🛒'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist — GadgetZone</title>
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
        .wishlist-page{padding:40px 0}
        .wishlist-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px}
        .wish-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07);transition:transform .3s}
        .wish-card:hover{transform:translateY(-4px)}
        .wish-image{height:200px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:80px;position:relative}
        .wish-image img{width:100%;height:100%;object-fit:cover}
        .wish-remove{position:absolute;top:10px;right:10px;background:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.1);transition:all .2s;color:#ef4444}
        .wish-remove:hover{background:#ef4444;color:#fff}
        .wish-info{padding:16px}
        .wish-category{font-size:.72rem;color:#ff6b35;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
        .wish-name{font-size:.95rem;font-weight:600;color:#333;margin:6px 0;line-height:1.4}
        .wish-stars{color:#ffc107;font-size:.85rem;margin-bottom:8px}
        .wish-price{display:flex;align-items:center;gap:8px;margin-bottom:14px}
        .price-current{font-size:1.05rem;font-weight:700;color:#1e3a5f}
        .price-original{font-size:.82rem;color:#999;text-decoration:line-through}
        .wish-actions{display:flex;gap:8px}
        .btn-wish-cart{flex:1;background:#ff6b35;color:#fff;border:none;padding:10px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;transition:background .3s}
        .btn-wish-cart:hover{background:#e55a25}
        .btn-wish-view{background:#1e3a5f;color:#fff;border:none;padding:10px 14px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;transition:background .3s}
        .btn-wish-view:hover{background:#0f2840}
        .empty-state{text-align:center;padding:80px 20px;background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
        .empty-state .empty-icon{font-size:5rem;margin-bottom:16px}
        .empty-state h2{font-size:1.5rem;color:#1e3a5f;margin-bottom:8px}
        .empty-state p{color:#888;margin-bottom:24px}
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
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.wishlist-grid{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:768px){.wishlist-grid{grid-template-columns:repeat(2,1fr)}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.wishlist-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:1fr}}
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
        <a href="<?= SITE_URL ?>/shop.php?category=smartphones">📱 Smartphones</a><a href="<?= SITE_URL ?>/shop.php?category=accessories">🎧 Accessories</a>
        <a href="<?= SITE_URL ?>/blog.php">📝 Blog</a><a href="<?= SITE_URL ?>/contact.php">📞 Contact</a>
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a><a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>❤️ My Wishlist</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a> / Wishlist</div>
    </div>
</div>

<div class="wishlist-page">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">💝</div>
            <h2>Your wishlist is empty</h2>
            <p>Save your favourite gadgets here to purchase later.</p>
            <a href="<?= SITE_URL ?>/shop.php" style="display:inline-block;background:#ff6b35;color:#fff;padding:14px 32px;border-radius:10px;font-weight:700">🛍️ Explore Products</a>
        </div>
        <?php else: ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <p style="color:#666"><?= count($items) ?> item<?= count($items)!==1?'s':'' ?> in your wishlist</p>
            <button onclick="addAllToCart()" style="background:#1e3a5f;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem">Add All to Cart</button>
        </div>
        <div class="wishlist-grid" id="wishlistGrid">
            <?php foreach ($items as $item):
                $dp  = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
                $op  = $item['original_price'] ?? null;
                $cat = $item['category_name'] ?? 'Gadgets';
                $em  = $emoji_map[$cat] ?? '🛒';
            ?>
            <div class="wish-card" id="wish-<?= (int)$item['wid'] ?>">
                <div class="wish-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <?php else: echo $em; endif; ?>
                    <button class="wish-remove" onclick="removeFromWishlist(<?= (int)$item['wid'] ?>)" title="Remove from wishlist">✕</button>
                </div>
                <div class="wish-info">
                    <div class="wish-category"><?= htmlspecialchars($cat) ?></div>
                    <div class="wish-name"><a href="<?= SITE_URL ?>/product.php?id=<?= (int)$item['id'] ?>" style="color:inherit"><?= htmlspecialchars($item['name']) ?></a></div>
                    <div class="wish-stars"><?php $rv=round($item['rating']??4); for($i=1;$i<=5;$i++) echo $i<=$rv?'★':'☆'; ?></div>
                    <div class="wish-price">
                        <span class="price-current"><?= formatPrice($dp) ?></span>
                        <?php if ($op && $op>$dp): ?><span class="price-original"><?= formatPrice($op) ?></span><?php endif; ?>
                    </div>
                    <div class="wish-actions">
                        <button class="btn-wish-cart" onclick="addToCart(<?= (int)$item['id'] ?>,'<?= htmlspecialchars(addslashes($item['name'])) ?>',<?= (float)$dp ?>)">🛒 Add to Cart</button>
                        <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$item['id'] ?>"><button class="btn-wish-view">👁</button></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
function removeFromWishlist(wid) {
    if (!confirm('Remove from wishlist?')) return;
    fetch('<?= SITE_URL ?>/api/wishlist.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'remove', wid})
    }).then(r=>r.json()).then(d=>{
        if (d.success || !d.error) {
            const el = document.getElementById('wish-'+wid);
            if (el) el.remove();
        }
    }).catch(()=>{ document.getElementById('wish-'+wid)?.remove(); });
}

function addAllToCart() {
    const btns = document.querySelectorAll('.btn-wish-cart');
    btns.forEach(b => b.click());
    showToast('All items added to cart!');
}

if(typeof addToCart==='undefined'){
    function addToCart(id,name,price){
        fetch('<?= SITE_URL ?>/api/cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'add',product_id:id,name,price,quantity:1})})
        .then(r=>r.json()).then(d=>{const b=document.getElementById('cartBadge');if(b&&d.cart_count!==undefined)b.textContent=d.cart_count;showToast('✓ Added to cart!');}).catch(()=>showToast('✓ Added to cart!'));
    }
}
function showToast(msg){const t=document.createElement('div');t.textContent=msg;t.style.cssText='position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1e3a5f;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;z-index:9999';document.body.appendChild(t);setTimeout(()=>t.remove(),2500)}
</script>
</body>
</html>
