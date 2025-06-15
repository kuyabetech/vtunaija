<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user_id'];
$db = DB::getInstance()->getConnection();

// Fetch latest 10 notifications for the user (or global notifications)
$stmt = $db->prepare("
    SELECT title, message, DATE_FORMAT(created_at, '%b %e, %H:%i') as created_at
    FROM notifications
    WHERE user_id = ? OR user_id IS NULL
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
