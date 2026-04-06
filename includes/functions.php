<?php
require_once __DIR__ . '/db.php';

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect(SITE_URL . '/admin/admin_login.php');
    }
}

function getProducts($limit = 12, $offset = 0, $category = null, $search = null) {
    $db = getDB();
    $where = "WHERE p.status = 'active'";
    if ($category) {
        $category = $db->real_escape_string($category);
        $where .= " AND c.slug = '$category'";
    }
    if ($search) {
        $search = $db->real_escape_string($search);
        $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getProduct($id) {
    $db = getDB();
    $id = (int)$id;
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = $id AND p.status = 'active'";
    $result = $db->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function getCategories() {
    $db = getDB();
    $result = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCartItems() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function getCartCount() {
    $cart = getCartItems();
    return array_sum(array_column($cart, 'quantity'));
}

function getCartTotal() {
    $cart = getCartItems();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function addToCart($product_id, $quantity = 1, $options = []) {
    $product = getProduct($product_id);
    if (!$product) return false;
    $key = $product_id . '_' . md5(serialize($options));
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['sale_price'] ?: $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'options' => $options
        ];
    }
    return true;
}

function formatPrice($price) {
    return CURRENCY . number_format($price, 2);
}

function getSetting($key) {
    $db = getDB();
    $key = $db->real_escape_string($key);
    $result = $db->query("SELECT value FROM settings WHERE `key` = '$key'");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['value'];
    }
    return null;
}

function getBanners($position = 'home') {
    $db = getDB();
    $position = $db->real_escape_string($position);
    $result = $db->query("SELECT * FROM banners WHERE position = '$position' AND status = 'active' ORDER BY sort_order ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getVideos($limit = 12, $offset = 0) {
    $db = getDB();
    $result = $db->query("SELECT v.*, c.name as channel_name FROM videos v LEFT JOIN channels c ON v.channel_id = c.id WHERE v.status = 'active' ORDER BY v.created_at DESC LIMIT $limit OFFSET $offset");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getTrendingProducts($limit = 4) {
    $db = getDB();
    $result = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND p.featured = 1 ORDER BY p.views DESC LIMIT $limit");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}
?>
