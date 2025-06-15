<?php
// Start session at the very top before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vtu_app');

// Site configuration
define('SITE_NAME', 'VTU Pro');
define('SITE_URL', 'http://localhost/vtu');

// VTU API configuration
define('VTU_API_KEY', 'your_api_key_here');
define('VTU_API_URL', 'https://vtuprovider.com/api');

// Payment gateway configuration
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxx');
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxx');
define('PAYSTACK_CALLBACK_URL', SITE_URL.'/verify-payment.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
?>