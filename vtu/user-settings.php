<?php
require_once 'includes/header.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
$db = DB::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
// Handle user settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notify_email = isset($_POST['notify_email']) ? 1 : 0;
    $notify_sms = isset($_POST['notify_sms']) ? 1 : 0;
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    $stmt = $db->prepare("UPDATE users SET notify_email = ?, notify_sms = ?, dark_mode = ?, enable_2fa = ? WHERE id = ?");
    $stmt->execute([$notify_email, $notify_sms, $dark_mode, $enable_2fa, $user_id]);
    $success = 'Settings updated!';
}
// Fetch user settings
$stmt = $db->prepare("SELECT notify_email, notify_sms, dark_mode, enable_2fa FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="container" style="max-width:480px;margin:3rem auto 2rem auto;">
    <div class="settings-card" style="background:#181c3a;border-radius:1.5rem;box-shadow:0 2px 12px #FF007F;padding:2.5rem 2rem 2rem 2rem;color:#C0C0C0;font-family:'Audiowide','Press Start 2P',Arial,sans-serif;">
        <div class="settings-title" style="color:#FFA500;font-size:1.5rem;font-weight:700;margin-bottom:1.5rem;text-align:center;font-family:'Press Start 2P',Arial,sans-serif;"><i class="fas fa-cog" style="color:#FF007F;margin-right:8px;"></i>User Settings</div>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group form-check" style="margin-bottom:1rem;">
                <input type="checkbox" class="form-check-input" id="notify_email" name="notify_email" <?php if(!empty($settings['notify_email'])) echo 'checked'; ?>>
                <label class="form-check-label" for="notify_email">Email Notifications</label>
            </div>
            <div class="form-group form-check" style="margin-bottom:1rem;">
                <input type="checkbox" class="form-check-input" id="notify_sms" name="notify_sms" <?php if(!empty($settings['notify_sms'])) echo 'checked'; ?>>
                <label class="form-check-label" for="notify_sms">SMS Notifications</label>
            </div>
            <div class="form-group form-check" style="margin-bottom:1rem;">
                <input type="checkbox" class="form-check-input" id="dark_mode" name="dark_mode" <?php if(!empty($settings['dark_mode'])) echo 'checked'; ?>>
                <label class="form-check-label" for="dark_mode">Enable Dark Mode</label>
            </div>
            <div class="form-group form-check" style="margin-bottom:1rem;">
                <input type="checkbox" class="form-check-input" id="enable_2fa" name="enable_2fa" <?php if(!empty($settings['enable_2fa'])) echo 'checked'; ?>>
                <label class="form-check-label" for="enable_2fa">Enable Two-Factor Authentication (2FA)</label>
            </div>
            <button type="submit" class="btn btn-primary btn-powerup" style="width:100%;margin-top:0.7rem;"><i class="fas fa-save"></i> Save Settings</button>
        </form>
    </div>
</div>
<?php include 'includes/spinner.php'; ?>
<?php include 'includes/chat-widget.php'; ?>
<style>
:root {
  --primary: #2563eb;
  --primary-light: #3b82f6;
  --secondary: #059669;
  --accent: #7c3aed;
  --text: #1f2937;
  --text-light: #6b7280;
  --bg: #f9fafb;
  --card-bg: #ffffff;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --border: #e5e7eb;
}
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: var(--bg);
  color: var(--text);
  margin: 0;
  padding: 0;
  line-height: 1.5;
}
.settings-card {
  background: var(--card-bg)!important;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04)!important;
  padding: 2.5rem 2rem 2rem 2rem;
  color: var(--text);
  font-family: 'Inter', Arial, sans-serif;
  border-left: 4px solid var(--primary);
}
.settings-title {
  color: var(--primary)!important;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
  font-family: 'Inter', Arial, sans-serif;
}
.form-check-input {
  accent-color: var(--primary);
  width: 1.2em;
  height: 1.2em;
  margin-right: 0.5em;
}
.form-check-label {
  color: var(--text-light);
  font-size: 1rem;
  font-weight: 500;
}
.btn-powerup {
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  padding: 0.7rem 1.5rem;
  transition: background 0.2s;
}
.btn-powerup:hover {
  background: var(--primary-light);
}
.alert-success {
  background: #ecfdf5;
  color: var(--success);
  border-radius: 8px;
  border: none;
  font-weight: 600;
  margin-bottom: 1rem;
  padding: 1rem 1.2rem;
}
@media (max-width: 600px) {
  .settings-card { padding: 1.2rem 0.7rem; }
}
</style>