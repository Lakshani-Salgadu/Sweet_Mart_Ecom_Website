<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Home";
// Best sellers
$featured = $conn->query("SELECT p.*,c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 ORDER BY p.rating DESC LIMIT 8");
// Categories
$cats = $conn->query("SELECT * FROM categories ORDER BY id");
// Special offer product
$special = $conn->query("SELECT p.*,c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.category_id=6 ORDER BY p.price DESC LIMIT 1")->fetch_assoc();
require __DIR__ . "/includes/header.php";
?>

<!-- HERO -->
<section class="sm-hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge">🎉 Free Delivery on Orders Over LKR 5,000</div>
        <h1 class="hero-title">A Little <span>Sweetness</span>,<br>Just for You.</h1>
        <p class="hero-desc">Sri Lanka's favourite online dessert store. Freshly baked cakes, cupcakes, brownies, cookies and custom gift boxes — delivered with love to your door.</p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="shop.php" class="btn-primary-sm" id="hero-shop-btn">Shop Now</a>
          <a href="custom_box.php" class="btn-outline-brown" id="hero-box-btn">🎁 Create Your Box</a>
        </div>
        <div class="mt-4 d-flex gap-4">
          <div><div style="font-size:1.5rem;font-weight:700;color:var(--brown-dark)">500+</div><div style="font-size:.8rem;color:#8a6a5a">Happy Customers</div></div>
          <div><div style="font-size:1.5rem;font-weight:700;color:var(--brown-dark)">20+</div><div style="font-size:.8rem;color:#8a6a5a">Dessert Varieties</div></div>
          <div><div style="font-size:1.5rem;font-weight:700;color:var(--brown-dark)">4.9★</div><div style="font-size:.8rem;color:#8a6a5a">Average Rating</div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-img-wrap">
          <div class="hero-float-1">🎂 Fresh Daily</div>
          <img src="assets/images/hero.png" alt="Sweet Mart Desserts" style="border-radius:24px;width:100%;">
          <div class="hero-float-2">🚚 Fast Delivery</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="section-pad categories-section">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">🍰 Browse By Category</div>
      <h2 class="section-title">What Are You Craving?</h2>
    </div>
    <div class="cat-grid">
      <?php while($c=$cats->fetch_assoc()): ?>
      <a href="shop.php?cat=<?= urlencode($c['slug']) ?>" class="cat-card animate-on-scroll" id="cat-<?= $c['slug'] ?>">
        <span class="cat-icon"><?= $c['icon'] ?></span>
        <h4><?= clean($c['name']) ?></h4>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- BEST SELLERS -->
<section class="section-pad" style="background:#fff;">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">⭐ Most Loved</div>
      <h2 class="section-title">Best Sellers</h2>
      <p class="section-sub">Our customers can't get enough of these treats</p>
    </div>
    <div class="row g-4">
      <?php while($p=$featured->fetch_assoc()): ?>
      <div class="col-xl-3 col-lg-4 col-md-6 animate-on-scroll">
        <div class="product-card">
          <div class="product-img-wrap">
            <span class="product-badge">Best Seller</span>
            <?= productImage($p['image'],$p['name']) ?>
          </div>
          <div class="product-body">
            <div class="product-cat"><?= clean($p['cat_name']) ?></div>
            <div class="product-name"><?= clean($p['name']) ?></div>
            <div><?= renderStars($p['rating']) ?><span class="rating-count">(<?= $p['reviews_count'] ?>)</span></div>
            <div class="product-price"><?= price($p['price']) ?></div>
            <div class="product-actions">
              <button class="btn-add-cart" data-id="<?= $p['id'] ?>" id="add-<?= $p['id'] ?>">🛒 Add to Cart</button>
              <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" id="view-<?= $p['id'] ?>">View</a>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-5"><a href="shop.php" class="btn-primary-sm">View All Products</a></div>
  </div>
</section>

