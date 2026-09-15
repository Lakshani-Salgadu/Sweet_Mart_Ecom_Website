<?php
define("ADMIN_PAGE", true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();

$pid  = (int)($_GET["id"] ?? 0);
$p    = $pid ? $conn->query("SELECT * FROM products WHERE id=$pid")->fetch_assoc() : [];
$cats = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST["name"] ?? "");
    $desc  = trim($_POST["description"] ?? "");
    $price = (float)($_POST["price"] ?? 0);
    $cid   = (int)($_POST["category_id"] ?? 1);
    $img   = trim($_POST["image"] ?? "placeholder.jpg");
    $flav  = trim($_POST["flavours"] ?? "[]");
    $sz    = trim($_POST["sizes"] ?? "[]");
    $rat   = (float)($_POST["rating"] ?? 4.5);
    $rc    = (int)($_POST["reviews_count"] ?? 0);
    $stock = (int)($_POST["stock"] ?? 50);
    $feat  = isset($_POST["is_featured"]) ? 1 : 0;

    if ($pid) {
        $stmt = $conn->prepare("UPDATE products SET name=?,description=?,price=?,category_id=?,image=?,flavours=?,sizes=?,rating=?,reviews_count=?,stock=?,is_featured=? WHERE id=?");
        $stmt->bind_param("ssdisssdiiid", $name, $desc, $price, $cid, $img, $flav, $sz, $rat, $rc, $stock, $feat, $pid);
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name,description,price,category_id,image,flavours,sizes,rating,reviews_count,stock,is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssisssdiii", $name, $desc, $price, $cid, $img, $flav, $sz, $rat, $rc, $stock, $feat);
    }
    $stmt->execute();
    setFlash("success", $pid ? "Product updated!" : "Product added!");
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pid ? "Edit" : "Add" ?> Product | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
<?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2><?= $pid ? "Edit Product" : "Add Product" ?></h2>
    <a href="products.php" class="btn-admin-primary">← Back</a>
  </div>
  <?php showFlash(); ?>
  <div class="admin-card" style="max-width:700px;">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-bold" style="font-size:.85rem;">Product Name *</label>
          <input name="name" class="form-control-admin" required value="<?= clean($p["name"] ?? "") ?>" id="prod-name">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold" style="font-size:.85rem;">Category *</label>
          <select name="category_id" class="form-control-admin" id="prod-cat">
            <?php while($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c["id"] ?>" <?= ($p["category_id"] ?? "") == $c["id"] ? "selected" : "" ?>><?= clean($c["name"]) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-bold" style="font-size:.85rem;">Description</label>
          <textarea name="description" class="form-control-admin" rows="3" id="prod-desc"><?= clean($p["description"] ?? "") ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold" style="font-size:.85rem;">Price (LKR) *</label>
          <input name="price" type="number" step="0.01" class="form-control-admin" required value="<?= $p["price"] ?? 0 ?>" id="prod-price">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold" style="font-size:.85rem;">Stock</label>
          <input name="stock" type="number" class="form-control-admin" value="<?= $p["stock"] ?? 50 ?>" id="prod-stock">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold" style="font-size:.85rem;">Rating (1–5)</label>
          <input name="rating" type="number" step="0.1" min="1" max="5" class="form-control-admin" value="<?= $p["rating"] ?? 4.5 ?>" id="prod-rating">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold" style="font-size:.85rem;">Image filename</label>
          <input name="image" class="form-control-admin" value="<?= clean($p["image"] ?? "placeholder.jpg") ?>" id="prod-image">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold" style="font-size:.85rem;">Reviews Count</label>
          <input name="reviews_count" type="number" class="form-control-admin" value="<?= $p["reviews_count"] ?? 0 ?>" id="prod-reviews">
        </div>
        <div class="col-12">
          <label class="form-label fw-bold" style="font-size:.85rem;">Flavours (JSON array)</label>
          <input name="flavours" class="form-control-admin" value="<?= htmlspecialchars($p["flavours"] ?? '[]') ?>" id="prod-flavours" placeholder='["Chocolate","Vanilla"]'>
        </div>
        <div class="col-12">
          <label class="form-label fw-bold" style="font-size:.85rem;">Sizes (JSON array)</label>
          <input name="sizes" class="form-control-admin" value="<?= htmlspecialchars($p["sizes"] ?? '[]') ?>" id="prod-sizes" placeholder='["1 kg","2 kg"]'>
        </div>
        <div class="col-12">
          <div style="display:flex;align-items:center;gap:10px;">
            <input name="is_featured" type="checkbox" id="prod-featured" <?= ($p["is_featured"] ?? 0) ? "checked" : "" ?>>
            <label for="prod-featured" style="font-weight:600;font-size:.88rem;">Mark as Best Seller / Featured</label>
          </div>
        </div>
        <div class="col-12 mt-2">
          <button type="submit" class="btn-admin-primary" style="padding:12px 32px;" id="save-product-btn">
            <?= $pid ? "Update Product" : "Add Product" ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
