<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;
if ($userId > 0) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    include '../includes/admin-header.php';
    echo '<div class="container mt-5"><div class="alert alert-danger">User not found.</div></div>';
    include '../includes/admin-footer.php';
    exit;
}

// Get recent transactions
$stmt = $db->prepare("SELECT * FROM vtu_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:700px;margin:2rem auto;">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">User Details</h4>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Name</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($user['name']); ?></dd>
                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($user['email']); ?></dd>
                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($user['phone']); ?></dd>
                <dt class="col-sm-4">Wallet Balance</dt>
                <dd class="col-sm-8">₦<?php echo number_format($user['wallet_balance'], 2); ?></dd>
                <dt class="col-sm-4">Status</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($user['status'] ?? ''); ?></dd>
                <dt class="col-sm-4">Joined</dt>
                <dd class="col-sm-8"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></dd>
                <dt class="col-sm-4">Last Login</dt>
                <dd class="col-sm-8"><?php echo !empty($user['last_login']) ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></dd>
            </dl>
            <div class="mt-3">
                <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                <a href="wallet-fund.php?user_id=<?php echo $user['id']; ?>" class="btn btn-success"><i class="fas fa-wallet"></i> Fund Wallet</a>
                <a href="users.php" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <strong>Recent Transactions</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-width:100%;">
                <table class="table table-striped table-sm" style="min-width:600px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Network</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($tx['service_type']); ?></td>
                            <td><?php echo htmlspecialchars($tx['network']); ?></td>
                            <td><?php echo htmlspecialchars($tx['phone']); ?></td>
                            <td>₦<?php echo number_format($tx['amount'], 2); ?></td>
                            <td>
                                <?php if ($tx['status'] == 'successful'): ?>
                                    <span class="badge bg-success">Successful</span>
                                <?php elseif ($tx['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No transactions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
