<?php
require_once __DIR__ . '/functions.php';
$cartCount = getCartCount();
$base = defined('ADMIN_PAGE') ? '../' : '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sweet Mart – Sri Lanka's premier online dessert &amp; sweet gift store. Order cakes, cupcakes, brownies, cookies, donuts &amp; custom gift boxes.">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' | ' : '' ?>Sweet Mart</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<!-- ===================== NAVBAR ===================== -->
<nav class="navbar navbar-expand-lg sm-navbar sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand sm-logo" href="<?= $base ?>index.php">
      <span class="logo-icon">🍰</span>
      <span class="logo-text">Sweet<span class="logo-accent">Mart</span></span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Links -->
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= $base ?>index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'shop.php' ? 'active' : '' ?>" href="<?= $base ?>shop.php">Shop</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'custom_box.php' ? 'active' : '' ?>" href="<?= $base ?>custom_box.php">Custom Box</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="<?= $base ?>about.php">About Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="<?= $base ?>contact.php">Contact</a>
        </li>
      </ul>

      <!-- Right Side -->
      <div class="nav-actions d-flex align-items-center gap-2">
        <?php if (isLoggedIn()): ?>
          <div class="dropdown d-inline-block">
            <button class="btn btn-sm btn-outline-sm dropdown-toggle d-flex align-items-center gap-1" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i> <span><?= clean($_SESSION['full_name'] ?? 'Account') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenuDropdown" style="border-radius:12px;border:1px solid #f0e0d8;padding:8px 0;min-width:180px;">
              <li><a class="dropdown-item py-2" href="<?= $base ?>profile.php" id="nav-profile-link"><i class="bi bi-person me-2 text-primary"></i> My Profile</a></li>
              <li><a class="dropdown-item py-2" href="<?= $base ?>my_orders.php" id="nav-orders-link"><i class="bi bi-box-seam me-2 text-success"></i> My Orders</a></li>
              <?php if (isAdmin()): ?>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item py-2" href="<?= $base ?>admin/index.php" id="nav-admin-link"><i class="bi bi-speedometer2 me-2 text-warning"></i> Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider my-1"></li>
              <li><a class="dropdown-item py-2 text-danger" href="<?= $base ?>logout.php" id="nav-logout-dropdown-btn"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
          <a href="<?= $base ?>my_orders.php" class="btn btn-sm btn-outline-sm d-none" id="nav-orders-btn">My Orders</a>
          <?php if (isAdmin()): ?>
          <a href="<?= $base ?>admin/index.php" class="btn btn-sm btn-outline-sm d-none d-xl-inline-block" id="nav-admin-btn">
            <i class="bi bi-speedometer2"></i> Admin
          </a>
          <?php endif; ?>
          <a href="<?= $base ?>logout.php" class="btn btn-sm btn-ghost" id="nav-logout-btn">Logout</a>
        <?php else: ?>
          <a href="<?= $base ?>login.php" class="btn btn-sm btn-outline-sm" id="nav-login-btn">
            <i class="bi bi-person"></i> Login
          </a>
        <?php endif; ?>

        <a href="<?= $base ?>cart.php" class="btn btn-sm btn-cart" id="nav-cart-btn">
          <i class="bi bi-bag"></i>
          <span class="cart-badge" id="cart-count"><?= $cartCount ?></span>
        </a>
      </div>
    </div>
  </div>
</nav>
<!-- Flash Message -->
<div class="container">
  <?php showFlash(); ?>
</div>
