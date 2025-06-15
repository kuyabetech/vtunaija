<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set response header
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

// Validate required fields
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$reference = isset($data['reference']) ? trim($data['reference']) : '';
$payment_method = isset($data['payment_method']) ? trim($data['payment_method']) : '';

if (!$user_id || !$amount || !$reference || !$payment_method) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$db = DB::getInstance()->getConnection();

// Check if reference already exists (idempotency)
$stmt = $db->prepare("SELECT id FROM payments WHERE reference = ? LIMIT 1");
$stmt->execute([$reference]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Duplicate reference']);
    exit;
}

// Insert payment record
$stmt = $db->prepare("INSERT INTO payments (user_id, amount, reference, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
if ($stmt->execute([$user_id, $amount, $reference, $payment_method])) {
    // Optionally, trigger wallet funding or payment verification here
    echo json_encode(['status' => 'success', 'message' => 'Payment record created', 'payment_id' => $db->lastInsertId()]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create payment record']);
}
