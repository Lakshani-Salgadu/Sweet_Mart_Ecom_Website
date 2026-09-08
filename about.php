<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "About Us";
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero text-center"><div class="container"><h1 class="section-title">About Sweet Mart</h1><p class="section-sub">Our story, our passion, our promise to you</p></div></div>
<section class="section-pad">
  <div class="container">
    <div class="row align-items-center g-5 mb-5">
      <div class="col-md-6">
        <div class="section-label">🍰 Our Story</div>
        <h2 class="section-title">Born from a Love of Baking</h2>
        <p style="color:#7a5a4a;line-height:1.9;margin-bottom:1rem;">Sweet Mart began in 2021 in a small home kitchen in Colombo, Sri Lanka. What started as a hobby — baking cakes for friends and family — quickly grew into something much bigger as word spread about our irresistibly delicious creations.</p>
        <p style="color:#7a5a4a;line-height:1.9;">Today, Sweet Mart is one of Sri Lanka's most loved online dessert stores, delivering hundreds of orders each month across Colombo and beyond. Every item is still baked with the same love and care as that very first cake.</p>
      </div>
      <div class="col-md-6 text-center"><div style="font-size:8rem;">🧁</div></div>
    </div>
    <!-- Why Choose Us -->
    <div class="text-center mb-5"><div class="section-label">⭐ Why Sweet Mart</div><h2 class="section-title">Why Customers Choose Us</h2></div>
    <div class="row g-4 mb-5">
      <?php
      $reasons=[
        ["🌿","Fresh Ingredients","We source only the finest, freshest ingredients. No preservatives, no shortcuts — just pure, natural goodness baked into every item."],
        ["👩‍🍳","Expert Bakers","Our team of passionate bakers brings years of experience and creativity to every recipe, ensuring consistent quality and taste."],
        ["🚚","Reliable Delivery","We deliver within 1–3 working days across Sri Lanka. Orders are carefully packaged to arrive in perfect condition."],
        ["🎁","Custom Gift Boxes","Our signature Custom Sweet Box lets you curate a personalised gift experience — perfect for any occasion."],
        ["💬","Excellent Support","Our friendly customer support team is always ready to help — whether it's a special request or a last-minute order."],
        ["❤️","Made With Love","Every single item is made with genuine care and passion. We believe food tastes better when it is made with love."],
      ];
      foreach($reasons as [$icon,$title,$desc]):
      ?>
      <div class="col-md-4 animate-on-scroll">
        <div class="testimonial-card text-center">
          <div style="font-size:2.5rem;margin-bottom:.8rem;"><?= $icon ?></div>
          <h4 style="font-size:1.05rem;margin-bottom:.6rem;"><?= $title ?></h4>
          <p style="font-size:.88rem;margin:0;"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Stats -->
    <div class="newsletter-section p-5">
      <div class="row g-4 text-center">
        <?php
        $stats=[["500+","Happy Customers"],["20+","Dessert Varieties"],["4.9★","Average Rating"],["3","Years of Baking"]];
        foreach($stats as [$v,$l]):
        ?>
        <div class="col-md-3 col-6">
          <div style="font-size:2.4rem;font-weight:700;color:#fff;"><?= $v ?></div>
          <div style="color:#f0d0c0;font-size:.88rem;"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
