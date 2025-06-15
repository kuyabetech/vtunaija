<?php
// verify-payment.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['reference'])) {
    die('No reference supplied');
}

$reference = sanitizeInput($_GET['reference']);

// Verify with Paystack
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die('Curl returned error: ' . $err);
}

$result = json_decode($response, true);

if (!$result['status']) {
    die('Paystack error: ' . $result['message']);
}

// Payment was successful
$amount = $result['data']['amount'] / 100; // Convert from kobo to naira
$user_id = $result['data']['metadata']['user_id'];

// Update wallet
$db = DB::getInstance()->getConnection();
$stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
$stmt->execute([$amount, $user_id]);

// Record transaction
addWalletTransaction(
    $user_id,
    $amount,
    $reference,
    'successful',
    'paystack',
    'Paystack',
    'Wallet funding via Paystack'
);

// Redirect to wallet page with success message
$_SESSION['success'] = 'Wallet funded successfully!';
redirect('wallet.php');
?>