<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Shop";
// Filters
$catSlug = $_GET["cat"] ?? "";
$search  = trim($_GET["q"] ?? "");
$sort    = $_GET["sort"] ?? "featured";
$minP    = (float)($_GET["min"] ?? 0);
$maxP    = (float)($_GET["max"] ?? 99999);

$where = ["p.stock > 0"];
$params = [];
$types  = "";
if ($catSlug) { $where[] = "c.slug = ?"; $params[] = $catSlug; $types .= "s"; }
if ($search)  { $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= "ss"; }
if ($minP>0)  { $where[] = "p.price >= ?"; $params[] = $minP; $types .= "d"; }
if ($maxP<99999) { $where[] = "p.price <= ?"; $params[] = $maxP; $types .= "d"; }
$orderBy = match($sort) {
  "price_asc"  => "p.price ASC",
  "price_desc" => "p.price DESC",
  "rating"     => "p.rating DESC",
  "newest"     => "p.created_at DESC",
  default      => "p.is_featured DESC, p.rating DESC"
};
$sql = "SELECT p.*,c.name AS cat_name,c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE " . implode(" AND ", $where) . " ORDER BY $orderBy";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();
$cats = $conn->query("SELECT * FROM categories ORDER BY name");
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero">
  <div class="container">
    <h1 class="section-title">Our Sweet Collection</h1>
    <p class="section-sub">Browse our full range of freshly baked desserts and gift boxes</p>
  </div>
</div>

<section class="section-pad">
  <div class="container">
    <div class="row g-4">
      <!-- Sidebar Filters -->
      <div class="col-lg-3">
        <div class="cart-summary-box mb-3">
          <h5 style="margin-bottom:1rem;">🔍 Search</h5>
          <form method="GET" action="shop.php">
            <input name="q" class="form-control mb-2" placeholder="Search desserts..." value="<?= clean($search) ?>">
            <input type="hidden" name="cat" value="<?= clean($catSlug) ?>">
            <button class="btn-primary-sm w-100" style="padding:10px;" type="submit">Search</button>
          </form>
        </div>
        <div class="cart-summary-box mb-3">
          <h5 style="margin-bottom:1rem;">🏷️ Categories</h5>
          <a href="shop.php" class="d-block py-1 px-2 rounded mb-1 <?= !$catSlug?'fw-bold':'' ?>" style="color:var(--brown-dark)">All Products</a>
          <?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?>
          <a href="shop.php?cat=<?= urlencode($c['slug']) ?>" class="d-block py-1 px-2 rounded mb-1 <?= $catSlug===$c['slug']?'fw-bold':'' ?>" style="color:var(--brown-dark)"><?= $c['icon'].' '.clean($c['name']) ?></a>
          <?php endwhile; ?>
        </div>
        <div class="cart-summary-box">
          <h5 style="margin-bottom:1rem;">💰 Price Range</h5>
          <form method="GET" action="shop.php">
            <input type="hidden" name="cat" value="<?= clean($catSlug) ?>">
            <input type="hidden" name="q" value="<?= clean($search) ?>">
            <label class="form-label">Min (LKR)</label>
            <input name="min" type="number" class="form-control mb-2" value="<?= $minP?:'' ?>" placeholder="0">
            <label class="form-label">Max (LKR)</label>
            <input name="max" type="number" class="form-control mb-2" value="<?= $maxP<99999?$maxP:'' ?>" placeholder="Any">
            <button class="btn-primary-sm w-100" style="padding:10px;" type="submit">Apply</button>
          </form>
        </div>
      </div>
      <!-- Products Grid -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div style="color:#8a6a5a;font-size:.88rem;"><?= $products->num_rows ?> products found</div>
          <form method="GET" action="shop.php">
            <input type="hidden" name="cat" value="<?= clean($catSlug) ?>">
            <input type="hidden" name="q" value="<?= clean($search) ?>">
            <select name="sort" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()" id="sort-select">
              <option value="featured" <?= $sort==="featured"?"selected":"" ?>>Featured</option>
              <option value="rating" <?= $sort==="rating"?"selected":"" ?>>Top Rated</option>
              <option value="price_asc" <?= $sort==="price_asc"?"selected":"" ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort==="price_desc"?"selected":"" ?>>Price: High to Low</option>
              <option value="newest" <?= $sort==="newest"?"selected":"" ?>>Newest</option>
            </select>
          </form>
        </div>
        <?php if($products->num_rows===0): ?>
        <div class="text-center py-5"><div style="font-size:4rem">🔍</div><h3 style="margin-top:1rem">No products found</h3><p style="color:#8a6a5a">Try a different search or category.</p><a href="shop.php" class="btn-primary-sm mt-3">Browse All</a></div>
        <?php else: ?>
        <div class="row g-4">
          <?php while($p=$products->fetch_assoc()): ?>
          <div class="col-xl-4 col-md-6">
            <div class="product-card">
              <div class="product-img-wrap">
                <?php if($p['is_featured']): ?><span class="product-badge">⭐ Best Seller</span><?php endif; ?>
                <?= productImage($p['image'],$p['name']) ?>
              </div>
              <div class="product-body">
                <div class="product-cat"><?= clean($p['cat_name']) ?></div>
                <div class="product-name"><?= clean($p['name']) ?></div>
                <div><?= renderStars($p['rating']) ?><span class="rating-count">(<?= $p['reviews_count'] ?>)</span></div>
                <div class="product-price"><?= price($p['price']) ?></div>
                <div class="product-actions">
                  <button class="btn-add-cart" data-id="<?= $p['id'] ?>" id="cart-<?= $p['id'] ?>">🛒 Add to Cart</button>
                  <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" id="view-<?= $p['id'] ?>">View</a>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
