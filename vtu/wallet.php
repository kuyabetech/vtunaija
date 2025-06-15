<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getUserById($_SESSION['user_id']);

// Get wallet transactions
$db = DB::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle fund wallet request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);
    
    if ($amount < 100) {
        $_SESSION['error'] = 'Minimum funding amount is ₦100';
    } else {
        // Generate unique reference
        $reference = 'WALLET_' . time() . '_' . uniqid();
        
        // Initialize Paystack payment
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount * 100, // Convert to kobo
                'email' => $user['email'],
                'reference' => $reference,
                'callback_url' => PAYSTACK_CALLBACK_URL,
                'metadata' => array(
                    'user_id' => $user['id'],
                    'purpose' => 'Wallet Funding'
                )
            ]),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer " . PAYSTACK_SECRET_KEY,
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            $_SESSION['error'] = 'Payment initialization failed';
        } else {
            $result = json_decode($response, true);
            if ($result['status']) {
                // Record pending transaction
                addWalletTransaction(
                    $user['id'],
                    $amount,
                    $reference,
                    'pending',
                    'paystack',
                    'Paystack',
                    'Wallet funding'
                );
                
                // Redirect to Paystack payment page
                header('Location: ' . $result['data']['authorization_url']);
                exit();
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Payment initialization failed';
            }
        }
    }
}

// Handle manual fund wallet request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['manual_fund'])) {
    $manual_amount = floatval($_POST['manual_amount']);
    $manual_reference = trim($_POST['manual_reference']);
    $manual_bank = trim($_POST['manual_bank']);

    if ($manual_amount < 100) {
        $_SESSION['error'] = 'Minimum funding amount is ₦100';
    } elseif (empty($manual_reference) || empty($manual_bank)) {
        $_SESSION['error'] = 'Please provide all manual funding details.';
    } else {
        // Save manual fund to DB (wallet_transactions)
        $reference = 'MANUAL_' . time() . '_' . uniqid();
        $stmt = $db->prepare("INSERT INTO wallet_transactions 
            (user_id, amount, reference, status, type, payment_method, bank, note, created_at) 
            VALUES (?, ?, ?, 'pending', 'manual', 'manual', ?, ?, NOW())");
        $stmt->execute([
            $user['id'],
            $manual_amount,
            $reference,
            $manual_bank,
            'Manual funding: Ref ' . $manual_reference
        ]);
        $_SESSION['success'] = 'Manual funding request submitted. Your wallet will be credited after admin verification.';
        header('Location: wallet.php');
        exit();
    }
}

include 'includes/header.php';
?>
<?php include 'includes/spinner.php'; ?>

<style>
:root {
  --primary: #2563eb;
  --primary-light: #3b82f6;
  --secondary: #059669;
  --accent: #7c3aed;
  --text: #1f2937;
  --text-light: #6b7280;
  --bg: #f9fafb;
  --card-bg: #ffffff;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --border: #e5e7eb;
}
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: var(--bg);
  color: var(--text);
  margin: 0;
  padding: 0;
  line-height: 1.5;
}
.wallet-grid-container {
  display: flex;
  gap: 2rem;
  margin: 2rem auto;
  max-width: 1200px;
  padding: 0 1rem;
}
.wallet-main {
  flex: 2;
  min-width: 0;
}
.wallet-side {
  flex: 1;
  min-width: 260px;
  max-width: 350px;
}
.card, .wallet-balance-card, .wallet-fund-card, .wallet-history, .wallet-quick-actions, .wallet-referral {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
}
.wallet-balance-label, .wallet-fund-title, .wallet-history-title, .quick-actions-title, .referral-title {
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.7rem;
}
.wallet-balance-amount {
  font-size: 2rem;
  font-weight: 700;
  color: #2563eb;
  margin-bottom: 1rem;
}
.wallet-table, .referral-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.97rem;
}
.wallet-table th, .wallet-table td, .referral-table th, .referral-table td {
  padding: 0.7rem 0.5rem;
  border-bottom: 1px solid #e5e7eb;
}
.wallet-table th, .referral-table th {
  background: #f3f4f6;
  font-weight: 600;
}
.wallet-table tr:last-child td, .referral-table tr:last-child td {
  border-bottom: none;
}
.badge-success {
  background: #ecfdf5;
  color: #10b981;
}
.badge-danger {
  background: #fee2e2;
  color: #ef4444;
}
.badge-warning {
  background: #fef3c7;
  color: #f59e0b;
}
.wallet-quick-actions .btn,
.wallet-referral .btn {
  width: 100%;
  margin-bottom: 0.7rem;
}
.referral-link-group {
  display: flex;
  gap: 0.5rem;
  margin: 0.7rem 0;
}
.referral-link-group input[type="text"] {
  flex: 1;
  min-width: 0;
  padding: 0.4rem 0.7rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.97rem;
}
/* Responsive styles */
@media (max-width: 991px) {
  .wallet-grid-container {
    flex-direction: column;
    gap: 0;
    padding: 0 0.5rem;
  }
  .wallet-main, .wallet-side {
    max-width: 100%;
    min-width: 0;
  }
  .wallet-side {
    margin-top: 1.5rem;
  }
  .referral-link-group {
    flex-direction: column;
    gap: 0.5rem;
  }
  .wallet-quick-actions .btn,
  .wallet-referral .btn {
    margin-bottom: 0.5rem;
  }
}
@media (max-width: 600px) {
  .wallet-grid-container {
    margin: 1rem 0;
    padding: 0 0.2rem;
  }
  .card, .wallet-balance-card, .wallet-fund-card, .wallet-history, .wallet-quick-actions, .wallet-referral {
    padding: 1rem;
    margin-bottom: 1rem;
  }
  .wallet-balance-amount {
    font-size: 1.3rem;
  }
  .wallet-table th, .wallet-table td, .referral-table th, .referral-table td {
    padding: 0.5rem 0.3rem;
    font-size: 0.92rem;
  }
  .wallet-quick-actions .btn,
  .wallet-referral .btn {
    font-size: 0.97rem;
  }
}
</style>