<!-- SPECIAL OFFER -->
<?php if($special): ?>
<section class="section-pad" style="background:linear-gradient(135deg,#fff5ef,#fce8e8);">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-6">
        <div class="section-label">🎁 Special Offer</div>
        <h2 class="section-title" style="font-size:2.4rem;">Gift Someone You Love</h2>
        <p style="color:#7a5a4a;font-size:1.05rem;margin-bottom:1.5rem;">Our luxury gift boxes make the perfect surprise for any occasion — birthdays, anniversaries, graduations, and more. Beautifully packed and delivered with care.</p>
        <div style="font-size:2rem;font-weight:700;color:var(--brown-dark);margin-bottom:1.5rem;"><?= price($special['price']) ?></div>
        <div class="d-flex gap-3">
          <a href="product.php?id=<?= $special['id'] ?>" class="btn-primary-sm" id="offer-view-btn">View Details</a>
          <button class="btn-outline-brown btn-add-cart" data-id="<?= $special['id'] ?>" id="offer-cart-btn">Add to Cart</button>
        </div>
      </div>
      <div class="col-md-6 text-center">
        <div style="font-size:8rem;">🎁</div>
        <div style="font-size:1.1rem;font-weight:600;color:var(--brown-dark);margin-top:.5rem;"><?= clean($special['name']) ?></div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CUSTOM BOX CTA -->
<section class="section-pad" style="background:var(--brown-dark);color:#fff;">
  <div class="container text-center">
    <div style="font-size:3.5rem;margin-bottom:1rem;">🎨</div>
    <h2 style="color:#fff;font-size:2.4rem;margin-bottom:1rem;">Build Your Own Sweet Box</h2>
    <p style="color:#f0d0c0;max-width:560px;margin:0 auto 2rem;font-size:1.05rem;">Choose your desserts, pick an occasion, add a personal message — and we will deliver a one-of-a-kind sweet gift box made just for you.</p>
    <a href="custom_box.php" class="btn-primary-sm" style="background:var(--pink);border-color:var(--pink);" id="cta-box-btn">Start Creating 🎁</a>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section-pad" style="background:#fff;">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">💬 Reviews</div>
      <h2 class="section-title">What Our Customers Say</h2>
    </div>
    <div class="row g-4">
      <?php
      $reviews=[
        ["Amaya Silva","★★★★★","The Birthday Sweet Box was absolutely gorgeous! My sister was in tears. The brownies and cupcakes tasted incredible. Will definitely order again!","Colombo"],
        ["Kavindu Perera","★★★★★","Ordered the Chocolate Fudge Cake for my anniversary — it arrived on time, beautifully packaged, and tasted divine. 10/10 from me!","Kandy"],
        ["Dilini Fernando","★★★★☆","Love the custom box feature! I was able to personalise everything for my best friend's graduation. The message card was a lovely touch.","Galle"],
      ];
      foreach($reviews as $r): ?>
      <div class="col-md-4 animate-on-scroll">
        <div class="testimonial-card">
          <div style="color:#f4a515;font-size:1.1rem;margin-bottom:.8rem;"><?= $r[1] ?></div>
          <p>"<?= $r[2] ?>"</p>
          <div class="d-flex align-items-center gap-3">
            <div class="testimonial-avatar">😊</div>
            <div><div style="font-weight:600;font-size:.9rem"><?= $r[0] ?></div><div style="font-size:.78rem;color:#8a6a5a"><?= $r[3] ?></div></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="section-pad">
  <div class="container">
    <div class="newsletter-section p-5 text-center">
      <div style="font-size:2.5rem;margin-bottom:.8rem;">📬</div>
      <h2 style="font-size:2rem;margin-bottom:.5rem;">Get Sweet Deals in Your Inbox</h2>
      <p>Subscribe to our newsletter for exclusive offers, new arrivals and baking inspiration.</p>
      <div class="d-flex justify-content-center mt-4">
        <input class="newsletter-input" type="email" placeholder="Enter your email address" style="max-width:340px;">
        <button class="newsletter-btn" id="newsletter-btn">Subscribe</button>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . "/includes/footer.php"; ?>
