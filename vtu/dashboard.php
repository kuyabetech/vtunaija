<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

$isDashboard = true;

$user = getUserById($_SESSION['user_id']);
if (!$user) {
    // Invalid user/session, force logout and redirect
    session_destroy();
    redirect('login.php');
    exit;
}

// Get recent transactions
$db = DB::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM vtu_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Home";
$currentPage = "home";

include 'includes/header.php';
?>

<div class="container dashboard-arcade">
    <!-- Security Notice Section -->
    <section class="dashboard-section mb-4">
        <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-shield-alt me-2"></i>
            <div>
                <strong>Security Tip:</strong> Always log out after your session and never share your credentials.
            </div>
        </div>
    </section>

    <!-- User Summary Section -->
    <section class="dashboard-section mb-4">
        <div class="row dashboard-cards">
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
                        <a href="wallet.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-wallet me-1"></i> Fund Wallet
                        </a>
                        <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-user-cog me-1"></i> Profile
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="arcade-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="arcade-card-title mb-0">Referral Earnings</h3>
                        <span class="badge bg-success">
                            <?php echo htmlspecialchars($user['referral_code'] ?? 'N/A'); ?>
                        </span>
                    </div>
                    <div class="arcade-card-amount mb-3">
                        ₦<?= number_format($user['referral_balance'], 2) ?>
                    </div>
                    <a href="referrals.php" class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-users me-1"></i> My Referrals
                    </a>
                </div>
            </div>
            <!-- Service Cards Section -->
            <section class="dashboard-section mb-4 services-section">
                <h3 class="section-title">Our Services</h3>
                <div class="services-grid">
                    <?php
                    $db = DB::getInstance()->getConnection();
                    $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY id ASC");
                    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($services as $svc):
                        $icon = $svc['icon'] ?? 'fas fa-cogs';
                        $link = $svc['link'] ?? '#';
                    ?>
                    <a href="<?php echo htmlspecialchars($link); ?>" style="text-decoration:none;">
                        <div class="arcade-card service-card h-100">
                            <div>
                                <i class="<?php echo htmlspecialchars($icon); ?>" style="color:#2563eb;font-size:2.2rem;margin-bottom:0.5rem;"></i>
                            </div>
                            <div class="arcade-card-title" style="justify-content:center;margin-top:0.5rem;">
                                <?php echo htmlspecialchars($svc['name']); ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

    <!-- Transactions Section -->
    <section class="dashboard-section">
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
                                            <?php
                                            $status = strtolower($txn['status']);
                                            $badgeClass = $status === 'successful' || $status === 'success' ? 'success'
                                                : ($status === 'failed' ? 'danger'
                                                : ($status === 'pending' ? 'warning' : 'secondary'));
                                            ?>
                                            <span class="badge badge-<?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($txn['status']); ?>
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
    </section>
</div>
<?php include 'includes/spinner.php'; ?>
<?php include 'includes/chat-widget.php'; ?>

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
// Show welcome modal only once per session after login
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
    text-align: center;
    min-height: 170px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .services-section {
    margin-top: 2rem;
    margin-bottom: 2rem;
  }

  .services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }

  .services-section .section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2563eb;
    margin-bottom: 1.2rem;
    text-align: left;
  }

  @media (max-width: 992px) {
    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
  }

  @media (max-width: 600px) {
    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
  }
    @media (max-width: 360px) {
    .services-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
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
}</style>
