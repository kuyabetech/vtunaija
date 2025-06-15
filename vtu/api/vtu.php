<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

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
$service_type = isset($data['service_type']) ? trim($data['service_type']) : '';
$network = isset($data['network']) ? trim($data['network']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$reference = isset($data['reference']) ? trim($data['reference']) : '';

if (!$user_id || !$service_type || !$network || !$phone || !$amount || !$reference) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$db = DB::getInstance()->getConnection();

// Check for duplicate reference
$stmt = $db->prepare("SELECT id FROM vtu_transactions WHERE reference = ? LIMIT 1");
$stmt->execute([$reference]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Duplicate reference']);
    exit;
}

// Insert transaction (status: pending)
$stmt = $db->prepare("INSERT INTO vtu_transactions (user_id, service_type, network, phone, amount, reference, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
if ($stmt->execute([$user_id, $service_type, $network, $phone, $amount, $reference])) {
    // Optionally, trigger VTU API integration here
    echo json_encode(['status' => 'success', 'message' => 'Transaction created', 'transaction_id' => $db->lastInsertId()]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create transaction']);
}
