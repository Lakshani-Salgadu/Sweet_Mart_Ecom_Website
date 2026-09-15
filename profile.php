<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

// Must be authenticated
requireLogin();

$pageTitle = "My Profile / Account";
$userId = (int)$_SESSION["user_id"];
$user = getUserProfile($userId);

if (!$user) {
    // If user account is not found in database, clear session
    header("Location: logout.php");
    exit;
}

$profileErrors = [];
$passwordErrors = [];

// Handle Profile Details Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action_update_profile"])) {
    $fullName = trim($_POST["full_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $shipping = trim($_POST["shipping_address"] ?? "");
    $billing = trim($_POST["billing_address"] ?? "");

    if (!$fullName) {
        $profileErrors[] = "Full name is required.";
    }

    if (empty($profileErrors)) {
        $data = [
            "full_name"        => $fullName,
            "phone"            => $phone,
            "shipping_address" => $shipping,
            "billing_address"  => $billing
        ];

        if (updateUserProfile($userId, $data)) {
            setFlash("success", "Your profile details have been updated successfully!");
            header("Location: profile.php");
            exit;
        } else {
            $profileErrors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action_change_password"])) {
    $currentPass = $_POST["current_password"] ?? "";
    $newPass     = $_POST["new_password"] ?? "";
    $confirmPass = $_POST["confirm_new_password"] ?? "";

    if (!$currentPass || !$newPass || !$confirmPass) {
        $passwordErrors[] = "All password fields are required.";
    } else {
        // Fetch current stored password
        $stmt = $conn->prepare("SELECT password, password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $storedUser = $stmt->get_result()->fetch_assoc();
        $storedHash = $storedUser["password_hash"] ?? $storedUser["password"] ?? "";

        if (!password_verify($currentPass, $storedHash)) {
            $passwordErrors[] = "Your current password is incorrect.";
        } elseif (strlen($newPass) < 8) {
            $passwordErrors[] = "New password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Za-z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
            $passwordErrors[] = "New password must contain at least one letter and one number.";
        } elseif ($newPass !== $confirmPass) {
            $passwordErrors[] = "New passwords do not match.";
        } else {
            // Update password with bcrypt hash
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $upStmt = $conn->prepare("UPDATE users SET password = ?, password_hash = ? WHERE id = ?");
            $upStmt->bind_param("ssi", $newHash, $newHash, $userId);
            $upStmt->execute();

            setFlash("success", "Your password has been changed successfully!");
            header("Location: profile.php");
            exit;
        }
    }
}

// Re-fetch user in case updated
$user = getUserProfile($userId);

require __DIR__ . "/includes/header.php";
?>
<div class="page-hero">
  <div class="container">
    <h1 class="section-title">👤 My Profile / Account</h1>
    <p class="section-sub">Manage your personal information, delivery addresses, and account security</p>
  </div>
</div>

<section class="section-pad">
  <div class="container">
    <div class="row g-4">
      <!-- Left Column: Account Overview -->
      <div class="col-lg-4">
        <!-- Overview Card -->
        <div class="cart-summary-box text-center mb-4">
          <div style="width:84px;height:84px;background:var(--pink-light);color:var(--brown-dark);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;font-weight:700;margin-bottom:1rem;border:3px solid #fff;box-shadow:var(--shadow);">
            <?= strtoupper(mb_substr($user["full_name"], 0, 1)) ?>
          </div>
          <h4 style="margin-bottom:.2rem;font-family:'Playfair Display',serif;"><?= clean($user["full_name"]) ?></h4>
          <p style="color:#8a6a5a;font-size:.88rem;margin-bottom:.8rem;"><?= clean($user["email"]) ?></p>

          <div class="d-flex justify-content-center gap-2 mb-3">
            <span class="status-badge" style="background:#e8f5e9;color:#2e7d32;">
              <i class="bi bi-shield-check me-1"></i> Active Account
            </span>
            <span class="status-badge" style="background:var(--pink-light);color:var(--brown);">
              <?= ucfirst(clean($user["role"])) ?>
            </span>
          </div>

          <div class="text-start" style="border-top:1px solid #f5ece5;padding-top:14px;font-size:.85rem;">
            <div class="d-flex justify-content-between py-1">
              <span style="color:#8a6a5a;">User ID:</span>
              <span class="fw-bold">#<?= str_pad($user["user_id"], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span style="color:#8a6a5a;">Phone:</span>
              <span><?= !empty($user["phone"]) ? clean($user["phone"]) : '<em style="color:#aaa;">Not set</em>' ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span style="color:#8a6a5a;">Member Since:</span>
              <span><?= date("d M Y", strtotime($user["created_at"])) ?></span>
            </div>
          </div>
        </div>

        <!-- Quick Links Navigation Card -->
        <div class="cart-summary-box">
          <h5 class="mb-3" style="font-size:1.05rem;">Quick Navigation</h5>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
            <li>
              <a href="my_orders.php" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none" style="background:var(--cream);color:var(--brown-dark);transition:.2s;">
                <span><i class="bi bi-box-seam me-2 text-primary"></i> My Orders</span>
                <i class="bi bi-chevron-right text-muted"></i>
              </a>
            </li>
            <li>
              <a href="cart.php" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none" style="background:var(--cream);color:var(--brown-dark);transition:.2s;">
                <span><i class="bi bi-bag me-2 text-success"></i> My Cart</span>
                <i class="bi bi-chevron-right text-muted"></i>
              </a>
            </li>
            <li>
              <a href="custom_box.php" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none" style="background:var(--cream);color:var(--brown-dark);transition:.2s;">
                <span><i class="bi bi-gift me-2 text-warning"></i> Custom Sweet Box</span>
                <i class="bi bi-chevron-right text-muted"></i>
              </a>
            </li>
            <?php if ($user["role"] === "admin"): ?>
            <li>
              <a href="admin/index.php" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none" style="background:var(--cream);color:var(--brown-dark);transition:.2s;">
                <span><i class="bi bi-speedometer2 me-2 text-danger"></i> Admin Dashboard</span>
                <i class="bi bi-chevron-right text-muted"></i>
              </a>
            </li>
            <?php endif; ?>
            <li>
              <a href="logout.php" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-danger" style="background:var(--cream);transition:.2s;">
                <span><i class="bi bi-box-arrow-right me-2"></i> Logout</span>
                <i class="bi bi-chevron-right text-muted"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Right Column: Profile Edit & Security -->
      <div class="col-lg-8">
        <!-- Edit Profile Details Card -->
        <div class="cart-summary-box mb-4">
          <h4 class="mb-3" style="font-family:'Playfair Display',serif;">📝 Edit Profile Details</h4>

          <?php foreach ($profileErrors as $err): ?>
            <div class="alert alert-error">❌ <?= clean($err) ?></div>
          <?php endforeach; ?>

          <form method="POST" class="checkout-form">
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="profile-name">Full Name *</label>
                <input name="full_name" class="form-control" id="profile-name" required value="<?= clean($user["full_name"]) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="profile-email">Email Address</label>
                <input type="email" class="form-control" id="profile-email" value="<?= clean($user["email"]) ?>" disabled style="background:#f5ece5;cursor:not-allowed;">
                <div class="form-text" style="font-size:.78rem;color:#8a6a5a;">Email address cannot be modified.</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="profile-phone">Phone Number</label>
              <input name="phone" class="form-control" id="profile-phone" placeholder="+94 7X XXX XXXX" value="<?= clean($user["phone"] ?? "") ?>">
            </div>

            <div class="mb-3">
              <label class="form-label" for="profile-shipping">Shipping Address</label>
              <textarea name="shipping_address" class="form-control" id="profile-shipping" rows="3" placeholder="Street Address, City, Postal Code, Country"><?= clean($user["shipping_address"] ?? "") ?></textarea>
              <div class="form-text" style="font-size:.78rem;color:#8a6a5a;">Used as your default delivery address during checkout.</div>
            </div>

            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="profile-billing">Billing Address</label>
                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="copy-address-btn" style="font-size:.8rem;color:var(--brown);" onclick="copyShippingToBilling()">
                  Same as shipping address
                </button>
              </div>
              <textarea name="billing_address" class="form-control" id="profile-billing" rows="3" placeholder="Billing Address (if different from shipping)"><?= clean($user["billing_address"] ?? "") ?></textarea>
            </div>

            <button type="submit" name="action_update_profile" class="btn-primary-sm" id="save-profile-btn">
              💾 Save Profile Details
            </button>
          </form>
        </div>

        <!-- Security / Password Change Card -->
        <div class="cart-summary-box">
          <h4 class="mb-3" style="font-family:'Playfair Display',serif;">🔒 Account Security</h4>
          <p style="color:#8a6a5a;font-size:.88rem;margin-bottom:1.5rem;">Update your account password with a strong combination of letters and numbers.</p>

          <?php foreach ($passwordErrors as $pErr): ?>
            <div class="alert alert-error">❌ <?= clean($pErr) ?></div>
          <?php endforeach; ?>

          <form method="POST" class="checkout-form">
            <div class="mb-3">
              <label class="form-label" for="current-password">Current Password *</label>
              <input name="current_password" type="password" class="form-control" id="current-password" required>
            </div>

            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="new-password">New Password *</label>
                <input name="new_password" type="password" class="form-control" id="new-password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" title="Minimum 8 characters containing letters and numbers">
                <div class="form-text" style="font-size:.78rem;color:#8a6a5a;">Minimum 8 characters with letters &amp; numbers.</div>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label" for="confirm-new-password">Confirm New Password *</label>
                <input name="confirm_new_password" type="password" class="form-control" id="confirm-new-password" required minlength="8">
              </div>
            </div>

            <button type="submit" name="action_change_password" class="btn-outline-brown" id="change-password-btn">
              🔑 Update Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function copyShippingToBilling() {
  const shipping = document.getElementById("profile-shipping").value;
  document.getElementById("profile-billing").value = shipping;
}
</script>

<?php require __DIR__ . "/includes/footer.php"; ?>
