<?php
define("ADMIN_PAGE",true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Products | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout"><?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2>📦 Products</h2>
    <a href="product_form.php" class="btn-admin-primary"><i class="bi bi-plus"></i> Add Product</a>
  </div>
  <?php
  // Delete
  if (isset($_GET["delete"])) {
    $did = (int)$_GET["delete"];
    $conn->query("DELETE FROM products WHERE id=$did");
    setFlash("success","Product deleted."); header("Location: products.php"); exit;
  }
  $products = $conn->query("SELECT p.*,c.name AS cat FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC");
  ?>
  <?php showFlash(); ?>
  <div class="admin-card" style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Rating</th><th>Featured</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while($p=$products->fetch_assoc()): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td style="font-weight:600;"><?= clean($p['name']) ?></td>
          <td><?= clean($p['cat']) ?></td>
          <td><?= price($p['price']) ?></td>
          <td><?= $p['stock'] ?></td>
          <td><?= $p['rating'] ?> ★</td>
          <td><?= $p['is_featured']?"⭐":"—" ?></td>
          <td>
            <a href="product_form.php?id=<?= $p['id'] ?>" class="btn-admin-info" style="text-decoration:none;color:#fff;border-radius:6px;padding:5px 10px;font-size:.78rem;">Edit</a>
            <a href="products.php?delete=<?= $p['id'] ?>" class="btn-admin-danger ms-1" style="text-decoration:none;color:#fff;border-radius:6px;padding:5px 10px;font-size:.78rem;" onclick="return confirm('Delete this product?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body></html>
