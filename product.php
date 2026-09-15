<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$id = (int)($_GET["id"] ?? 0);
if (!$id) { header("Location: shop.php"); exit; }
$stmt = $conn->prepare("SELECT p.*,c.name AS cat_name,c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE p.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header("Location: shop.php"); exit; }
$pageTitle = $p["name"];
$flavours = json_decode($p["flavours"] ?? "[]", true);
$sizes    = json_decode($p["sizes"]    ?? "[]", true);
$related  = $conn->query("SELECT p.*,c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.category_id={$p['category_id']} AND p.id!=$id LIMIT 4");
// Handle add to cart
if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["add_to_cart"])) {
  $qty = max(1,(int)($_POST["qty"]??1));
  addToCart($id,$qty);
  setFlash("success","Added to cart successfully!");
  header("Location: product.php?id=$id");
  exit;
}
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero" style="padding:40px 0;">
  <div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item"><a href="shop.php?cat=<?= clean($p['cat_slug']) ?>"><?= clean($p['cat_name']) ?></a></li><li class="breadcrumb-item active"><?= clean($p['name']) ?></li></ol></nav></div>
</div>
<section class="section-pad">
  <div class="container">
    <div class="row g-5">
      <!-- Image -->
      <div class="col-md-5">
        <div class="product-img-wrap" style="height:380px;border-radius:16px;overflow:hidden;background:var(--cream);">
          <?= productImage($p["image"],$p["name"]) ?>
        </div>
      </div>
      <!-- Details -->
      <div class="col-md-7">
        <div class="product-cat"><?= clean($p["cat_name"]) ?></div>
        <h1 class="section-title" style="font-size:2rem;margin:.3rem 0;"><?= clean($p["name"]) ?></h1>
        <div class="mb-2"><?= renderStars($p["rating"]) ?><span class="rating-count"><?= $p["rating"] ?>/5 (<?= $p["reviews_count"] ?> reviews)</span></div>
        <div class="product-price" style="font-size:1.8rem;margin:.5rem 0;"><?= price($p["price"]) ?></div>
        <p style="color:#7a5a4a;line-height:1.8;margin-bottom:1.5rem;"><?= clean($p["description"]) ?></p>
        <form method="POST">
          <?php if($flavours): ?>
          <div class="mb-3">
            <label class="form-label fw-bold">Choose Flavour</label>
            <select name="flavour" class="form-select" id="flavour-select" style="border:1.5px solid #e8d5c8;border-radius:8px;padding:10px;">
              <?php foreach($flavours as $f): ?><option><?= clean($f) ?></option><?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <?php if($sizes): ?>
          <div class="mb-3">
            <label class="form-label fw-bold">Choose Size</label>
            <select name="size" class="form-select" id="size-select" style="border:1.5px solid #e8d5c8;border-radius:8px;padding:10px;">
              <?php foreach($sizes as $s): ?><option><?= clean($s) ?></option><?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="mb-4">
            <label class="form-label fw-bold">Quantity</label>
            <div class="qty-control">
              <button type="button" class="qty-btn" data-dir="down" onclick="adjQty(-1)" id="qty-down">−</button>
              <input type="number" name="qty" id="qty-field" class="qty-input" value="1" min="1" max="99" style="width:60px;">
              <button type="button" class="qty-btn" data-dir="up" onclick="adjQty(1)" id="qty-up">+</button>
            </div>
          </div>
          <div class="d-flex gap-3 flex-wrap">
            <button type="submit" name="add_to_cart" class="btn-primary-sm" id="add-cart-btn">🛒 Add to Cart</button>
            <a href="cart.php" class="btn-outline-brown" id="go-cart-btn">View Cart</a>
          </div>
        </form>
        <div class="mt-4 d-flex gap-4">
          <div style="font-size:.85rem;color:#7a5a4a">✅ Fresh ingredients</div>
          <div style="font-size:.85rem;color:#7a5a4a">🚚 Fast delivery</div>
          <div style="font-size:.85rem;color:#7a5a4a">🎁 Gift wrapping available</div>
        </div>
      </div>
    </div>
    <!-- Related -->
    <?php if($related->num_rows > 0): ?>
    <div class="mt-5 pt-4">
      <h3 style="margin-bottom:1.5rem;">You May Also Like</h3>
      <div class="row g-4">
        <?php while($r=$related->fetch_assoc()): ?>
        <div class="col-xl-3 col-md-6">
          <div class="product-card">
            <div class="product-img-wrap" style="height:180px;"><?= productImage($r["image"],$r["name"]) ?></div>
            <div class="product-body">
              <div class="product-name" style="font-size:1rem;"><?= clean($r["name"]) ?></div>
              <div class="product-price"><?= price($r["price"]) ?></div>
              <div class="product-actions">
                <button class="btn-add-cart" data-id="<?= $r["id"] ?>" id="rel-<?= $r["id"] ?>">🛒 Add</button>
                <a href="product.php?id=<?= $r["id"] ?>" class="btn-view">View</a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<script>
function adjQty(d){const f=document.getElementById("qty-field");f.value=Math.max(1,Math.min(99,parseInt(f.value||1)+d));}
</script>
<?php require __DIR__ . "/includes/footer.php"; ?>
