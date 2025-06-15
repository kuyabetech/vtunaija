<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

if ($userId > 0) {
    // Optionally, you can do a soft delete by setting status = 'deleted'
    $stmt = $db->prepare("UPDATE users SET status = 'deleted' WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $success = "User deleted (status set to 'deleted').";
    } else {
        $error = "Failed to delete user.";
    }
} else {
    $error = "Invalid user ID.";
}

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:600px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">Delete User</h4>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="users.php" class="btn btn-primary">Back to Users</a>
            <?php else: ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <a href="users.php" class="btn btn-secondary">Back to Users</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
