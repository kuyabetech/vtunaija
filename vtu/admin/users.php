<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Update user status based on action
if (isset($_GET['action'], $_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $action = $_GET['action'];
    $status = null;
    if ($action === 'activate') $status = 'active';
    if ($action === 'suspend') $status = 'suspended';
    if ($action === 'delete') $status = 'deleted';
    if ($status) {
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $userId]);
        header("Location: users.php?msg=User status updated");
        exit;
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search and filter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter == 'new') {
    $where[] = "created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter == 'active') {
    $where[] = "last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($filter == 'inactive') {
    $where[] = "last_login < DATE_SUB(NOW(), INTERVAL 90 DAY) OR last_login IS NULL";
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get users
$query = "SELECT * FROM users $whereClause LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users $whereClause");
$stmt->execute($params);
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalUsers / $perPage);

include '../includes/admin-header.php';
?>
<style>
:root {
  --primary: #2563eb;
  --primary-light: #3b82f6;
  --secondary: #059669;
  --accent: #7c3aed;
  --text: #1f2937;
  --text-light: #6b7280;
  --bg: #f9fafb;
  --card-bg: #ffffff;
  --border: #e5e7eb;
}
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: var(--bg);
  color: var(--text);
}
.container-fluid {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 2rem;
}
.card-header {
  background: #f3f4f6;
  border-radius: 12px 12px 0 0 !important;
  border-bottom: 1px solid var(--border);
  padding: 1.25rem 1.5rem;
}
.search-filter {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 2rem;
}
.search-input {
  flex: 1;
  min-width: 250px;
}
.filter-select {
  min-width: 200px;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}
.data-table th {
  background: #f3f4f6;
  color: var(--primary);
  font-weight: 600;
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
}
.data-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border);
  color: var(--text-light);
}
.data-table tr:last-child td {
  border-bottom: none;
}
.data-table tr:hover {
  background: #f9fafb;
}
.action-dropdown .dropdown-toggle {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 500;
}
.action-dropdown .dropdown-menu {
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  border: 1px solid var(--border);
}
.action-dropdown .dropdown-item {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}
.action-dropdown .dropdown-item i {
  width: 20px;
  margin-right: 8px;
  text-align: center;
}
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  gap: 0.5rem;
  border: none;
}
.btn-primary {
  background: var(--primary);
  color: white;
}
.btn-primary:hover {
  background: var(--primary-light);
}
.pagination {
  justify-content: center;
  margin-top: 2rem;
}
.page-link {
  color: var(--primary);
  border-radius: 8px !important;
  border: 1px solid var(--border);
  margin: 0 0.25rem;
  padding: 0.5rem 0.75rem;
}
.page-item.active .page-link {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}
@media (max-width: 768px) {
  .container-fluid {
    padding: 1rem;
  }
  .search-filter {
    flex-direction: column;
  }
  .search-input,
  .filter-select {
    width: 100%;
  }
  .data-table {
    font-size: 0.85rem;
  }
  .data-table th,
  .data-table td {
    padding: 0.75rem;
  }
}
@media (max-width: 576px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .page-title {
    font-size: 1.5rem;
  }
  .data-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>
<div class="container-fluid">
  <div class="page-header">
    <h1 class="page-title">User Management</h1>
    <a href="user-add.php" class="btn btn-primary">
      <i class="fas fa-plus"></i> Add New User
    </a>
  </div>
  <div class="card">
    <div class="card-header">
      <form method="GET" class="search-filter">
        <div class="input-group search-input">
          <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
          <button class="btn btn-outline-secondary" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
        <select name="filter" class="form-select filter-select" onchange="this.form.submit()">
          <option value="">All Users</option>
          <option value="new" <?php echo $filter == 'new' ? 'selected' : ''; ?>>New (Last 7 days)</option>
          <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>Active (Last 30 days)</option>
          <option value="inactive" <?php echo $filter == 'inactive' ? 'selected' : ''; ?>>Inactive (90+ days)</option>
        </select>
      </form>
    </div>
    <div class="card-body">
      <!-- Communication Actions -->
      <div class="mb-4" style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="send-bulk-email.php" class="btn btn-primary">
          <i class="fas fa-envelope"></i> Send Bulk Email
        </a>
        <a href="send-bulk-sms.php" class="btn btn-primary">
          <i class="fas fa-sms"></i> Send Bulk SMS
        </a>
        <a href="push-notifications.php" class="btn btn-primary">
          <i class="fas fa-bell"></i> Push Notifications
        </a>
        <a href="referrals.php" class="btn btn-primary">
          <i class="fas fa-user-friends"></i> Referral System
        </a>
        <a href="affiliate-commissions.php" class="btn btn-primary">
          <i class="fas fa-coins"></i> Affiliate Commissions
        </a>
        <button class="btn btn-secondary" id="toggle-dark-mode" type="button">
          <i class="fas fa-moon"></i> Dark Mode
        </button>
      </div>
      <div class="table-responsive" style="max-width: 950px; margin: 0 auto;">
        <table class="data-table" style="min-width:700px;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Balance</th>
              <th>Joined</th>
              <th>Last Login</th>
              <th>KYC</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td>₦<?php echo number_format($user['wallet_balance'], 2); ?></td>
                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td><?php echo !empty($user['last_login']) ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                <td>
                  <?php
                  // Example: KYC status logic (assumes kyc_status column: 'pending', 'approved', 'rejected')
                  if (isset($user['kyc_status'])) {
                    if ($user['kyc_status'] === 'approved') {
                      echo '<span class="badge bg-success">Approved</span>';
                    } elseif ($user['kyc_status'] === 'pending') {
                      echo '<span class="badge bg-warning text-dark">Pending</span>';
                    } elseif ($user['kyc_status'] === 'rejected') {
                      echo '<span class="badge bg-danger">Rejected</span>';
                    } else {
                      echo '<span class="badge bg-secondary">N/A</span>';
                    }
                  } else {
                    echo '<span class="badge bg-secondary">N/A</span>';
                  }
                  ?>
                  <?php if (isset($user['kyc_status']) && $user['kyc_status'] === 'pending'): ?>
                    <a href="kyc-review.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary ms-2">Review</a>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="dropdown action-dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      Actions
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="user-view.php?id=<?php echo $user['id']; ?>">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="user-edit.php?id=<?php echo $user['id']; ?>">
                          <i class="fas fa-edit"></i> Edit
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="wallet-fund.php?user_id=<?php echo $user['id']; ?>">
                          <i class="fas fa-wallet"></i> Fund Wallet
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                          <i class="fas fa-trash"></i> Delete
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">Previous</a>
            </li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
              <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</div>
<script>
function confirmDelete(userId) {
  if (confirm('Are you sure you want to delete this user?')) {
    window.location.href = 'user-delete.php?id=' + userId;
  }
}

</script>
<style>
</style>
<?php include '../includes/admin-footer.php'; ?>

