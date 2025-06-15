<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['crypto_payment_id'])) {
    redirect('wallet-crypto.php');
}

require_once 'classes/CryptoPayment.php';
$crypto = new CryptoPayment();
$payment = $crypto->checkPaymentStatus($_SESSION['crypto_payment_id']);

// Auto-refresh every 30 seconds if still pending
$metaRefresh = $payment['status'] == 'pending' ? '<meta http-equiv="refresh" content="30">' : '';

include 'includes/header.php';
?>

<div class="container crypto-container" style="display:grid;place-items:center;min-height:60vh;">
    <div class="crypto-card" style="background:var(--card-bg,#fff);border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.08),0 2px 4px -1px rgba(0,0,0,0.04);padding:2rem 1.5rem;max-width:420px;width:100%;">
        <div class="crypto-title" style="color:var(--primary,#2563eb);font-size:1.3rem;font-weight:600;margin-bottom:1.2rem;text-align:center;">
            Complete Cryptocurrency Payment
        </div>
        <div class="crypto-status-body text-center">
            <?php if ($payment['status'] == 'pending'): ?>
                <h5 style="color:var(--warning,#f59e0b);font-family:'Inter',Arial,sans-serif;">Send <?php echo $payment['amount_crypto']; ?> <?php echo $payment['currency']; ?></h5>
                <p style="color:var(--text-light,#6b7280);">to the following address:</p>
                <div class="mb-4">
                    <img src="<?php echo htmlspecialchars($payment['qr_code_url'] ?? ''); ?>" alt="Payment QR Code" class="img-fluid mb-3" style="box-shadow:0 0 16px var(--primary,#2563eb);border-radius:1.2rem;background:#fff;padding:8px;">
                    <div style="display:grid;grid-template-columns:1fr auto;gap:0.5rem;margin-bottom:1rem;">
                        <input type="text" class="form-control text-center font-monospace" id="walletAddress" value="<?php echo $payment['wallet_address']; ?>" readonly style="background:#f3f4f6;color:var(--primary,#2563eb);border:1px solid var(--border,#e5e7eb);border-radius:8px;font-family:'Inter',Arial,sans-serif;">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWalletAddress()" style="background:var(--primary,#2563eb);color:#fff;border:none;border-radius:8px;font-weight:600;">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="alert alert-info" style="background:#f3f4f6;color:var(--primary,#2563eb);border:1px solid var(--primary,#2563eb);border-radius:8px;">
                    <p>After sending, please wait for blockchain confirmation (usually takes 5-30 minutes).</p>
                    <p>This page will automatically refresh to check for your payment.</p>
                </div>
                <a href="wallet.php" class="btn btn-outline-secondary" style="background:#fff;color:var(--primary,#2563eb);border:1px solid var(--primary,#2563eb);border-radius:8px;">Cancel</a>
            <?php elseif ($payment['status'] == 'confirmed'): ?>
                <div class="alert alert-success" style="background:#ecfdf5;color:var(--success,#10b981);border-radius:8px;">
                    <h4><i class="fas fa-check-circle"></i> Payment Confirmed!</h4>
                    <p>Your wallet has been credited with ₦<?php echo number_format($payment['amount_ngn'], 2); ?>.</p>
                    <p>Transaction ID: <?php echo $payment['tx_hash']; ?></p>
                </div>
                <a href="wallet.php" class="btn btn-primary btn-powerup" style="background:var(--primary,#2563eb);border-radius:8px;">Back to Wallet</a>
            <?php else: ?>
                <div class="alert alert-danger" style="background:#fee2e2;color:var(--danger,#ef4444);border-radius:8px;">
                    <h4><i class="fas fa-exclamation-circle"></i> Payment Failed</h4>
                    <p>Please try again or contact support.</p>
                </div>
                <a href="wallet-crypto.php" class="btn btn-primary btn-powerup" style="background:var(--primary,#2563eb);border-radius:8px;">Try Again</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function copyWalletAddress() {
    const input = document.getElementById('walletAddress');
    input.select();
    document.execCommand('copy');
    input.style.background = '#e0ffe0';
    setTimeout(() => { input.style.background = '#f3f4f6'; }, 1200);
}
</script>

<?php include 'includes/spinner.php'; ?>