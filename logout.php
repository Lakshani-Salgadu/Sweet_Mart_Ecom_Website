<?php
require_once __DIR__ . "/includes/functions.php";

// Clear session variables
$_SESSION = [];

// Invalidate session cookie if enabled
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy current session
session_destroy();

// Start a fresh session to store a logout confirmation flash message
session_start();
setFlash("info", "You have been logged out successfully.");

header("Location: index.php");
exit;
