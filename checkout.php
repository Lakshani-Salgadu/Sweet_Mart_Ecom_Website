<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

// Protected Route: Checkout requires authentication
if (!isLoggedIn()) {
    setFlash("info", "Please log in or register to complete your order.");
    header("Location: login.php?redirect=" . urlencode("checkout.php"));
    exit;
}

$pageTitle = "Checkout";
$userId = (int)$_SESSION["user_id"];
$userProfile = getUserProfile($userId);

// Load cart
$cartItems = []; $subtotal = 0;
if (!empty($_SESSION["cart"])) {
  $ids  = implode(",", array_map("intval", array_keys($_SESSION["cart"])));
  $rows = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
  while ($r = $rows->fetch_assoc()) {
    $qty = $_SESSION["cart"][$r["id"]];
    $r["qty"] = $qty; $r["line"] = $r["price"]*$qty;
    $subtotal += $r["line"]; $cartItems[] = $r;
  }
}
$customBox = $_SESSION["custom_box"] ?? null;
if ($customBox) $subtotal += $customBox["total_price"];
if (empty($cartItems) && !$customBox) { header("Location: cart.php"); exit; }
$delivery = getDeliveryFee($subtotal);
$total    = $subtotal + $delivery;
$errors   = [];
// POST
if ($_SERVER["REQUEST_METHOD"]==="POST") {
  $fn  = trim($_POST["full_name"]  ?? "");
  $em  = trim($_POST["email"]      ?? "");
  $ph  = trim($_POST["phone"]      ?? "");
  $addr= trim($_POST["address"]    ?? "");
  $city= trim($_POST["city"]       ?? "");
  $pc  = trim($_POST["postal_code"]?? "");
  $dd  = $_POST["delivery_date"]   ?? "";
  $pay = $_POST["payment_method"]  ?? "cod";
  if (!$fn)  $errors[] = "Full name required.";
  if (!filter_var($em,FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
  if (!$ph)  $errors[] = "Phone number required.";
  if (!$addr) $errors[] = "Address required.";
  if (!$city) $errors[] = "City required.";
  if (empty($errors)) {
    $uid = $userId;
    $stmt = $conn->prepare("INSERT INTO orders (user_id,full_name,email,phone,address,city,postal_code,delivery_date,payment_method,subtotal,delivery_fee,total,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'Pending')");
    $stmt->bind_param("issssssssddd",$uid,$fn,$em,$ph,$addr,$city,$pc,$dd,$pay,$subtotal,$delivery,$total);
    $stmt->execute();
    $oid = $conn->insert_id;
    // Insert items
    $is = $conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,quantity,unit_price) VALUES (?,?,?,?,?)");
    foreach ($cartItems as $it) {
      $is->bind_param("iisid",$oid,$it["id"],$it["name"],$it["qty"],$it["price"]);
      $is->execute();
    }
    if ($customBox) {
      $cbStmt = $conn->prepare("INSERT INTO custom_boxes (user_id,box_size,occasion,message,total_price) VALUES (?,?,?,?,?)");
      $cbStmt->bind_param("isssd",$uid,$customBox["size"],$customBox["occasion"],$customBox["message"],$customBox["total_price"]);
      $cbStmt->execute(); $cbId = $conn->insert_id;
      $io = $conn->prepare("INSERT INTO order_items (order_id,custom_box_id,product_name,quantity,unit_price) VALUES (?,?,'Custom Sweet Box',1,?)");
      $io->bind_param("iid",$oid,$cbId,$customBox["total_price"]); $io->execute();
    }
    // Payment record
    $ps = $conn->prepare("INSERT INTO payments (order_id,method,status) VALUES (?,?,?)");
    $pst = $pay==="card"?"Paid":"Pending";
    $ps->bind_param("iss",$oid,$pay,$pst); $ps->execute();

    // Auto-update profile address/phone if missing
    if ($userProfile && (empty($userProfile["phone"]) || empty($userProfile["shipping_address"]))) {
      $upData = [
        "full_name"        => $fn,
        "phone"            => !empty($userProfile["phone"]) ? $userProfile["phone"] : $ph,
        "shipping_address" => !empty($userProfile["shipping_address"]) ? $userProfile["shipping_address"] : $addr,
        "billing_address"  => $userProfile["billing_address"] ?? ""
      ];
      updateUserProfile($userId, $upData);
    }

    clearCart();
    $_SESSION["last_order_id"] = $oid;
    header("Location: order_confirmation.php"); exit;
  }
}
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero"><div class="container"><h1 class="section-title">Checkout</h1></div></div>
<section class="section-pad">
  <div class="container">
    <?php foreach($errors as $e): ?><div class="alert alert-error">❌ <?= clean($e) ?></div><?php endforeach; ?>
    <form method="POST" class="checkout-form">
      <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-7">
          <div class="cart-summary-box mb-4">
            <h5 class="mb-3">👤 Customer Information</h5>
            <div class="mb-3"><label class="form-label">Full Name *</label><input name="full_name" class="form-control" required value="<?= clean($_POST["full_name"] ?? $userProfile["full_name"] ?? $_SESSION["full_name"] ?? "") ?>" id="input-name"></div>
            <div class="row g-3">
              <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input name="email" type="email" class="form-control" required value="<?= clean($_POST["email"] ?? $userProfile["email"] ?? $_SESSION["email"] ?? "") ?>" id="input-email"></div>
              <div class="col-md-6 mb-3"><label class="form-label">Phone *</label><input name="phone" class="form-control" required value="<?= clean($_POST["phone"] ?? $userProfile["phone"] ?? "") ?>" id="input-phone"></div>
            </div>
          </div>
          <div class="cart-summary-box mb-4">
            <h5 class="mb-3">🚚 Delivery Information</h5>
            <div class="mb-3"><label class="form-label">Address *</label><textarea name="address" class="form-control" rows="2" required id="input-address"><?= clean($_POST["address"] ?? $userProfile["shipping_address"] ?? "") ?></textarea></div>
            <div class="row g-3">
              <div class="col-md-6 mb-3"><label class="form-label">City *</label><input name="city" class="form-control" required value="<?= clean($_POST["city"]??"") ?>" id="input-city"></div>
              <div class="col-md-6 mb-3"><label class="form-label">Postal Code</label><input name="postal_code" class="form-control" value="<?= clean($_POST["postal_code"]??"") ?>" id="input-postal"></div>
            </div>
            <div class="mb-3"><label class="form-label">Preferred Delivery Date</label><input name="delivery_date" type="date" class="form-control" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" value="<?= clean($_POST["delivery_date"]??"") ?>" id="input-date"></div>
          </div>
          <div class="cart-summary-box">
            <h5 class="mb-3">💳 Payment Method</h5>
            <label class="payment-option d-flex gap-3 align-items-center" onclick="this.classList.toggle('selected')">
              <input type="radio" name="payment_method" value="cod" checked id="pay-cod"> <div><div style="font-weight:600">💵 Cash on Delivery</div><div style="font-size:.82rem;color:#8a6a5a">Pay when your order arrives</div></div>
            </label>
            <label class="payment-option d-flex gap-3 align-items-center" onclick="this.classList.toggle('selected')">
              <input type="radio" name="payment_method" value="card" id="pay-card"> <div><div style="font-weight:600">💳 Card Payment</div><div style="font-size:.82rem;color:#8a6a5a">Visa / Mastercard (secure simulation)</div></div>
            </label>
          </div>
        </div>
        <!-- Summary -->
        <div class="col-lg-5">
          <div class="cart-summary-box" style="position:sticky;top:80px;">
            <h5 class="mb-3">🧾 Order Summary</h5>
            <?php foreach($cartItems as $it): ?>
            <div class="summary-row"><span><?= clean($it["name"]) ?> × <?= $it["qty"] ?></span><span><?= price($it["line"]) ?></span></div>
            <?php endforeach; ?>
            <?php if($customBox): ?>
            <div class="summary-row"><span>🎁 Custom Box</span><span><?= price($customBox["total_price"]) ?></span></div>
            <?php endif; ?>
            <div class="summary-row"><span>Subtotal</span><span><?= price($subtotal) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span><?= $delivery==0?"Free":price($delivery) ?></span></div>
            <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>
            <button type="submit" class="btn-primary-sm w-100 mt-4" style="padding:16px;font-size:1.05rem;" id="place-order-btn">✅ Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
