<?php
// Ensure NO whitespace or output before this tag in this file and all included files!
// session_start() must be called before any output

require_once '../includes/functions.php'; // Ensure this file contains isAdmin() definition
require_once '../includes/admin-auth.php'; // Ensure this file contains isLoggedIn() definition

// Define isAdmin() if not already defined
if (!function_exists('isAdmin')) {
    function isAdmin() {
        // Example: check if $_SESSION['user_role'] is 'admin'
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
if (!isAdmin()) {
    echo '<div class="container"><div class="alert alert-danger">Access denied.</div></div>';
    include '../includes/admin-footer.php';
    exit();
}
$db = DB::getInstance()->getConnection();
$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'vtu_markup_percentage',
        'min_wallet_fund',
        'max_wallet_fund',
        'support_email',
        'site_name',
        'flutterwave_api_key',
        'termii_api_key',
        'notification_email',
        'notification_sms_sender',
        'frontend_announcement'
    ];
    $set = [];
    $params = [];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $set[] = "$field = ?";
            $params[] = $_POST[$field];
        }
    }

    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $logoDir = '../assets/images/';
            if (!is_dir($logoDir)) {
                mkdir($logoDir, 0777, true);
            }
            $logoName = 'logo_' . time() . '.' . $ext;
            $logoPath = $logoDir . $logoName;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $logoPath)) {
                // Save relative path for frontend use
                $set[] = "site_logo = ?";
                $params[] = 'assets/images/' . $logoName;
            } else {
                $error = 'Failed to upload logo.';
            }
        } else {
            $error = 'Invalid logo format. Only jpg, jpeg, png, gif allowed.';
        }
    }

    if ($set && !$error) {
        // Ensure the settings row exists
        $check = $db->query("SELECT id FROM settings WHERE id = 1")->fetch();
        if (!$check) {
            $db->exec("INSERT INTO settings (id) VALUES (1)");
        }
        $sql = "UPDATE settings SET " . implode(', ', $set) . " WHERE id = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $success = 'Settings updated!';
    }
}

// Fetch all settings (single row)
$stmt = $db->prepare("SELECT * FROM settings LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

require_once '../includes/admin-header.php';
?>
<style>
    .settings-container {
        max-width: 600px;
        margin: 3rem auto 2rem auto;
        background: #fff;
        border-radius: 1.5rem;
        padding: 2.5rem 2rem 2rem 2rem;
        border: 1.5px solid var(--primary);
        color: var(--text);
        font-family: 'Poppins', Arial, sans-serif;
    }
    .settings-container h2 {
        color: var(--primary);
        font-family: 'Press Start 2P', Arial, sans-serif;
        margin-bottom: 2rem;
        font-size: 1.5rem;
        text-align: center;
    }
    .settings-container .form-group label {
        color: var(--primary);
        font-family: 'Press Start 2P', Arial, sans-serif;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .settings-container .form-control {
        border-radius: 8px;
        border: 1px solid var(--primary);
        background: #f9fafb;
        color: var(--text);
        font-size: 1rem;
        margin-bottom: 1.2rem;
        font-family: inherit;
    }
    .settings-container .form-control:focus {
        border-color: var(--primary);
        box-shadow: none;
    }
    .settings-container .btn-primary.btn-powerup {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.7rem 0;
        width: 100%;
        margin-top: 1.2rem;
        font-family: inherit;
        transition: background 0.2s, color 0.2s;
    }
    .settings-container .btn-primary.btn-powerup:hover {
        background: var(--primary-light);
        color: #fff;
    }
    .settings-container img {
        border-radius: 8px;
        background: #fff;
        padding: 4px;
        margin-bottom: 0.7rem;
        border: 1.5px solid var(--primary);
    }
    .alert {
        border-radius: 1rem;
        padding: 1rem 1.2rem;
        margin-bottom: 1.2rem;
        font-size: 1rem;
        font-family: 'Audiowide', Arial, sans-serif;
    }
</style>
<div class="settings-container">
    <h2>Site Settings</h2>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group mb-3">
            <label for="site_name">Site Name</label>
            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="support_email">Support Email</label>
            <input type="email" class="form-control" id="support_email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="site_logo">Site Logo</label><br>
            <?php if (!empty($settings['site_logo'])): ?>
                <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Site Logo" height="48">
            <?php endif; ?>
            <input type="file" class="form-control mt-2" id="site_logo" name="site_logo" accept="image/*">
        </div>
        <div class="form-group mb-3">
            <label for="vtu_markup_percentage">VTU Markup Percentage (%)</label>
            <input type="number" class="form-control" id="vtu_markup_percentage" name="vtu_markup_percentage" value="<?php echo htmlspecialchars($settings['vtu_markup_percentage'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="min_wallet_fund">Minimum Wallet Funding (₦)</label>
            <input type="number" class="form-control" id="min_wallet_fund" name="min_wallet_fund" value="<?php echo htmlspecialchars($settings['min_wallet_fund'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="max_wallet_fund">Maximum Wallet Funding (₦)</label>
            <input type="number" class="form-control" id="max_wallet_fund" name="max_wallet_fund" value="<?php echo htmlspecialchars($settings['max_wallet_fund'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="flutterwave_api_key">Flutterwave API Key</label>
            <input type="text" class="form-control" id="flutterwave_api_key" name="flutterwave_api_key" value="<?php echo htmlspecialchars($settings['flutterwave_api_key'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="termii_api_key">Termii API Key</label>
            <input type="text" class="form-control" id="termii_api_key" name="termii_api_key" value="<?php echo htmlspecialchars($settings['termii_api_key'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="notification_email">Notification Email</label>
            <input type="email" class="form-control" id="notification_email" name="notification_email" value="<?php echo htmlspecialchars($settings['notification_email'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="notification_sms_sender">Notification SMS Sender</label>
            <input type="text" class="form-control" id="notification_sms_sender" name="notification_sms_sender" value="<?php echo htmlspecialchars($settings['notification_sms_sender'] ?? ''); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="frontend_announcement">Frontend Announcement</label>
            <textarea class="form-control" id="frontend_announcement" name="frontend_announcement" rows="2"><?php echo htmlspecialchars($settings['frontend_announcement'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-powerup"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>
<?php include '../includes/admin-footer.php'; ?>

