<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    $referral = isset($_POST['referral']) ? trim(sanitizeInput($_POST['referral'])) : '';

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        $db = DB::getInstance()->getConnection();

        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered';
        } else {
            // Check if phone exists
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);

            if ($stmt->rowCount() > 0) {
                $error = 'Phone number already registered';
            } else {
                // Create account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");

                if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
                    $user_id = $db->lastInsertId();

                    // Log user in
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $email;

                    // Send welcome email (optional)
                    // sendWelcomeEmail($email, $name);

                    // Generate referral code
                    $referral_code = generateReferralCode();
                    $stmt2 = $db->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
                    $stmt2->execute([$referral_code, $user_id]);

                    // Process referral if exists
                    if (!empty($referral)) {
                        // Find referrer by referral_code
                        $stmt3 = $db->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
                        $stmt3->execute([$referral]);
                        $referrer = $stmt3->fetch(PDO::FETCH_ASSOC);
                        if ($referrer) {
                            // Save referral relationship
                            $stmt4 = $db->prepare("INSERT INTO referrals (referrer_id, referred_id, created_at) VALUES (?, ?, NOW())");
                            $stmt4->execute([$referrer['id'], $user_id]);
                            // Optionally: reward referrer, etc.
                        }
                    }

                    $success = 'Registration successful! Redirecting...';
                    header("Refresh: 2; url=dashboard.php");
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}


?>

        <style>
            /* Retro Tech Arcade Theme for Register Page */
            .register-container {
              max-width: 420px;
              margin: 3rem auto 2rem auto;
              background: #fff;
              border-radius: 12px;
              box-shadow: 0 4px 16px rgba(37,99,235,0.08);
              padding: 2.5rem 2rem 2rem 2rem;
              color: #1f2937;
              font-family: 'Inter', Arial, sans-serif;
            }
            .register-title {
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
            .btn-primary {
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
            .btn-primary:hover {
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
            .text-center {
              text-align: center;
            }
            .mt-3 { margin-top: 1.5rem; }
        </style>
    </head>
</body>
<div class="register-container">
  <div class="register-title">Create Your Account</div>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="tel" class="form-control" id="phone" name="phone" required>
    </div>
    <div class="form-group">
      <label for="referral">Referral Code (optional)</label>
      <input type="text" class="form-control" id="referral" name="referral" value="<?php echo htmlspecialchars($_POST['referral'] ?? ''); ?>">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" class="form-control" id="password" name="password" minlength="8" required>
    </div>
    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
  </form>
  <div class="text-center mt-3">
    <p>Already have an account? <a href="login.php" style="color:#2563eb;">Login here</a></p>
  </div>
</div>

<?php include 'includes/spinner.php'; ?>
