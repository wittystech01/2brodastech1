<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$channel_id = (int)($_GET['id'] ?? 1);
$channel    = null;
$ch_videos  = [];
$tab        = $_GET['tab'] ?? 'videos';

try {
    $db   = getDB();
    $res  = $db->query("SELECT * FROM channels WHERE id=$channel_id AND status='active' LIMIT 1");
    $channel = $res ? $res->fetch_assoc() : null;
    if ($channel) {
        $vres   = $db->query("SELECT * FROM videos WHERE channel_id=$channel_id AND status='active' ORDER BY created_at DESC LIMIT 12");
        $ch_videos = $vres ? $vres->fetch_all(MYSQLI_ASSOC) : [];
    }
} catch (Exception $e) { $channel = null; }

if (!$channel) {
    $channel = [
        'id'            => 1,
        'name'          => 'GadgetZone Reviews',
        'logo'          => '',
        'banner'        => '',
        'description'   => 'Your go-to channel for honest tech reviews, unboxings, and the latest gadget news. We cover smartphones, tablets, accessories, and everything in between.',
        'subscriber_count' => 245000,
        'video_count'   => 128,
        'country'       => 'Nigeria',
        'created_at'    => '2020-01-01',
    ];
    $ch_videos = [
        ['id'=>1,'title'=>'iPhone 15 Pro Max Full Review 2024','views'=>245000,'duration'=>'12:45','category'=>'Reviews','thumbnail_url'=>'','created_at'=>'2024-01-15'],
        ['id'=>3,'title'=>'Best Wireless Earbuds Under ₦50,000','views'=>127000,'duration'=>'15:20','category'=>'Reviews','thumbnail_url'=>'','created_at'=>'2024-01-10'],
        ['id'=>7,'title'=>'Google Pixel 8 Pro Camera Test','views'=>143000,'duration'=>'22:15','category'=>'Reviews','thumbnail_url'=>'','created_at'=>'2024-01-05'],
        ['id'=>9,'title'=>'Top 5 Gaming Gadgets 2024','views'=>205000,'duration'=>'14:30','category'=>'Reviews','thumbnail_url'=>'','created_at'=>'2023-12-28'],
    ];
}

