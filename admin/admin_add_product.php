<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();
$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? strtolower(str_replace(' ', '-', $name)));
    $price = (float)($_POST['price'] ?? 0);
    $sale_price = $_POST['sale_price'] ? (float)$_POST['sale_price'] : 'NULL';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = $db->real_escape_string($_POST['description'] ?? '');
    $short_description = sanitize($_POST['short_description'] ?? '');
    $specifications = $db->real_escape_string($_POST['specifications'] ?? '');
    $colors = sanitize($_POST['colors'] ?? '');
    $storage_options = sanitize($_POST['storage_options'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'active');
    $image = 'product-placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'product_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
    }
    $slug_esc = $db->real_escape_string($slug);
    $name_esc = $db->real_escape_string($name);
    $short_esc = $db->real_escape_string($short_description);
    $colors_esc = $db->real_escape_string($colors);
    $storage_esc = $db->real_escape_string($storage_options);
    $status_esc = $db->real_escape_string($status);
    $sale_val = is_numeric($sale_price) ? $sale_price : 'NULL';
    $sql = "INSERT INTO products (name, slug, price, sale_price, category_id, description, short_description, specifications, colors, storage_options, stock, featured, status, image)
            VALUES ('$name_esc', '$slug_esc', $price, $sale_val, $category_id, '$description', '$short_esc', '$specifications', '$colors_esc', '$storage_esc', $stock, $featured, '$status_esc', '$image')";
    if ($db->query($sql)) {
        header('Location: admin_products.php?msg=added');
        exit;
    } else {
        $message = 'Error: ' . $db->error;
    }
}
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product - GadgetZone Admin</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <div class="sidebar-brand">⚡ GadgetZone Admin</div>
    <nav class="sidebar-nav">
      <a href="admin_dashboard.php" class="sidebar-link">📊 Dashboard</a>
      <a href="admin_products.php" class="sidebar-link">📦 Products</a>
      <a href="admin_add_product.php" class="sidebar-link active">➕ Add Product</a>
      <a href="admin_categories.php" class="sidebar-link">📁 Categories</a>
      <a href="admin_orders.php" class="sidebar-link">🛒 Orders</a>
      <a href="admin_users.php" class="sidebar-link">👥 Users</a>
      <a href="admin_videos.php" class="sidebar-link">🎥 Videos</a>
      <a href="admin_channels.php" class="sidebar-link">📺 Channels</a>
      <a href="admin_coupons.php" class="sidebar-link">🎟️ Coupons</a>
      <a href="admin_banner.php" class="sidebar-link">🖼️ Banners</a>
      <a href="admin_settings.php" class="sidebar-link">⚙️ Settings</a>
      <a href="admin_payment.php" class="sidebar-link">💳 Payments</a>
      <a href="admin_logo.php" class="sidebar-link">🏷️ Logo</a>
      <a href="admin_logout.php" class="sidebar-link logout">🚪 Logout</a>
    </nav>
  </aside>
  <main class="admin-content">
    <header class="admin-header">
      <h1>Add New Product</h1>
      <a href="admin_products.php" class="btn btn-outline">← Back</a>
    </header>
    <?php if ($message): ?><div class="alert alert-danger"><?php echo $message; ?></div><?php endif; ?>
    <div class="admin-card">
      <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-row">
          <div class="form-group"><label>Product Name *</label><input type="text" name="name" class="admin-input" required></div>
          <div class="form-group"><label>Slug</label><input type="text" name="slug" class="admin-input" placeholder="auto-generated if empty"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Price (₦) *</label><input type="number" name="price" step="0.01" class="admin-input" required></div>
          <div class="form-group"><label>Sale Price (₦)</label><input type="number" name="sale_price" step="0.01" class="admin-input"></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="admin-input">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock" class="admin-input" value="0"></div>
        </div>
        <div class="form-group"><label>Short Description</label><input type="text" name="short_description" class="admin-input"></div>
        <div class="form-group"><label>Full Description</label><textarea name="description" class="admin-input" rows="4"></textarea></div>
        <div class="form-group"><label>Specifications (one per line)</label><textarea name="specifications" class="admin-input" rows="4" placeholder="6.1&quot; Display&#10;50MP Camera"></textarea></div>
        <div class="form-row">
          <div class="form-group"><label>Colors (comma separated)</label><input type="text" name="colors" class="admin-input" placeholder="Black, White, Gold"></div>
          <div class="form-group"><label>Storage Options (comma separated)</label><input type="text" name="storage_options" class="admin-input" placeholder="128GB, 256GB, 512GB"></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Status</label>
            <select name="status" class="admin-input">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
          <div class="form-group"><label>Product Image</label><input type="file" name="image" class="admin-input" accept="image/*"></div>
        </div>
        <div class="form-group"><label><input type="checkbox" name="featured"> Featured Product</label></div>
        <button type="submit" class="btn btn-primary">Add Product</button>
      </form>
    </div>
  </main>
</div>
<script src="../js/admin.js"></script>
</body>
</html>
