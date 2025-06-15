<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $flag_id = intval($_POST['flag_id'] ?? 0);
    
    if ($action == 'resolve') {
        $stmt = $db->prepare("UPDATE fraud_flags 
                             SET resolved_at = NOW(), resolved_by = ?
                             WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $flag_id]);
        
        $_SESSION['success'] = 'Flag resolved successfully';
    } elseif ($action == 'block') {
        $stmt = $db->prepare("UPDATE users 
                             SET is_blocked = TRUE 
                             WHERE id = (SELECT user_id FROM fraud_flags WHERE id = ?)");
        $stmt->execute([$flag_id]);
        
        $stmt = $db->prepare("UPDATE fraud_flags 
                             SET resolved_at = NOW(), resolved_by = ?
                             WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $flag_id]);
        
        $_SESSION['success'] = 'User blocked and flag resolved';
    }
    
    redirect('fraud-review.php');
}

// Get flagged transactions
$stmt = $db->prepare("
    SELECT f.*, u.name as user_name, u.email, u.phone 
    FROM fraud_flags f
    JOIN users u ON f.user_id = u.id
    WHERE f.resolved_at IS NULL
    ORDER BY f.created_at DESC
");
$stmt->execute();
$flags = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Fraud Review</h2>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h4>Pending Flags</h4>
        </div>
        <div class="card-body">
            <?php if (empty($flags)): ?>
                <div class="alert alert-info">No pending fraud flags</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Reason</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flags as $flag): ?>
                                <tr>
                                    <td><?php echo date('M j, H:i', strtotime($flag['created_at'])); ?></td>
                                    <td>
                                        <a href="user-view.php?id=<?php echo $flag['user_id']; ?>">
                                            <?php echo $flag['user_name']; ?>
                                        </a><br>
                                        <small><?php echo $flag['email']; ?></small>
                                    </td>
                                    <td><?php echo $flag['reason']; ?></td>
                                    <td>
                                        <?php 
                                        $metadata = json_decode($flag['metadata'], true);
                                        if ($metadata): ?>
                                            <button class="btn btn-sm btn-outline-info" type="button" 
                                                    data-toggle="collapse" 
                                                    data-target="#details-<?php echo $flag['id']; ?>">
                                                View Details
                                            </button>
                                            <div class="collapse" id="details-<?php echo $flag['id']; ?>">
                                                <pre class="mt-2"><?php print_r($metadata); ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="flag_id" value="<?php echo $flag['id']; ?>">
                                            <button type="submit" name="action" value="resolve" 
                                                    class="btn btn-sm btn-success">
                                                Mark as Safe
                                            </button>
                                            <button type="submit" name="action" value="block" 
                                                    class="btn btn-sm btn-danger ml-1">
                                                Block User
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>