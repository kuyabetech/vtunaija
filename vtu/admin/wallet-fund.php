<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

// Make sure session_start() is called only once and before any output in config.php and functions.php
// Remove any whitespace or output before <?php in all included files, especially admin-header.php

$db = DB::getInstance()->getConnection();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user = $user_id ? getUserById($user_id) : null;
$success = '';
$error = '';

if (!$user) {
    $error = 'User not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $amount = floatval($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    $action = $_POST['action'] ?? 'fund'; // 'fund' or 'deduct'

    if ($amount <= 0) {
        $error = 'Enter a valid amount.';
    } elseif ($action === 'deduct' && $amount > $user['wallet_balance']) {
        $error = 'Cannot deduct more than current wallet balance.';
    } else {
        $signedAmount = ($action === 'deduct') ? -$amount : $amount;

        // Update wallet
        $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->execute([$signedAmount, $user_id]);

        // Log transaction
        $stmt = $db->prepare("INSERT INTO wallet_transactions (user_id, amount, reference, status, payment_method, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $reference = strtoupper($action) . '_' . time() . '_' . uniqid();
        $description = $note ?: (($action === 'deduct') ? 'Admin wallet deduction' : 'Admin wallet funding');
        $stmt->execute([
            $user_id,
            $signedAmount,
            $reference,
            'successful',
            'admin',
            $description
        ]);

        if ($action === 'deduct') {
            $success = "Wallet deducted successfully. ₦" . number_format($amount, 2) . " removed from {$user['name']}'s wallet.";
        } else {
            $success = "Wallet funded successfully. ₦" . number_format($amount, 2) . " added to {$user['name']}'s wallet.";
        }
        // Refresh user balance
        $user = getUserById($user_id);
    }
}
?>
<?php include '../includes/admin-header.php'; ?>

<div class="container" style="max-width:480px;margin:3rem auto 2rem auto;">
    <div class="card" style="background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(37,99,235,0.08);">
        <div class="card-header" style="background:#2563eb;color:#fff;border-radius:12px 12px 0 0;">
            <h4 style="margin-bottom:0;">Fund/Deduct User Wallet</h4>
        </div>
        <div class="card-body" style="padding:2rem;">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="background:#fee2e2;color:#ef4444;border-radius:8px;padding:1rem 1.2rem;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" style="background:#ecfdf5;color:#10b981;border-radius:8px;padding:1rem 1.2rem;"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($user): ?>
                <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
                <p><strong>Current Balance:</strong> ₦<?php echo number_format($user['wallet_balance'], 2); ?></p>
                <form method="post">
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label style="font-weight:600;color:#2563eb;">Action</label><br>
                        <label>
                            <input type="radio" name="action" value="fund" checked> Fund
                        </label>
                        <label style="margin-left:1.5rem;">
                            <input type="radio" name="action" value="deduct"> Deduct
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label for="amount" style="font-weight:600;color:#2563eb;">Amount (₦)</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1" required style="border-radius:8px;border:1px solid #e5e7eb;padding:0.7rem 1.2rem;">
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label for="note" style="font-weight:600;color:#2563eb;">Note (optional)</label>
                        <input type="text" class="form-control" id="note" name="note" maxlength="100" style="border-radius:8px;border:1px solid #e5e7eb;padding:0.7rem 1.2rem;">
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" style="background:#2563eb;border-radius:8px;font-weight:600;">Submit</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
