<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Custom Sweet Box";
// Handle submission
if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["box_state"])) {
  $data = json_decode($_POST["box_state"],true);
  if ($data && !empty($data["size"])) {
    $_SESSION["custom_box"] = [
      "size"        => $data["size"],
      "desserts"    => $data["desserts"] ?? [],
      "occasion"    => $data["occasion"] ?? "",
      "message"     => $data["message"]  ?? "",
      "total_price" => (float)($_POST["total_price"] ?? 0),
    ];
    setFlash("success","Custom box added to cart! 🎁");
    header("Location: cart.php"); exit;
  }
}
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero text-center">
  <div class="container">
    <div style="font-size:3rem;">🎨</div>
    <h1 class="section-title">Build Your Custom Sweet Box</h1>
    <p class="section-sub">Choose your desserts, pick an occasion, add a message — we do the rest!</p>
  </div>
</div>
<section class="section-pad">
  <div class="container" style="max-width:860px;">
    <!-- Step Indicators -->
    <div class="box-steps mb-5">
      <?php
      $steps=[["1","Size"],["2","Desserts"],["3","Occasion"],["4","Message"],["5","Review"]];
      foreach($steps as $i=>[$n,$l]):
      ?>
      <div class="box-step <?= $i===0?'active':'' ?>" id="stepind-<?= $n ?>">
        <div class="step-circle"><?= $n ?></div>
        <div class="step-label"><?= $l ?></div>
      </div>
      <?php if($i<4): ?><div class="step-line"></div><?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Step 1: Size -->
    <div class="step-panel active" id="step1">
      <h3 class="text-center mb-4">Step 1 — Choose Your Box Size</h3>
      <div class="row g-3 justify-content-center mb-4">
        <?php
        $sizes=[
          ["Small","Small","800","4 items","Perfect for 1–2 people","🎀"],
          ["Medium","Medium","1400","8 items","Great for sharing","🎁"],
          ["Large","Large","2200","12 items","Ideal for parties","🎊"],
        ];
        foreach($sizes as [$sz,$label,$price,$cap,$desc,$icon]):
        ?>
        <div class="col-md-4">
          <div class="size-option" data-size="<?= $sz ?>" onclick="selectSize('<?= $sz ?>')" id="size-<?= strtolower($sz) ?>">
            <div style="font-size:2.5rem;margin-bottom:.5rem;"><?= $icon ?></div>
            <div style="font-weight:700;font-size:1.1rem;"><?= $label ?></div>
            <div style="color:var(--pink);font-weight:600;">LKR <?= number_format($price) ?></div>
            <div style="font-size:.82rem;color:#8a6a5a;margin-top:.3rem;"><?= $cap ?> · <?= $desc ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center"><button class="btn-primary-sm" onclick="goStep(2)" id="next-step1">Next: Choose Desserts →</button></div>
    </div>

    <!-- Step 2: Desserts -->
    <div class="step-panel" id="step2">
      <h3 class="text-center mb-2">Step 2 — Choose Your Desserts</h3>
      <p class="text-center mb-3" style="color:#8a6a5a;" id="max-items-note">Select your desserts below.</p>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>Total Items: <strong id="total-items-count">0</strong></div>
        <div>Price so far: <strong id="box-price-preview">LKR 0.00</strong></div>
      </div>
      <div class="row g-3" id="dessert-items-wrap"></div>
      <div class="d-flex gap-3 justify-content-center mt-4">
        <button class="btn-outline-brown" onclick="goStep(1)" id="back-step2">← Back</button>
        <button class="btn-primary-sm" onclick="goStep(3)" id="next-step2">Next: Choose Occasion →</button>
      </div>
    </div>

    <!-- Step 3: Occasion -->
    <div class="step-panel" id="step3">
      <h3 class="text-center mb-4">Step 3 — Choose an Occasion</h3>
      <div class="row g-3 justify-content-center mb-4">
        <?php
        $occs=[["Birthday","🎂"],["Anniversary","💑"],["Graduation","🎓"],["Thank You","🙏"],["Surprise","🎉"],["Just Because","❤️"]];
        foreach($occs as [$occ,$icon]):
        ?>
        <div class="col-md-4 col-6">
          <div class="occasion-option" data-occ="<?= $occ ?>" onclick="selectOccasion('<?= $occ ?>')" id="occ-<?= strtolower(str_replace(' ','-',$occ)) ?>">
            <div style="font-size:2rem;margin-bottom:.4rem;"><?= $icon ?></div>
            <div style="font-weight:600;"><?= $occ ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="d-flex gap-3 justify-content-center">
        <button class="btn-outline-brown" onclick="goStep(2)" id="back-step3">← Back</button>
        <button class="btn-primary-sm" onclick="goStep(4)" id="next-step3">Next: Add Message →</button>
      </div>
    </div>

    <!-- Step 4: Message -->
    <div class="step-panel" id="step4">
      <h3 class="text-center mb-4">Step 4 — Add a Personal Message</h3>
      <div class="cart-summary-box" style="max-width:500px;margin:0 auto 2rem;">
        <label class="form-label fw-bold" for="box-message">Your Message (optional)</label>
        <textarea id="box-message" class="form-control" rows="4" placeholder="e.g. Happy Birthday! Wishing you all the sweetest moments ❤️" style="border:1.5px solid #e8d5c8;border-radius:8px;"></textarea>
        <div style="font-size:.78rem;color:#8a6a5a;margin-top:.5rem;">This message will be printed on a card inside your box.</div>
      </div>
      <div class="d-flex gap-3 justify-content-center">
        <button class="btn-outline-brown" onclick="goStep(3)" id="back-step4">← Back</button>
        <button class="btn-primary-sm" onclick="goStep(5)" id="next-step4">Review My Box →</button>
      </div>
    </div>

    <!-- Step 5: Review -->
    <div class="step-panel" id="step5">
      <h3 class="text-center mb-4">Step 5 — Review Your Box</h3>
      <div class="cart-summary-box mb-4" id="review-content" style="max-width:500px;margin:0 auto;"></div>
      <form method="POST" style="max-width:500px;margin:0 auto;text-align:center;">
        <input type="hidden" name="box_state" id="box-state-input">
        <input type="hidden" name="total_price" id="total-price-input">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <button type="button" class="btn-outline-brown" onclick="goStep(4)" id="back-step5">← Edit</button>
          <button type="submit" class="btn-primary-sm" id="add-box-cart-btn" onclick="document.getElementById('total-price-input').value=updatePrice()">🛒 Add Custom Box to Cart</button>
        </div>
      </form>
    </div>
  </div>
</section>
<script src="assets/js/custom_box.js"></script>
<?php require __DIR__ . "/includes/footer.php"; ?>