$is_subscribed = false;
$thumb_colors  = ['#1e3a5f','#2d5f8f','#ff6b35','#8b5cf6','#0ea5e9','#14b8a6'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($channel['name']) ?> — GadgetZone</title>
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
        .channel-banner{height:220px;background:linear-gradient(135deg,#1e3a5f 0%,#2d5f8f 100%);position:relative;overflow:hidden}
        .channel-banner img{width:100%;height:100%;object-fit:cover}
        .channel-header{background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.06)}
        .channel-header .container{display:flex;align-items:center;gap:20px;padding-top:0;padding-bottom:20px;flex-wrap:wrap}
        .channel-logo-wrap{margin-top:-40px;flex-shrink:0}
        .channel-logo{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#ff6b35,#1e3a5f);display:flex;align-items:center;justify-content:center;font-size:2rem;border:4px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,.15)}
        .channel-logo img{width:100%;height:100%;object-fit:cover;border-radius:50%}
        .channel-meta{flex:1;padding-top:12px}
        .channel-name{font-size:1.4rem;font-weight:800;color:#1e3a5f;margin-bottom:6px}
        .channel-stats{display:flex;gap:20px;font-size:.85rem;color:#666;flex-wrap:wrap}
        .channel-stat{display:flex;align-items:center;gap:4px}
        .channel-actions{display:flex;gap:12px;padding-top:12px;flex-wrap:wrap}
        .btn-subscribe{background:#ff6b35;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:700;cursor:pointer;font-size:.9rem;transition:all .3s}
        .btn-subscribe:hover{background:#e55a25}
        .btn-subscribe.subscribed{background:#e8f0fe;color:#1e3a5f}
        .btn-bell{background:#f8f9fa;border:2px solid #eee;padding:10px 14px;border-radius:8px;cursor:pointer;font-size:1rem;transition:all .2s}
        .btn-bell:hover{border-color:#1e3a5f}
        .channel-page{padding:32px 0}
        .channel-tabs{display:flex;gap:0;border-bottom:2px solid #eee;margin-bottom:28px;background:#fff;border-radius:10px 10px 0 0;overflow:hidden}
        .channel-tab{padding:14px 28px;border:none;background:transparent;font-size:.95rem;font-weight:600;cursor:pointer;color:#888;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s}
        .channel-tab.active,.channel-tab:hover{color:#1e3a5f;border-bottom-color:#ff6b35;background:#f0f4ff}
        .videos-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .video-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);cursor:pointer;transition:transform .3s}
        .video-card:hover{transform:translateY(-3px)}
        .video-thumb{height:160px;position:relative;display:flex;align-items:center;justify-content:center}
        .play-btn{width:44px;height:44px;background:rgba(255,107,53,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff}
        .video-duration{position:absolute;bottom:7px;right:7px;background:rgba(0,0,0,.75);color:#fff;padding:2px 7px;border-radius:4px;font-size:.7rem}
        .video-info{padding:12px}
        .video-title{font-weight:600;font-size:.88rem;color:#333;margin-bottom:5px;line-height:1.4}
        .video-meta{font-size:.77rem;color:#888;display:flex;justify-content:space-between}
        .about-section{background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        .about-section h3{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:16px}
        .about-section p{color:#555;line-height:1.7;margin-bottom:16px}
        .about-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:20px}
        .about-stat{background:#f8f9fa;border-radius:10px;padding:16px;text-align:center}
        .about-stat-val{font-size:1.5rem;font-weight:800;color:#1e3a5f}
        .about-stat-label{font-size:.8rem;color:#888;margin-top:4px}
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
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}.videos-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.channel-header .container{flex-direction:column;align-items:flex-start}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.videos-grid{grid-template-columns:1fr}.about-stats{grid-template-columns:1fr}.footer-grid{grid-template-columns:1fr}}
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
        <a href="<?= SITE_URL ?>/video.php">🎥 Videos</a><a href="<?= SITE_URL ?>/blog.php">📝 Blog</a>
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Channel Banner -->
<div class="channel-banner">
    <?php if (!empty($channel['banner'])): ?>
        <img src="<?= htmlspecialchars($channel['banner']) ?>" alt="Channel Banner">
    <?php endif; ?>
</div>

<!-- Channel Header -->
<div class="channel-header">
    <div class="container">
        <div class="channel-logo-wrap">
            <div class="channel-logo">
                <?php if (!empty($channel['logo'])): ?>
                    <img src="<?= htmlspecialchars($channel['logo']) ?>" alt="<?= htmlspecialchars($channel['name']) ?>">
                <?php else: echo '📺'; endif; ?>
            </div>
        </div>
        <div class="channel-meta">
            <div class="channel-name"><?= htmlspecialchars($channel['name']) ?></div>
            <div class="channel-stats">
                <div class="channel-stat">👥 <?= number_format($channel['subscriber_count'] ?? 0) ?> subscribers</div>
                <div class="channel-stat">🎬 <?= $channel['video_count'] ?? count($ch_videos) ?> videos</div>
                <?php if (!empty($channel['country'])): ?>
                <div class="channel-stat">🌍 <?= htmlspecialchars($channel['country']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="channel-actions">
            <button class="btn-subscribe <?= $is_subscribed?'subscribed':'' ?>" id="subBtn" onclick="toggleSubscribe()">
                <?= $is_subscribed ? '✓ Subscribed' : '🔔 Subscribe' ?>
            </button>
            <button class="btn-bell" onclick="alert('Notification settings coming soon!')" title="Notifications">🔔</button>
        </div>
    </div>
</div>

<!-- Channel Content -->
<div class="channel-page">
    <div class="container">
        <div class="channel-tabs">
            <button class="channel-tab <?= $tab==='videos'?'active':'' ?>" onclick="switchTab('videos')">🎬 Videos (<?= count($ch_videos) ?>)</button>
            <button class="channel-tab <?= $tab==='about'?'active':'' ?>" onclick="switchTab('about')">ℹ️ About</button>
        </div>

        <!-- Videos Tab -->
        <div id="tab-videos" class="<?= $tab!=='about'?'':'hidden' ?>">
            <?php if (empty($ch_videos)): ?>
            <div style="text-align:center;padding:60px;background:#fff;border-radius:12px">
                <div style="font-size:4rem;margin-bottom:12px">🎬</div>
                <p style="color:#888">No videos found for this channel.</p>
            </div>
            <?php else: ?>
            <div class="videos-grid">
                <?php foreach ($ch_videos as $i => $v):
                    $bg = $thumb_colors[$i % count($thumb_colors)];
                ?>
                <div class="video-card">
                    <div class="video-thumb" style="background:<?= $bg ?>">
                        <?php if (!empty($v['thumbnail_url'])): ?>
                            <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                        <?php endif; ?>
                        <div class="play-btn">▶</div>
                        <span class="video-duration"><?= htmlspecialchars($v['duration'] ?? '0:00') ?></span>
                    </div>
                    <div class="video-info">
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                        <div class="video-meta">
                            <span><?= date('M j, Y', strtotime($v['created_at']??'now')) ?></span>
                            <span>👁 <?= number_format($v['views'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- About Tab -->
        <div id="tab-about" style="display:<?= $tab==='about'?'block':'none' ?>">
            <div class="about-section">
                <h3>About <?= htmlspecialchars($channel['name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($channel['description'] ?? '')) ?></p>
                <?php if (!empty($channel['created_at'])): ?>
                <p style="font-size:.85rem;color:#888">📅 Channel created: <?= date('F Y', strtotime($channel['created_at'])) ?></p>
                <?php endif; ?>
                <div class="about-stats">
                    <div class="about-stat">
                        <div class="about-stat-val"><?= number_format($channel['subscriber_count'] ?? 0) ?></div>
                        <div class="about-stat-label">Subscribers</div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-val"><?= $channel['video_count'] ?? count($ch_videos) ?></div>
                        <div class="about-stat-label">Videos</div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-val"><?= number_format(array_sum(array_column($ch_videos, 'views'))) ?></div>
                        <div class="about-stat-label">Total Views</div>
                    </div>
                </div>
            </div>
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
<script>
let subscribed = <?= $is_subscribed ? 'true' : 'false' ?>;
function toggleSubscribe() {
    subscribed = !subscribed;
    const btn = document.getElementById('subBtn');
    btn.textContent = subscribed ? '✓ Subscribed' : '🔔 Subscribe';
    btn.classList.toggle('subscribed', subscribed);
    fetch('<?= SITE_URL ?>/api/subscribe.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action: subscribed?'subscribe':'unsubscribe', channel_id: <?= $channel_id ?>})
    }).catch(()=>{});
}
function switchTab(t) {
    document.getElementById('tab-videos').style.display = t==='videos' ? 'block' : 'none';
    document.getElementById('tab-about').style.display  = t==='about'  ? 'block' : 'none';
    document.querySelectorAll('.channel-tab').forEach((b,i)=>b.classList.toggle('active', (i===0&&t==='videos')||(i===1&&t==='about')));
}
</script>
</body>
</html>
