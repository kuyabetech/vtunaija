<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $audience = $_POST['audience'] ?? 'all';

    if (empty($title) || empty($message)) {
        $error = "Please enter a title and message.";
    } else {
        // Save notification(s) to the database
        if ($audience === 'all') {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (NULL, ?, ?, NOW())");
            $stmt->execute([$title, $message]);
        } elseif ($audience === 'selected' && !empty($_POST['user_ids'])) {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($_POST['user_ids'] as $uid) {
                $stmt->execute([$uid, $title, $message]);
            }
        }
        $success = "Notification sent to " . ($audience === 'all' ? "all users" : "selected users") . ".";
    }
}

// Fetch all users for selection (for targeted notifications)
$stmt = $db->query("SELECT id, name, email FROM users ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:700px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Send Push Notification</h4>
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
                    <label for="title" class="form-label">Notification Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Audience</label>
                    <select class="form-select" name="audience" id="audience" onchange="toggleUserSelect()">
                        <option value="all" <?php if(($_POST['audience'] ?? '') === 'all') echo 'selected'; ?>>All Users</option>
                        <option value="selected" <?php if(($_POST['audience'] ?? '') === 'selected') echo 'selected'; ?>>Selected Users</option>
                    </select>
                </div>
                <div class="mb-3" id="user-select-div" style="display:<?php echo (($_POST['audience'] ?? '') === 'selected') ? 'block' : 'none'; ?>">
                    <label for="user_ids" class="form-label">Select Users</label>
                    <select class="form-select" id="user_ids" name="user_ids[]" multiple size="8">
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-bell"></i> Send Notification</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
function toggleUserSelect() {
    var audience = document.getElementById('audience').value;
    document.getElementById('user-select-div').style.display = (audience === 'selected') ? 'block' : 'none';
}
</script>
<?php include '../includes/admin-footer.php'; ?>
