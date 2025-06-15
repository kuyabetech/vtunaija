<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Debug: check if email exists in DB
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = getUserByEmail($email);
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db = DB::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            // Send reset email using PHPMailer if available
            $resetLink = SITE_URL . "/reset-password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Hello,<br><br>To reset your password, click the link below:<br><a href=\"$resetLink\">$resetLink</a><br><br>If you did not request this, please ignore this email.";
            if (function_exists('sendEmailNotification')) {
                sendEmailNotification($user['email'], $subject, $message);
            } else {
                // fallback to mail()
                $plainMsg = strip_tags(str_replace("<br>", "\n", $message));
                mail($user['email'], $subject, $plainMsg);
            }
            $success = 'A password reset link has been sent to your email address.';
        } else {
            $error = 'No account found with that email address.';
        }
    }
}

include 'includes/header.php';
?>
<div class="container" style="max-width:420px;margin:3rem auto 2rem auto;">
    <div class="card" style="background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(37,99,235,0.08);">
        <div class="card-header" style="background:#2563eb;color:#fff;border-radius:12px 12px 0 0;">
            <h4 style="margin-bottom:0;">Forgot Password</h4>
        </div>
        <div class="card-body" style="padding:2rem;">
            <?php if ($success): ?>
                <div class="alert alert-success" style="background:#ecfdf5;color:#10b981;border-radius:8px;padding:1rem 1.2rem;"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger" style="background:#fee2e2;color:#ef4444;border-radius:8px;padding:1rem 1.2rem;"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group mb-3">
                    <label for="email" style="font-weight:600;color:#2563eb;">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus style="border-radius:8px;border:1px solid #e5e7eb;padding:0.7rem 1.2rem;">
                </div>
                <button type="submit" class="btn btn-primary w-100" style="background:#2563eb;border-radius:8px;font-weight:600;">Send Reset Link</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" style="color:#2563eb;">Back to Login</a>
            </div>
        </div>
    </div>
</div>