<div class="wallet-grid-container">
    <div class="wallet-main">
        <div class="wallet-balance-card card">
            <div class="wallet-balance-label">Available Balance</div>
            <div class="wallet-balance-amount">₦<?php echo number_format($user['wallet_balance'], 2); ?></div>
        </div>
        <div class="wallet-fund-card card">
            <div class="wallet-fund-title">Fund Wallet</div>
            <form method="POST">
                <label for="amount">Amount (₦)</label>
                <input type="number" id="amount" name="amount" min="100" required>
                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
            </form>
            <div class="mt-3">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#manualFundCollapse" aria-expanded="false" aria-controls="manualFundCollapse">
                    Manual Bank Transfer
                </button>
                <div class="collapse mt-2" id="manualFundCollapse">
                    <div class="card card-body" style="background:#f9fafb;">
                        <strong>Bank Name:</strong> Example Bank<br>
                        <strong>Account Name:</strong> VTU Platform<br>
                        <strong>Account Number:</strong> 0123456789<br>
                        <hr>
                        <form method="POST" action="">
                            <input type="hidden" name="manual_fund" value="1">
                            <div class="mb-2">
                                <label for="manual_amount">Amount (₦)</label>
                                <input type="number" class="form-control" id="manual_amount" name="manual_amount" min="100" required>
                            </div>
                            <div class="mb-2">
                                <label for="manual_reference">Transfer Reference</label>
                                <input type="text" class="form-control" id="manual_reference" name="manual_reference" required>
                            </div>
                            <div class="mb-2">
                                <label for="manual_bank">Bank Used</label>
                                <input type="text" class="form-control" id="manual_bank" name="manual_bank" required>
                            </div>
                            <button type="submit" class="btn btn-success">Submit Manual Funding</button>
                        </form>
                        <small class="text-muted d-block mt-2">After transfer, fill this form. Your wallet will be credited after admin verification.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="wallet-history card">
            <div class="wallet-history-title">Transaction History</div>
            <?php if (empty($transactions)): ?>
                <p>No wallet transactions yet</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="wallet-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td><?php echo date('M j, Y H:i', strtotime($txn['created_at'])); ?></td>
                                    <td><?php echo $txn['reference']; ?></td>
                                    <td>₦<?php echo number_format($txn['amount'], 2); ?></td>
                                    <td><?php echo ucfirst($txn['payment_method']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $txn['status'] == 'successful' ? 'success' : ($txn['status'] == 'failed' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($txn['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="wallet-side">
        <div class="wallet-quick-actions">
            <div class="quick-actions-title">Quick Actions</div>
            <a href="airtime.php" class="btn btn-outline-primary">Buy Airtime</a>
            <a href="data.php" class="btn btn-outline-primary">Buy Data</a>
            <a href="bills.php" class="btn btn-outline-primary">Pay Bills</a>
            <a href="profile.php" class="btn btn-outline-secondary">My Profile</a>
        </div>
        <div class="wallet-referral">
            <div class="referral-title">Referral Program</div>
            <div class="referral-code">Your Referral Code: <strong><?php echo $user['referral_code']; ?></strong></div>
            <div class="referral-desc">Earn ₦200 for every friend who signs up and funds their wallet!</div>
            <div class="referral-link-group">
                <input type="text" id="referralLink" value="<?php echo SITE_URL . '/register.php?ref=' . $user['referral_code']; ?>" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <div class="referral-earnings">Your Referral Earnings: <strong>₦<?php echo number_format($user['referral_balance'], 2); ?></strong></div>
            <?php 
            $stmt = $db->prepare("SELECT r.*, u.name as referred_name 
                                 FROM referrals r 
                                 JOIN users u ON r.referred_id = u.id 
                                 WHERE r.referrer_id = ? 
                                 ORDER BY r.created_at DESC");
            $stmt->execute([$user['id']]);
            $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if (!empty($referrals)): ?>
                <div class="table-responsive">
                    <table class="referral-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Referred User</th>
                                <th>Status</th>
                                <th>Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $ref): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($ref['created_at'])); ?></td>
                                    <td><?php echo $ref['referred_name']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $ref['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($ref['status']); ?>
                                        </span>
                                    </td>
                                    <td>₦<?php echo number_format($ref['bonus_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function copyReferralLink() {
    const link = document.getElementById('referralLink');
    link.select();
    document.execCommand('copy');
    link.style.background = '#e0ffe0';
    setTimeout(() => { link.style.background = ''; }, 1200);
}
</script>
