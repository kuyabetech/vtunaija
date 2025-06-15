<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
    exit();
}

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'classes/VtuService.php';
    
    $network = sanitizeInput($_POST['network']);
    $phone = sanitizeInput($_POST['phone']);
    $amount = sanitizeInput($_POST['amount']);
    
    if (empty($network) || empty($phone) || empty($amount)) {
        $error = 'All fields are required';
    } elseif (!is_numeric($amount) || $amount < 50) {
        $error = 'Amount must be at least ₦50';
    } else {
        $vtu = new VtuService();
        $result = $vtu->buyAirtime($network, $phone, $amount, $user['id']);
        
        if ($result['status']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>
<div class="container airtime-container">
    <div class="airtime-card">
        <div class="airtime-title">Buy Airtime</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="network">Network</label>
                <select class="form-control" id="network" name="network" required>
                    <option value="">Select Network</option>
                    <option value="MTN">MTN</option>
                    <option value="GLO">Glo</option>
                    <option value="AIRTEL">Airtel</option>
                    <option value="9MOBILE">9mobile</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount (₦)</label>
                <input type="number" class="form-control" id="amount" name="amount" min="50" required>
            </div>
            <div class="form-group">
                <p>Wallet Balance: <strong>₦<?php echo number_format($user['wallet_balance'], 2); ?></strong></p>
            </div>
            <button type="submit" class="btn btn-primary btn-powerup">Buy Airtime</button>
        </form>
    </div>
</div>
<style>
.airtime-container {
  max-width: 420px;
  margin: 3rem auto 2rem auto;
}
.airtime-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(37,99,235,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  color: #1f2937;
  font-family: 'Inter', Arial, sans-serif;
}
.airtime-title {
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
.alert {
  border-radius: 8px;
  padding: 1rem 1.2rem;
  margin-bottom: 1.2rem;
  font-size: 1rem;
  font-family: inherit;
}
.alert-danger {
  background: #fee2e2;
  color: #ef4444;
}
.alert-success {
  background: #ecfdf5;
  color: #10b981;
}
</style>
<?php include 'includes/spinner.php'; ?>
