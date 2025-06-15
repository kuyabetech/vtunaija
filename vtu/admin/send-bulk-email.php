<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';
require_once '../vendor/autoload.php'; // Include email functions if available

$db = DB::getInstance()->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $recipients = $_POST['recipients'] ?? [];

    if (empty($subject) || empty($body) || empty($recipients)) {
        $error = "Please enter a subject, message, and select at least one recipient.";
    } else {
        // Use the sendEmailNotifications function from functions.php
        $result = sendEmailNotifications($recipients, $subject, $body);
        if (is_array($result)) {
            if ($result['sent'] > 0) {
                $success = "Bulk email sent to {$result['sent']} recipients.";
            } else {
                $error = !empty($result['errors']) ? implode('<br>', $result['errors']) : "No emails were sent. Please check your SMTP configuration and credentials.";
            }
        } else if (is_int($result) && $result > 0) {
            $success = "Bulk email sent to $result recipients.";
        } else {
            $error = "No emails were sent. Please check your SMTP configuration and credentials.";
        }
    }
} 

// Fetch all users for selection
$stmt = $db->query("SELECT id, name, email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:700px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Send Bulk Email</h4>
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
                            <option value="<?php echo htmlspecialchars($user['email']); ?>">
                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.</small>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="body" class="form-label">Message</label>
                    <textarea class="form-control" id="body" name="body" rows="6" required><?php echo htmlspecialchars($_POST['body'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Email</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
