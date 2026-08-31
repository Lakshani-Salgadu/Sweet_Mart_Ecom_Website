<?php
if (!function_exists('clean')) {
    function clean($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return false;
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return false;
    }
}
if (!function_exists('showFlash')) {
    function showFlash() {}
}

$cartCount = 0;
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ===================== NAVBAR ===================== -->
<nav class="navbar navbar-expand-lg sm-navbar sticky-top">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand sm-logo" href="index.php">
      <img src="assets/images/logo.svg" alt="SweetMart Logo" class="logo-img" width="32" height="32">
      <span class="logo-text">Sweet<span class="logo-accent">Mart</span></span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Links -->
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#categories">Shop</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#custom-box">Custom Box</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#about">About Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#contact">Contact</a>
        </li>
      </ul>

      <!-- Right Side -->
      <div class="nav-actions d-flex align-items-center gap-2">
        <a href="#login" class="btn btn-sm btn-outline-sm" id="nav-login-btn">
          <i class="bi bi-person"></i> Login
        </a>
        <a href="#cart" class="btn btn-sm btn-cart" id="nav-cart-btn">
          <i class="bi bi-bag"></i>
          <span class="cart-badge" id="cart-count"><?= $cartCount ?></span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Flash Message Container -->
<div class="container">
  <?php showFlash(); ?>
</div>
