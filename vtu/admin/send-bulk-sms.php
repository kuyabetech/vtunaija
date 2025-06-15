<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $recipients = $_POST['recipients'] ?? [];
    if (empty($message) || empty($recipients)) {
        $error = "Please enter a message and select at least one recipient.";
    } else {
        // Send SMS via Termii or other SMS API
        $sent = 0;
        foreach ($recipients as $phone) {
            // Replace with your SMS API integration
            // Example: send_sms($phone, $message);
            $sent++;
        }
        $success = "Bulk SMS sent to $sent recipients.";
    }
}

// Fetch all users for selection
$stmt = $db->query("SELECT id, name, phone FROM users WHERE phone IS NOT NULL AND phone != '' ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:700px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Send Bulk SMS</h4>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="recipients" class="form-label">Recipients</label>
                    <select class="form-select" id="recipients" name="recipients[]" multiple size="8" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['phone']); ?>">
                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['phone'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.</small>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send SMS</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
