<?php
// Sweet Mart – Shared Helper Functions

// ─── Session Start ───────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Auth Helpers ────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        $currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $intended = $currentPage . ($queryString ? '?' . $queryString : '');
        $redirectUrl = $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'redirect=' . urlencode($intended);
        header("Location: $redirectUrl");
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}

function getUserProfile(int $userId): ?array {
    global $conn;
    if (!$conn || $userId <= 0) return null;
    $stmt = $conn->prepare("SELECT u.id, u.full_name, u.email, u.phone, u.shipping_address, u.billing_address, u.role, u.created_at, p.shipping_address AS profile_shipping, p.billing_address AS profile_billing FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id WHERE u.id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $row['user_id'] = (int)$row['id'];
        $row['shipping_address'] = !empty($row['shipping_address']) ? $row['shipping_address'] : ($row['profile_shipping'] ?? '');
        $row['billing_address'] = !empty($row['billing_address']) ? $row['billing_address'] : ($row['profile_billing'] ?? '');
        return $row;
    }
    return null;
}

function updateUserProfile(int $userId, array $data): bool {
    global $conn;
    if (!$conn || $userId <= 0) return false;
    $fullName = trim($data['full_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $shipping = trim($data['shipping_address'] ?? '');
    $billing = trim($data['billing_address'] ?? '');
    if (!$fullName) return false;

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, shipping_address = ?, billing_address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $fullName, $phone, $shipping, $billing, $userId);
    $ok = $stmt->execute();

    // Sync user_profiles table
    $stmtProf = $conn->prepare("INSERT INTO user_profiles (user_id, phone, shipping_address, billing_address) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE phone = VALUES(phone), shipping_address = VALUES(shipping_address), billing_address = VALUES(billing_address)");
    $stmtProf->bind_param("isss", $userId, $phone, $shipping, $billing);
    $stmtProf->execute();

    if ($ok) {
        $_SESSION['full_name'] = $fullName;
    }
    return $ok;
}

// ─── Cart Helpers ─────────────────────────────────────────────────
function getCartCount(): int {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function addToCart(int $productId, int $qty = 1): void {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
}

function removeFromCart(int $productId): void {
    unset($_SESSION['cart'][$productId]);
}

function updateCartQty(int $productId, int $qty): void {
    if ($qty <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
    unset($_SESSION['custom_box']);
}

// ─── Cart Totals ─────────────────────────────────────────────────
function getDeliveryFee(float $subtotal): float {
    if ($subtotal >= 5000) return 0;
    if ($subtotal >= 2000) return 200;
    return 350;
}

// ─── Product Image ────────────────────────────────────────────────
function productImage(string $img, string $alt = ''): string {
    $path = 'assets/images/products/' . $img;
    // Determine depth (admin pages need ../)
    $base = defined('ADMIN_PAGE') ? '../' : '';
    $full = $base . $path;
    if (file_exists(__DIR__ . '/../' . $path)) {
        return '<img src="' . htmlspecialchars($full) . '" alt="' . htmlspecialchars($alt) . '" loading="lazy">';
    }
    // Emoji fallback by category
    $emojis = [
        'cake' => '🎂', 'cupcake' => '🧁', 'brownie' => '🍫',
        'cookie' => '🍪', 'donut' => '🍩', 'box' => '🎁'
    ];
    $emoji = '🍰';
    foreach ($emojis as $k => $e) {
        if (str_contains(strtolower($img), $k)) { $emoji = $e; break; }
    }
    return '<span class="img-placeholder">' . $emoji . '</span>';
}

// ─── Rating Stars ─────────────────────────────────────────────────
function renderStars(float $rating): string {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="star full">★</i>';
        } elseif ($i - $rating < 1 && $i - $rating > 0) {
            $html .= '<i class="star half">★</i>';
        } else {
            $html .= '<i class="star empty">☆</i>';
        }
    }
    $html .= '</span>';
    return $html;
}

// ─── Sanitize ─────────────────────────────────────────────────────
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ─── Format Price ─────────────────────────────────────────────────
function price(float $amount): string {
    return 'LKR ' . number_format($amount, 2);
}

// ─── Flash Messages ───────────────────────────────────────────────
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash(): void {
    $f = getFlash();
    if (!$f) return;
    $icon = $f['type'] === 'success' ? '✅' : ($f['type'] === 'error' ? '❌' : 'ℹ️');
    echo '<div class="alert alert-' . clean($f['type']) . '">' . $icon . ' ' . clean($f['msg']) . '</div>';
}
