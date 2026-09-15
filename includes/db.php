<?php
// Database Connection
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sweetmart');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;background:#fff5f5;border:1px solid #f5c2c7;color:#842029;border-radius:8px;margin:20px;">
        <h3>⚠️ Database Connection Failed</h3>
        <p>Could not connect to MySQL. Please ensure XAMPP is running and the database <strong>sweetmart</strong> has been created.</p>
        <p><strong>Error:</strong> ' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Import <code>db/sweetmart.sql</code> via phpMyAdmin to set up the database.</p>
    </div>');
}

$conn->set_charset("utf8mb4");
