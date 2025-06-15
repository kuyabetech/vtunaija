<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'includes/functions.php'; // Ensure sendEmailNotification() is available

// Ensure user is logged in
if (!isLoggedIn() || !isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/header.php';

$db = DB::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$name || !$email || !$phone) {
        $error = 'All fields are required.';
    } else {
        // Check for duplicate email (exclude current user)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Email address is already in use by another account.';
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $user_id]);
            $success = 'Profile updated successfully!';
            $user = getUserById($user_id);
        }
    }
}

// Handle email verification request
if (isset($_POST['send_verification'])) {
    $verification_code = bin2hex(random_bytes(16));
    $stmt = $db->prepare("UPDATE users SET email_verification_code = ? WHERE id = ?");
    $stmt->execute([$verification_code, $user_id]);
    $verify_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify-email.php?code=$verification_code";
    $subject = 'Verify Your Email Address';
    $body = "<p>Hi {$user['name']},</p><p>Please verify your email by clicking the link below:</p><p><a href='$verify_link'>$verify_link</a></p>";

    // Try to send email using sendEmailNotification if available
    $mailSent = false;
    if (function_exists('sendEmailNotification')) {
        $mailSent = sendEmailNotification($user['email'], $subject, $body);
    }
    if ($mailSent) {
        $success = "A verification email has been sent to your address.";
    } else {
        $success = "Verification email could not be sent by the server. Here is your verification link: <br><a href='$verify_link'>$verify_link</a>";
    }
    $user = getUserById($user_id);
}

