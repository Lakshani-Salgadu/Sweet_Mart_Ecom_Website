<?php
// includes/payhere_config.php

// ==========================================
// PayHere Sandbox Configuration - Sweet Mart
// ==========================================

// PayHere Sandbox Merchant ID
define('PAYHERE_MERCHANT_ID', '1238483');

// IMPORTANT:
// PayHere Sandbox -> Integrations -> localhost
// Click "Copy" beside MERCHANT SECRET
// Paste the CURRENT secret below.
//
// Do NOT use an old Merchant Secret.
// Do NOT upload this secret to a public GitHub repository.
define('PAYHERE_MERCHANT_SECRET', 'MjIwMTg3MzgyMjQzODUxOTYxMTc1MjQ0NjI3MjQwMzE2OTY1MA==');

// Currency
define('PAYHERE_CURRENCY', 'LKR');


// ==========================================
// PayHere URLs
// ==========================================

// IMPORTANT:
// localhost cannot receive PayHere server-to-server
// notifications from the internet.
//
// This can stay like this temporarily while testing
// the checkout redirect.
//
// Later, replace this with an ngrok/public URL
// when testing payment notifications.
define(
    'PAYHERE_NOTIFY_URL',
    'http://localhost/sweet%20mart%20-07/payhere_notify.php'
);


// Page PayHere sends the customer to after payment
define(
    'PAYHERE_RETURN_URL',
    'http://localhost/sweet%20mart%20-07/payhere_return.php'
);


// Page PayHere sends the customer to if payment is cancelled
define(
    'PAYHERE_CANCEL_URL',
    'http://localhost/sweet%20mart%20-07/payhere_cancel.php'
);