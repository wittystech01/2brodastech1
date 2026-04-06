<?php
// Site Configuration
define('SITE_NAME', 'GadgetZone');
define('SITE_URL', 'http://localhost/2brodastech1');
define('SITE_EMAIL', 'info@gadgetzone.com');
define('CURRENCY', '₦');
define('CURRENCY_CODE', 'NGN');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gadgetzone');

// Payment Configuration
define('PAYSTACK_PUBLIC_KEY', 'pk_test_your_paystack_public_key');
define('PAYSTACK_SECRET_KEY', 'sk_test_your_paystack_secret_key');
define('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-your_flutterwave_public_key');
define('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-your_flutterwave_secret_key');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
