<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $currency = sanitizeInput($_POST['currency']);
    
    if ($amount < 5000) {
        $error = 'Minimum cryptocurrency deposit is ₦5,000';
    } else {
        require_once 'classes/CryptoPayment.php';
        $crypto = new CryptoPayment();
        
        try {
            $payment = $crypto->createPaymentRequest($user['id'], $amount, $currency);
            $_SESSION['crypto_payment_id'] = $payment['id'];
            redirect('wallet-crypto-process.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container crypto-container">
    <div class="crypto-card">
        <div class="crypto-title">Fund Wallet with Cryptocurrency</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="amount">Amount in Naira (₦)</label>
                <input type="number" class="form-control" id="amount" name="amount" min="5000" step="100" required>
            </div>
            <div class="form-group">
                <label for="currency">Cryptocurrency</label>
                <select class="form-control" id="currency" name="currency" required>
                    <option value="USDT">Tether (USDT)</option>
                    <option value="BTC">Bitcoin (BTC)</option>
                    <option value="ETH">Ethereum (ETH)</option>
                </select>
            </div>
            <div class="form-group">
                <p class="crypto-note">Exchange rates are calculated at the time of transaction.</p>
            </div>
            <button type="submit" class="btn btn-primary btn-powerup">Continue</button>
        </form>
    </div>
</div>

<style>
.crypto-container {
  max-width: 420px;
  margin: 3rem auto 2rem auto;
}
.crypto-card {
  background: #181c3a;
  border-radius: 1.5rem;
  box-shadow: 0 2px 12px #FF007F;
  padding: 2.5rem 2rem 2rem 2rem;
  color: #C0C0C0;
  font-family: 'Audiowide', 'Press Start 2P', Arial, sans-serif;
}
.crypto-title {
  color: #FFA500;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
  font-family: 'Press Start 2P', Arial, sans-serif;
}
.form-group label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
  color: #FFA500;
  font-family: 'Press Start 2P', Arial, sans-serif;
}
.form-control {
  width: 100%;
  padding: 0.8rem 1.2rem;
  border-radius: 1.2rem;
  border: 2px solid #FFA500;
  background: #181c3a;
  color: #C0C0C0;
  font-size: 1rem;
  margin-bottom: 1.2rem;
  box-sizing: border-box;
  transition: border 0.2s;
  font-family: 'Audiowide', Arial, sans-serif;
}
.form-control:focus {
  border-color: #FF007F;
  outline: none;
}
.btn-primary.btn-powerup {
  background: #FFA500;
  color: #050A30;
  border: 2px solid #FF007F;
  border-radius: 2rem;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 0.7rem 0;
  width: 100%;
  margin-top: 0.5rem;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.1s;
  font-family: 'Press Start 2P', Arial, sans-serif;
  box-shadow: 0 2px 8px #FF007F;
}
.btn-primary.btn-powerup:hover {
  background: #FF007F;
  color: #fff;
  box-shadow: 0 0 24px #FFA500;
  transform: scale(1.05) rotate(-2deg);
}
.crypto-note {
  color: #C0C0C0;
  font-size: 0.97rem;
  margin-bottom: 0.7rem;
}
.alert {
  border-radius: 1rem;
  padding: 1rem 1.2rem;
  margin-bottom: 1.2rem;
  font-size: 1rem;
  font-family: 'Audiowide', Arial, sans-serif;
}
.alert-danger {
  background: #ffdddd;
  color: #a94442;
}
.alert-success {
  background: #ddffdd;
  color: #228B22;
}
</style>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/spinner.php'; ?>
