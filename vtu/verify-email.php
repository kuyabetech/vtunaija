<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$success = '';
$error = '';

if (!$token) {
    $error = "Invalid verification link.";
} else {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, email_verified FROM users WHERE email_verification_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Invalid or expired verification link.";
    } elseif ($user['email_verified']) {
        $success = "Your email is already verified. You can log in.";
    } else {
        // Mark email as verified
        $stmt = $db->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        $success = "Your email has been successfully verified! You can now log in.";
    }
}

include 'includes/header.php';
?>
<div class="container" style="max-width:500px;margin:3rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Email Verification</h4>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="login.php" class="btn btn-primary">Login</a>
            <?php else: ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>