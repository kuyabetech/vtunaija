<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once "includes/header.php";

// Add default values for filter variables to avoid warnings
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

// Fetch transactions for the logged-in user with filters
$db = DB::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Build filter/search logic
$where = "user_id = ?";
$params = [$user_id];
if ($search) {
    $where .= " AND (network LIKE ? OR phone LIKE ? OR account LIKE ? OR reference LIKE ?)";
    $params = array_merge($params, array_fill(0, 4, "%$search%"));
}
if ($status) {
    $where .= " AND status = ?";
    $params[] = $status;
}
if ($type) {
    $where .= " AND service_type = ?";
    $params[] = $type;
}
$stmt = $db->prepare("SELECT * FROM vtu_transactions WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="transactions-container">
    <h1 class="transactions-header">Transaction History</h1>
    
    <form method="get" class="transactions-filters">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search transactions..." class="transactions-search">
        
        <select name="status" class="transactions-select">
            <option value="">All Statuses</option>
            <option value="successful" <?php if($status=='successful') echo 'selected'; ?>>Successful</option>
            <option value="failed" <?php if($status=='failed') echo 'selected'; ?>>Failed</option>
            <option value="pending" <?php if($status=='pending') echo 'selected'; ?>>Pending</option>
        </select>
        
        <select name="type" class="transactions-select">
            <option value="">All Types</option>
            <option value="airtime" <?php if($type=='airtime') echo 'selected'; ?>>Airtime</option>
            <option value="data" <?php if($type=='data') echo 'selected'; ?>>Data</option>
            <option value="bills" <?php if($type=='bills') echo 'selected'; ?>>Bills</option>
            <option value="crypto" <?php if($type=='crypto') echo 'selected'; ?>>Crypto</option>
            <option value="wallet" <?php if($type=='wallet') echo 'selected'; ?>>Wallet</option>
            <option value="referral" <?php if($type=='referral') echo 'selected'; ?>>Referral</option>
        </select>
        
        <button type="submit" class="transactions-button">
            <i class="fas fa-search"></i> Filter
        </button>
    </form>
    
    <div class="transactions-table-container">
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Network/Provider</th>
                    <th>Account/Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="no-transactions">
                            No transactions found matching your criteria
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $txn): ?>
                        <tr>
                            <td><?php echo date('M j, Y H:i', strtotime($txn['created_at'])); ?></td>
                            <td><?php echo ucfirst($txn['service_type']); ?></td>
                            <td><?php echo $txn['network'] ?? 'N/A'; ?></td>
                            <td><?php echo $txn['phone'] ?? $txn['account'] ?? 'N/A'; ?></td>
                            <td class="transaction-amount">₦<?php echo number_format($txn['amount'], 2); ?></td>
                            <td>
                                <span class="transaction-status 
                                    <?php echo $txn['status'] == 'successful' ? 'transaction-success' : 
                                          ($txn['status'] == 'failed' ? 'transaction-failed' : 'transaction-pending'); ?>">
                                    <?php echo ucfirst($txn['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>

</style>