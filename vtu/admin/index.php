<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

// Only allow admin access
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Get stats
$db = DB::getInstance()->getConnection();

// Total users
$stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Total wallet transactions
$stmt = $db->query("SELECT COUNT(*) as total_wallet_txns FROM wallet_transactions");
$totalWalletTxns = $stmt->fetch(PDO::FETCH_ASSOC)['total_wallet_txns'];

// Total VTU transactions
$stmt = $db->query("SELECT COUNT(*) as total_vtu_txns FROM vtu_transactions");
$totalVtuTxns = $stmt->fetch(PDO::FETCH_ASSOC)['total_vtu_txns'];

// Total wallet balance
$stmt = $db->query("SELECT SUM(wallet_balance) as total_balance FROM users");
$totalBalance = $stmt->fetch(PDO::FETCH_ASSOC)['total_balance'];

// Recent transactions
$stmt = $db->query("SELECT v.*, u.name, u.phone as user_phone FROM vtu_transactions v JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC LIMIT 5");
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<header class="admin-header">
  <div class="container">
    <h1 class="admin-title">Admin Dashboard</h1>
    <p class="admin-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
      </ol>
    </nav>
  </div>
</header>

<div class="container">
  <div class="stats-grid">
    <div class="stat-card stat-card-primary">
      <h3 class="stat-card-title">Total Users</h3>
      <p class="stat-card-value"><?php echo number_format($totalUsers); ?></p>
      <a href="users.php" class="stat-card-link">View Users →</a>
    </div>
    
    <div class="stat-card stat-card-success">
      <h3 class="stat-card-title">Wallet Transactions</h3>
      <p class="stat-card-value"><?php echo number_format($totalWalletTxns); ?></p>
      <a href="wallet-transactions.php" class="stat-card-link">View Transactions →</a>
    </div>
    
    <div class="stat-card stat-card-info">
      <h3 class="stat-card-title">VTU Transactions</h3>
      <p class="stat-card-value"><?php echo number_format($totalVtuTxns); ?></p>
      <a href="vtu-transactions.php" class="stat-card-link">View Transactions →</a>
    </div>
    
    <div class="stat-card stat-card-warning">
      <h3 class="stat-card-title">Total Balance</h3>
      <p class="stat-card-value">₦<?php echo number_format($totalBalance, 2); ?></p>
      <a href="wallet-transactions.php" class="stat-card-link">View Details →</a>
    </div>
  </div>
  
  <div class="content-row">
    <div>
      <div class="data-card">
        <div class="data-card-header">
          <h3 class="data-card-title">Recent VTU Transactions</h3>
        </div>
        <div class="data-card-body">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>User</th>
                  <th>Type</th>
                  <th>Phone</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentTransactions as $txn): ?>
                  <tr>
                    <td><?php echo date('M j, H:i', strtotime($txn['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($txn['name']); ?></td>
                    <td><?php echo ucfirst($txn['service_type']); ?></td>
                    <td><?php echo $txn['phone'] ?? $txn['user_phone']; ?></td>
                    <td>₦<?php echo number_format($txn['amount'], 2); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $txn['status'] == 'successful' ? 'success' : ($txn['status'] == 'failed' ? 'danger' : 'warning'); ?>">
                        <?php echo ucfirst($txn['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <a href="vtu-transactions.php" class="btn btn-primary" style="margin-top: 1rem;">View All Transactions</a>
        </div>
      </div>
    </div>
    
    <div>
      <div class="data-card">
        <div class="data-card-header">
          <h3 class="data-card-title">Quick Actions</h3>
        </div>
        <div class="data-card-body">
          <div class="quick-actions">
            <a href="wallet-fund.php" class="action-btn">
              <i class="fas fa-wallet"></i> Fund User Wallet
            </a>
            <a href="manual-fund-requests.php" class="action-btn" target="_blank" rel="noopener">
              <i class="fas fa-university"></i> Manual Fund Requests
            </a>
            <a href="settings.php" class="action-btn">
              <i class="fas fa-cog"></i> System Settings
            </a>
            <a href="users.php?filter=new" class="action-btn">
              <i class="fas fa-user-plus"></i> New Users
            </a>
            <a href="failed-transactions.php" class="action-btn">
              <i class="fas fa-exclamation-triangle"></i> Failed TXNs
            </a>
          </div>
        </div>
      </div>
      
      <div class="data-card" style="margin-top: 1.5rem;">
        <div class="data-card-header">
          <h3 class="data-card-title">System Status</h3>
        </div>
        <div class="data-card-body">
          <div class="system-status">
            <div class="status-item status-success">
              <strong>VTU API:</strong> Connected
            </div>
            <div class="status-item status-success">
              <strong>Payment Gateway:</strong> Active
            </div>
            <div class="status-item status-info">
              <strong>Last Cron Run:</strong> <?php echo date('M j, H:i:s'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin-footer.php'; ?>