// Handle profile picture upload
if (isset($_POST['upload_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
        $targetDir = 'assets/images/avatars/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $target = $targetDir . $filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$target, $user_id]);
            $success = 'Profile picture updated!';
            $user = getUserById($user_id);
        } else {
            $error = 'Failed to upload image.';
        }
    } else {
        $error = 'Invalid image format. Only jpg, jpeg, png, gif allowed.';
    }
}
// Handle KYC upload (BVN/NIN)
if (isset($_POST['upload_kyc'])) {
    $bvn = trim($_POST['bvn'] ?? '');
    $nin = trim($_POST['nin'] ?? '');
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = isset($_FILES['kyc_doc']['name']) ? strtolower(pathinfo($_FILES['kyc_doc']['name'], PATHINFO_EXTENSION)) : '';
    if (!$bvn && !$nin) {
        $error = 'Please provide either BVN or NIN.';
    } elseif ($bvn && !preg_match('/^[0-9]{11}$/', $bvn)) {
        $error = 'Invalid BVN. It must be 11 digits.';
    } elseif ($nin && !preg_match('/^[0-9]{11}$/', $nin)) {
        $error = 'Invalid NIN. It must be 11 digits.';
    } elseif (!empty($_FILES['kyc_doc']['name']) && !in_array($ext, $allowed)) {
        $error = 'Invalid KYC format. Only jpg, jpeg, png, pdf allowed.';
    } else {
        $filename = '';
        if (!empty($_FILES['kyc_doc']['name'])) {
            $filename = 'kyc_' . $user_id . '_' . time() . '.' . $ext;
            $targetDir = 'assets/kyc/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $target = $targetDir . $filename;
            if (!move_uploaded_file($_FILES['kyc_doc']['tmp_name'], $target)) {
                $error = 'Failed to upload KYC document.';
                $target = '';
            }
        } else {
            $target = '';
        }
        if (!$error) {
            $stmt = $db->prepare("UPDATE users SET bvn = ?, nin = ?, kyc_doc = ? WHERE id = ?");
            $stmt->execute([$bvn, $nin, $target, $user_id]);
            $success = 'KYC information updated!';
            $user = getUserById($user_id);
        }
    }
}
?>
<div class="profile-container">
  <div class="profile-card">
    <h1 class="profile-title">
      <i class="fas fa-user"></i> My Profile
    </h1>
    
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" 
               value="<?php echo htmlspecialchars($user['name']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" 
               value="<?php echo htmlspecialchars($user['email']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" 
               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
      </div>
      
      <?php if (empty($user['email_verified_at'])): ?>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i> Your email is not verified.
          <button type="submit" name="send_verification" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
            <i class="fas fa-envelope"></i> Send Verification Email
          </button>
        </div>
      <?php else: ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> Email verified
        </div>
      <?php endif; ?>
      
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-save"></i> Update Profile
      </button>
    </form>
    
    <div class="profile-section">
      <div class="profile-avatar">
        <img src="<?php echo getUserAvatar($user); ?>" alt="Profile Photo" class="avatar-img">
        <form method="post" enctype="multipart/form-data" class="avatar-upload">
          <input type="file" name="avatar" accept="image/*">
          <button type="submit" name="upload_avatar" class="btn btn-primary btn-sm">
            <i class="fas fa-upload"></i> Upload New Photo
          </button>
        </form>
      </div>
    </div>
    
    <div class="profile-section">
      <div class="kyc-section">
        <h3 class="profile-info-label">KYC Verification</h3>
        
        <?php if (!empty($user['kyc_doc'])): ?>
          <div class="profile-info">
            <span class="kyc-status kyc-verified">Verified</span>
            <a href="<?php echo $user['kyc_doc']; ?>" target="_blank" class="btn btn-sm" style="margin-left: 0.5rem;">
              <i class="fas fa-eye"></i> View Document
            </a>
          </div>
        <?php else: ?>
          <div class="profile-info">
            <span class="kyc-status kyc-unverified">Not Verified</span>
          </div>
          
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="bvn">BVN (11 digits)</label>
              <input type="text" class="form-control" id="bvn" name="bvn" 
                     value="<?php echo htmlspecialchars($user['bvn'] ?? ''); ?>" 
                     maxlength="11" pattern="[0-9]{11}">
            </div>
            
            <div class="form-group">
              <label for="nin">NIN (11 digits)</label>
              <input type="text" class="form-control" id="nin" name="nin" 
                     value="<?php echo htmlspecialchars($user['nin'] ?? ''); ?>" 
                     maxlength="11" pattern="[0-9]{11}">
            </div>
            
            <div class="form-group">
              <label for="kyc_doc">KYC Document</label>
              <input type="file" class="form-control" id="kyc_doc" name="kyc_doc" 
                     accept="image/*,.pdf">
            </div>
            
            <button type="submit" name="upload_kyc" class="btn btn-primary btn-block">
              <i class="fas fa-id-card"></i> Submit KYC
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="profile-section">
      <div class="profile-info">
        <div class="profile-info-label">Referral Code</div>
        <div class="profile-info-value" style="display: flex; align-items: center; gap: 0.5rem;">
          <span style="font-weight: 600;"><?php echo $user['referral_code']; ?></span>
          <button onclick="copyReferralCode()" class="btn btn-sm">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
      </div>
      
      <div class="profile-info">
        <div class="profile-info-label">Wallet Balance</div>
        <div class="profile-info-value" style="font-weight: 600; color: #2563eb;">
          ₦<?php echo number_format($user['wallet_balance'], 2); ?>
        </div>
      </div>
      
      <div class="profile-info">
        <div class="profile-info-label">Referral Earnings</div>
        <div class="profile-info-value" style="font-weight: 600; color: #2563eb;">
          ₦<?php echo number_format($user['referral_balance'], 2); ?>
        </div>
      </div>
    </div>
    
    <div class="profile-section">
      <a href="change-password.php" class="btn btn-primary btn-block">
        <i class="fas fa-key"></i> Change Password
      </a>
    </div>
  </div>
</div>

<script>
function copyReferralCode() {
  const temp = document.createElement('input');
  temp.value = '<?php echo $user['referral_code']; ?>';
  document.body.appendChild(temp);
  temp.select();
  document.execCommand('copy');
  document.body.removeChild(temp);
  
  // Show a nice toast notification instead of alert
  const toast = document.createElement('div');
  toast.textContent = 'Referral code copied!';
  toast.style.position = 'fixed';
  toast.style.bottom = '20px';
  toast.style.left = '50%';
  toast.style.transform = 'translateX(-50%)';
  toast.style.backgroundColor = '#2563eb';
  toast.style.color = 'white';
  toast.style.padding = '10px 20px';
  toast.style.borderRadius = '8px';
  toast.style.zIndex = '1000';
  toast.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
  
  document.body.appendChild(toast);
  setTimeout(() => {
    document.body.removeChild(toast);
  }, 2000);
}

<?php include 'includes/spinner.php'; ?>
<?php include 'includes/chat-widget.php'; ?>
