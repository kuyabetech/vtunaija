<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$db = DB::getInstance()->getConnection();

// Fetch notifications for user (and global)
$stmt = $db->prepare("
    SELECT title, message, created_at
    FROM notifications
    WHERE user_id = ? OR user_id IS NULL
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="container" style="max-width:700px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Notifications</h4>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <div class="alert alert-info text-center">You have no notifications.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($notifications as $n): ?>
                        <li class="list-group-item">
                            <div class="fw-bold"><?php echo htmlspecialchars($n['title']); ?></div>
                            <div style="color:#6b7280;"><?php echo htmlspecialchars($n['message']); ?></div>
                            <div class="text-end" style="font-size:0.9em;color:#aaa;"><?php echo date('M j, Y H:i', strtotime($n['created_at'])); ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/spinner.php'; ?>
