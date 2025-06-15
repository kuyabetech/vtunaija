<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

/**
 * Returns a Bootstrap badge class based on transaction status.
 *
 * @param string $status
 * @return string
 */
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'success':
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
        case 'error':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Secure session management
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

// Validate user session
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    session_regenerate_id(true);
    session_destroy();
    redirect('login.php');
    exit;
}

// Get recent transactions with pagination
$transactions = [];
$limit = 5;
try {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM vtu_transactions 
                         WHERE user_id = ? 
                         ORDER BY created_at DESC 
                         LIMIT ?");
    $stmt->execute([$user['id'], $limit]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Get active services (no APCu, fallback to direct DB query)
$services = [];
try {
    $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Services fetch error: " . $e->getMessage());
}

// CSRF token for forms
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

$pageTitle = "Dashboard";
$currentPage = "home";

include 'includes/header.php';
?>

<div class="container dashboard-arcade">
    <!-- Security Notice -->
    <div class="alert alert-info d-flex align-items-center">
        <i class="fas fa-shield-alt me-2"></i>
        <div>
            <strong>Security Tip:</strong> Always log out after your session and never share your credentials.
        </div>
    </div>

    <div class="row dashboard-cards">
        <!-- User Summary Card -->
        <div class="col-md-4 mb-4">
            <div class="arcade-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="arcade-card-title mb-0">My Account</h3>
                    <span class="badge bg-primary">
                        <?php echo htmlspecialchars($user['account_type'] ?? 'User'); ?>
                    </span>
                </div>
                <div class="arcade-card-amount mb-3">
                    ₦<?= number_format($user['wallet_balance'], 2) ?>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="fund-wallet.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-wallet me-1"></i> Fund Wallet
                    </a>
                    <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-user-cog me-1"></i> Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Service Cards -->
        <?php foreach ($services as $svc): ?>
            <div class="col-md-4 mb-4">
                <a href="<?= htmlspecialchars($svc['link'] ?: 'service.php?id='.$svc['id']) ?>" class="text-decoration-none">
                    <div class="arcade-card h-100 service-card" data-service-id="<?= $svc['id'] ?>">
                        <div class="text-center py-3">
                            <i class="<?= htmlspecialchars($svc['icon'] ?: 'fas fa-cogs') ?> fa-3x mb-3"
                               style="color: <?= htmlspecialchars($svc['color'] ?: '#2563eb') ?>"></i>
                            <h4 class="arcade-card-title"><?= htmlspecialchars($svc['name']) ?></h4>
                            <?php if (!empty($svc['description'])): ?>
                                <p class="text-muted"><?= htmlspecialchars($svc['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Transactions Section -->
    <div class="dashboard-transactions mt-4">
        <div class="arcade-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="arcade-card-title mb-0">Recent Transactions</h3>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php if (empty($transactions)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No transactions yet</p>
                    <a href="airtime.php" class="btn btn-primary">Make Your First Transaction</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Details</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td><?= date('M j, g:i a', strtotime($txn['created_at'])) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($txn['service_type'])) ?></td>
                                    <td>
                                        <?php if (!empty($txn['network'])): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($txn['network']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($txn['phone'] ?? $txn['account'] ?? 'N/A') ?>
                                    </td>
                                    <td class="fw-bold">₦<?= number_format($txn['amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($txn['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($txn['status'])) ?>
                                        </span>
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

<?php 
// Include modals and footers
include 'includes/spinner.php'; 
include 'includes/chat-widget.php'; 
include 'includes/footer.php'; 
?>

<!-- Welcome Modal -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Welcome Back, <?= htmlspecialchars($user['name']) ?>!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <img src="assets/img/welcome.svg" alt="Welcome" class="img-fluid mb-3" style="max-height: 120px;">
        <p>Your wallet balance: <strong>₦<?= number_format($user['wallet_balance'], 2) ?></strong></p>
        <div class="alert alert-success">
          <i class="fas fa-bolt me-2"></i>
          Enjoy instant transactions with 99.9% success rate!
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
          <i class="fas fa-rocket me-2"></i> Get Started
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Enhanced dashboard interactions
document.addEventListener('DOMContentLoaded', function() {
    // Show welcome modal only once
    if (!sessionStorage.getItem('welcomeShown')) {
        new bootstrap.Modal(document.getElementById('welcomeModal')).show();
        sessionStorage.setItem('welcomeShown', 'true');
    }

    // Service card hover effects
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
/* Enhanced Dashboard Styles */
.dashboard-arcade {
    padding-top: 2rem;
    padding-bottom: 3rem;
}

.arcade-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    overflow: hidden;
}

.arcade-card:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-color: rgba(37, 99, 235, 0.2);
}

.service-card {
    cursor: pointer;
    border-top: 3px solid var(--bs-primary);
}

.dashboard-cards {
    row-gap: 1.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(37, 99, 235, 0.05);
}

@media (max-width: 768px) {
    .dashboard-cards > div {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>