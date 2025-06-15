<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
// Bill Payment Page - Retro Tech Arcade Theme

// Backend logic for bill payment
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_type = $_POST['bill_type'] ?? '';
    $account = $_POST['account'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $provider = $_POST['provider'] ?? '';
    if (!$bill_type || !$account || !$amount || !$provider) {
        $error = 'All fields are required.';
    } elseif (!is_numeric($amount) || $amount < 100) {
        $error = 'Amount must be at least ₦100.';
    } else {
        // Simulate bill payment process (replace with real API call)
        // Deduct from wallet if user is logged in
        if (isLoggedIn()) {
            $db = DB::getInstance()->getConnection();
            $user = getUserById($_SESSION['user_id']);
            if ($user['wallet_balance'] < $amount) {
                $error = 'Insufficient wallet balance.';
            } else {
                $newBalance = $user['wallet_balance'] - $amount;
                $stmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
                $stmt->execute([$newBalance, $user['id']]);
                // Record transaction
                $stmt = $db->prepare("INSERT INTO vtu_transactions (user_id, service_type, network, phone, amount, reference, status) VALUES (?, 'bills', ?, ?, ?, ?, 'successful')");
                $reference = 'BILL_' . time() . '_' . uniqid();
                $stmt->execute([$user['id'], $provider, $account, $amount, $reference]);
                $success = 'Bill payment successful!';
            }
        } else {
            $error = 'You must be logged in to pay bills.';
        }
    }
}
?>
<div class="container bill-container">
    <div class="bill-card">
        <div class="bill-title"><i class="fas fa-file-invoice-dollar" style="color:#2563eb;margin-right:8px;"></i>Pay Your Bills</div>
        <?php if ($error): ?><div class="alert alert-danger" style="background:#fee2e2;color:#ef4444;border-radius:8px;padding:1rem 1.2rem;"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success" style="background:#ecfdf5;color:#10b981;border-radius:8px;padding:1rem 1.2rem;"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="bill_type">Bill Type</label>
                <select class="form-control" id="bill_type" name="bill_type" required>
                    <option value="">Select Bill Type</option>
                    <option value="electricity">Electricity</option>
                    <option value="cable">Cable TV</option>
                    <option value="water">Water</option>
                    <option value="education">Education</option>
                    <option value="tax">Tax</option>
                    <option value="internet">Internet</option>
                </select>
            </div>
            <div class="form-group">
                <label for="account">Account/Customer Number</label>
                <input type="text" class="form-control" id="account" name="account" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount (₦)</label>
                <input type="number" class="form-control" id="amount" name="amount" min="100" required>
            </div>
            <div class="form-group">
                <label for="provider">Provider</label>
                <select class="form-control" id="provider" name="provider" required>
                    <option value="">Select Provider</option>
                    <option value="eko">Eko Electric</option>
                    <option value="ikeja">Ikeja Electric</option>
                    <option value="dstv">DSTV</option>
                    <option value="gotv">GOTV</option>
                    <option value="startimes">Startimes</option>
                    <option value="swift">Swift Internet</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-powerup"><i class="fas fa-bolt"></i> Pay Now</button>
        </form>
    </div>
</div>
<style>
.bill-container {
  max-width: 480px;
  margin: 3rem auto 2rem auto;
}
.bill-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(37,99,235,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  color: #1f2937;
  font-family: 'Inter', Arial, sans-serif;
  width: 400px;
}
.bill-title {
  color: #2563eb;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
}
.form-group label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
  color: #2563eb;
}
.form-control {
  width: 100%;
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #1f2937;
  font-size: 1rem;
  margin-bottom: 1.2rem;
  box-sizing: border-box;
  transition: border 0.2s;
  font-family: inherit;
}
.form-control:focus {
  border-color: #2563eb;
  outline: none;
}
.btn-primary.btn-powerup {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1.1rem;
  padding: 0.7rem 0;
  width: 100%;
  margin-top: 0.5rem;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.1s;
  font-family: inherit;
  box-shadow: 0 2px 8px #2563eb33;
}
.btn-primary.btn-powerup:hover {
  background: #3b82f6;
  color: #fff;
  box-shadow: 0 0 24px #2563eb33;
  transform: translateY(-2px);
}
</style>

<?php include 'includes/spinner.php'; ?>
