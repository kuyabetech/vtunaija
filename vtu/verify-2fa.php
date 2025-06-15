<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['2fa_required']) || !isset($_SESSION['2fa_user_id']) || empty($_SESSION['2fa_user_id'])) {
    redirect('login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = sanitizeInput($_POST['code']);
    
    if (empty($code)) {
        $error = 'Please enter your 2FA code';
    } elseif (verifyTwoFactorCode($_SESSION['2fa_user_id'], $code)) {
        $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
        unset($_SESSION['2fa_required'], $_SESSION['2fa_user_id']);
        redirect('dashboard.php');
    } else {
        $error = 'Invalid verification code';
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Two-Factor Authentication</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <p>Please enter the 6-digit code from your authenticator app:</p>
                    
                    <form method="POST">
                        <div class="form-group mb-3">
                            <label for="code">Verification Code</label>
                            <input type="text" class="form-control" id="code" name="code"
                                   pattern="[0-9]{6}" maxlength="6" required autofocus
                                   style="font-size:1.5rem;letter-spacing:0.5rem;text-align:center;width:100%;max-width:220px;margin:0 auto;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100" style="font-weight:700;font-size:1.1rem;">Verify</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>