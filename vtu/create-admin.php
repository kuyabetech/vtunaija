<?php
// Run this script once to create an admin user, then delete or protect this file!

require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = DB::getInstance()->getConnection();

// Set your desired admin email and password here
$email = 'abdulvirus6@gmail.com'; // CHANGE THIS
$password = 'Admin@123!'; // CHANGE THIS
$name = 'Admin';

$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert admin user (if table already exists, just insert the admin row)
$stmt = $db->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 1)");
$stmt->execute([$name, $email, $hashed]);

echo "Admin user created successfully with email: $email. Please delete this script after running!";
