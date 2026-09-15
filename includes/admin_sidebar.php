<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$base = '../';
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header">
    <a href="<?= $base ?>index.php" class="sidebar-logo">🍰 SweetMart</a>
    <button class="sidebar-toggle" id="sidebarClose"><i class="bi bi-x-lg"></i></button>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-avatar">👤</div>
    <div>
      <div class="sidebar-username"><?= clean($_SESSION['full_name'] ?? 'Admin') ?></div>
      <div class="sidebar-role">Administrator</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Dashboard</div>
    <a href="<?= $base ?>admin/index.php" class="sidebar-link <?= $currentAdmin === 'index.php' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="nav-section-label">Products</div>
    <a href="<?= $base ?>admin/products.php" class="sidebar-link <?= $currentAdmin === 'products.php' ? 'active' : '' ?>">
      <i class="bi bi-box-seam"></i> Products
    </a>
    <a href="<?= $base ?>admin/categories.php" class="sidebar-link <?= $currentAdmin === 'categories.php' ? 'active' : '' ?>">
      <i class="bi bi-tags"></i> Categories
    </a>
    <a href="<?= $base ?>admin/product_form.php" class="sidebar-link <?= $currentAdmin === 'product_form.php' ? 'active' : '' ?>">
      <i class="bi bi-plus-circle"></i> Add Product
    </a>

    <div class="nav-section-label">Orders</div>
    <a href="<?= $base ?>admin/orders.php" class="sidebar-link <?= $currentAdmin === 'orders.php' ? 'active' : '' ?>">
      <i class="bi bi-bag-check"></i> All Orders
    </a>

    <div class="nav-section-label">Customers</div>
    <a href="<?= $base ?>admin/customers.php" class="sidebar-link <?= $currentAdmin === 'customers.php' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Customers
    </a>

    <div class="nav-section-label">Store</div>
    <a href="<?= $base ?>index.php" class="sidebar-link" target="_blank">
      <i class="bi bi-shop"></i> View Store
    </a>
    <a href="<?= $base ?>logout.php" class="sidebar-link text-danger">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </nav>
</aside>
