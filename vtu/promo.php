<?php
require_once 'includes/header.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
$db = DB::getInstance()->getConnection();
$success = '';
$error = '';
// Handle promo code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $promo_code = trim($_POST['promo_code']);
    if (!$promo_code) {
        $error = 'Please enter a promo code.';
    } else {
        // Check if promo code exists and is valid
        $stmt = $db->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())");
        $stmt->execute([$promo_code]);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$promo) {
            $error = 'Invalid or expired promo code.';
        } else {
            // Check if user has already used this promo
            $stmt = $db->prepare("SELECT * FROM promo_usages WHERE user_id = ? AND promo_id = ?");
            $stmt->execute([$_SESSION['user_id'], $promo['id']]);
            if ($stmt->fetch()) {
                $error = 'You have already used this promo code.';
            } else {
                // Apply promo (e.g., add bonus to wallet)
                $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$promo['bonus_amount'], $_SESSION['user_id']]);
                // Record usage
                $stmt = $db->prepare("INSERT INTO promo_usages (user_id, promo_id, used_at) VALUES (?, ?, NOW())");
                $stmt->execute([$_SESSION['user_id'], $promo['id']]);
                $success = 'Promo code applied! You received ₦' . number_format($promo['bonus_amount'], 2) . '.';
            }
        }
    }
}
?>
<div class="container" style="max-width:420px;margin:3rem auto 2rem auto;">
    <div class="promo-card" style="background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(37,99,235,0.08);padding:2.5rem 2rem 2rem 2rem;color:#1f2937;font-family:'Inter',Arial,sans-serif;">
        <div class="promo-title" style="color:#2563eb;font-size:1.5rem;font-weight:700;margin-bottom:1.5rem;text-align:center;"><i class="fas fa-gift" style="color:#7c3aed;margin-right:8px;"></i>Enter Promo Code</div>
        <?php if ($error): ?><div class="alert alert-danger" style="background:#fee2e2;color:#ef4444;border-radius:8px;padding:1rem 1.2rem;"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success" style="background:#ecfdf5;color:#10b981;border-radius:8px;padding:1rem 1.2rem;"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="promo_code" style="font-weight:600;color:#2563eb;">Promo Code</label>
                <input type="text" class="form-control" id="promo_code" name="promo_code" placeholder="Enter promo code" required style="border-radius:8px;border:1px solid #e5e7eb;padding:0.7rem 1.2rem;">
            </div>
            <button type="submit" class="btn btn-primary btn-powerup" style="width:100%;margin-top:0.7rem;background:#2563eb;border-radius:8px;font-weight:600;"><i class="fas fa-gift"></i> Apply Promo</button>
        </form>
    </div>
</div>

<?php include 'includes/spinner.php'; ?>
<?php include 'includes/chat-widget.php'; ?>
