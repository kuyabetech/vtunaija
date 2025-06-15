<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../classes/VtuService.php';

// Log execution
file_put_contents(__DIR__.'/cron.log', "[" . date('Y-m-d H:i:s') . "] Starting reconciliation\n", FILE_APPEND);

$db = DB::getInstance()->getConnection();
$vtu = new VtuService();

// Get pending transactions older than 5 minutes
$stmt = $db->prepare("SELECT * FROM vtu_transactions WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$stmt->execute();
$pendingTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendingTransactions as $txn) {
    try {
        file_put_contents(__DIR__.'/cron.log', "Processing TXN: {$txn['reference']}\n", FILE_APPEND);
        
        // Check with VTU provider for status
        $statusResponse = $vtu->checkTransactionStatus($txn['reference']);
        
        if ($statusResponse['status'] === 'success') {
            // Transaction was successful
            $stmt = $db->prepare("UPDATE vtu_transactions SET status = 'successful', api_response = ? WHERE reference = ?");
            $stmt->execute([json_encode($statusResponse), $txn['reference']]);
            
            file_put_contents(__DIR__.'/cron.log', "TXN {$txn['reference']} confirmed successful\n", FILE_APPEND);
        } elseif ($statusResponse['status'] === 'failed') {
            // Transaction failed - refund user
            $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$txn['amount'], $txn['user_id']]);
            
            $stmt = $db->prepare("UPDATE vtu_transactions SET status = 'failed', api_response = ? WHERE reference = ?");
            $stmt->execute([json_encode($statusResponse), $txn['reference']]);
            
            // Log refund
            addWalletTransaction(
                $txn['user_id'],
                $txn['amount'],
                'REFUND_' . $txn['reference'],
                'successful',
                'system',
                'System',
                'Refund for failed transaction ' . $txn['reference']
            );
            
            file_put_contents(__DIR__.'/cron.log', "TXN {$txn['reference']} failed - refund issued\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        file_put_contents(__DIR__.'/cron.log', "Error processing TXN {$txn['reference']}: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

file_put_contents(__DIR__.'/cron.log', "[" . date('Y-m-d H:i:s') . "] Reconciliation completed\n", FILE_APPEND);