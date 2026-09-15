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
<title>Categories | Sweet Mart Admin</title>
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
    <h2>🏷️ Categories</h2>
  </div>
  <?php
  if ($_SERVER["REQUEST_METHOD"]==="POST") {
    if (isset($_POST["add_cat"])) {
      $n = trim($_POST["name"]??""); $s = trim($_POST["slug"]??strtolower($n)); $i = trim($_POST["icon"]??"🍰");
      if ($n) { $stmt=$conn->prepare("INSERT INTO categories (name,slug,icon) VALUES (?,?,?)"); $stmt->bind_param("sss",$n,$s,$i); $stmt->execute(); setFlash("success","Category added."); }
    } elseif (isset($_POST["delete_cat"])) {
      $did=(int)$_POST["delete_cat"];
      $conn->query("DELETE FROM categories WHERE id=$did"); setFlash("success","Category deleted.");
    }
    header("Location: categories.php"); exit;
  }
  $cats = $conn->query("SELECT c.*,(SELECT COUNT(*) FROM products WHERE category_id=c.id) AS prod_count FROM categories c ORDER BY c.id");
  ?>
  <?php showFlash(); ?>
  <div class="row g-4">
    <div class="col-md-5">
      <div class="admin-card">
        <h5 class="mb-3">Add Category</h5>
        <form method="POST">
          <div class="mb-3"><label class="form-label fw-bold" style="font-size:.85rem;">Name *</label><input name="name" class="form-control-admin" required id="cat-name"></div>
          <div class="mb-3"><label class="form-label fw-bold" style="font-size:.85rem;">Slug</label><input name="slug" class="form-control-admin" placeholder="auto-generated if blank" id="cat-slug"></div>
          <div class="mb-3"><label class="form-label fw-bold" style="font-size:.85rem;">Icon (emoji)</label><input name="icon" class="form-control-admin" value="🍰" id="cat-icon"></div>
          <button type="submit" name="add_cat" class="btn-admin-primary">Add Category</button>
        </form>
      </div>
    </div>
    <div class="col-md-7">
      <div class="admin-card" style="overflow-x:auto;">
        <table class="admin-table">
          <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Products</th><th>Delete</th></tr></thead>
          <tbody>
            <?php while($c=$cats->fetch_assoc()): ?>
            <tr>
              <td style="font-size:1.3rem;"><?= $c['icon'] ?></td>
              <td style="font-weight:600;"><?= clean($c['name']) ?></td>
              <td style="font-size:.82rem;color:#8a6a5a;"><?= clean($c['slug']) ?></td>
              <td><?= $c['prod_count'] ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <button name="delete_cat" value="<?= $c['id'] ?>" class="btn-admin-danger" onclick="return confirm('Delete category?')">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
