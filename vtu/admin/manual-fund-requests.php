<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Approve or reject manual fund
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    if ($action === 'approve') {
        // Approve: set status to 'successful' and credit user wallet
        $stmt = $db->prepare("SELECT * FROM wallet_transactions WHERE id = ? AND type = 'manual' AND status = 'pending'");
        $stmt->execute([$id]);
        $txn = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($txn) {
            // Credit wallet
            $db->beginTransaction();
            $db->prepare("UPDATE wallet_transactions SET status = 'successful' WHERE id = ?")->execute([$id]);
            $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?")->execute([$txn['amount'], $txn['user_id']]);
            $db->commit();
            $_SESSION['success'] = 'Manual fund approved and wallet credited.';
        }
    } elseif ($action === 'reject') {
        // Reject: set status to 'failed'
        $db->prepare("UPDATE wallet_transactions SET status = 'failed' WHERE id = ?")->execute([$id]);
        $_SESSION['success'] = 'Manual fund request rejected.';
    }
    header('Location: manual-fund-requests.php');
    exit;
}

// Fetch pending manual fund requests
$stmt = $db->query("
    SELECT w.*, u.name, u.email, u.phone
    FROM wallet_transactions w
    JOIN users u ON w.user_id = u.id
    WHERE w.type = 'manual' AND w.status = 'pending'
    ORDER BY w.created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container-fluid" style="max-width:1100px;margin:2rem auto;">
    <div class="page-header">
        <h1 class="page-title">Manual Fund Requests</h1>
    </div>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">
            <strong>Pending Manual Funding</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="min-width:800px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Bank</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $row): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td>₦<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['bank']); ?></td>
                            <td><?php echo htmlspecialchars($row['reference']); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['note']); ?></td>
                            <td>
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve and credit wallet?')">Approve</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No pending manual fund requests.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
