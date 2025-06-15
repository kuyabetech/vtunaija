<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $user = null;
    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required';
    } else {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            // 2FA check
            requireTwoFactor($user);
            // If no 2FA, proceed as normal
            $_SESSION['user_id'] = $user['id'];
            redirect('dashboard.php');
        } else {
            $error = 'Invalid email or password';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - VTU Naija</title>
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
.login-container {
  max-width: 420px;
  margin: 3rem auto 2rem auto;
}
.login-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(37,99,235,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  color: #1f2937;
  font-family: 'Inter', Arial, sans-serif;
}
.login-title {
  color: #2563eb;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
}
.login-card label {
  color: #2563eb;
  font-family: inherit;
  font-weight: 600;
}
.login-card .form-control {
  margin-bottom: 0.7rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #1f2937;
  font-family: inherit;
  font-size: 1rem;
  margin
  padding: 0.8rem 1.2rem;
  transition: border-color 0.2s;
}
.login-card .form-control:focus {
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
.text-center {
  text-align: center;
}
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="login-card">
        <div class="login-title"><i class="fas fa-sign-in-alt" style="color:#7c3aed;margin-right:8px;"></i>Login to Your Account</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group form-check" style="margin-bottom:0.7rem;">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary btn-powerup" style="width:100%;margin-top:0.7rem;"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="forgot-password.php" style="color:#2563eb;">Forgot Password?</a>
            <p class="mt-2">Don't have an account? <a href="register.php" style="color:#7c3aed;">Register here</a></p>
        </div>
    </div>
</div>
<?php include 'includes/spinner.php'; ?>
</body>
</html>