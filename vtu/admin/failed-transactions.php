<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

// Search/filter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(t.reference LIKE ? OR t.phone LIKE ? OR t.user_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$where[] = "t.status = 'failed'";

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get failed transactions
$query = "
    SELECT t.*, u.name AS user_name, u.email AS user_email
    FROM vtu_transactions t
    LEFT JOIN users u ON t.user_id = u.id
    $whereClause
    ORDER BY t.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM vtu_transactions t $whereClause");
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $perPage);

include '../includes/admin-header.php';
?>
<style>
.failed-table th, .failed-table td {
    padding: 0.8rem 1rem;
    font-size: 0.97rem;
}
.failed-table th {
    background: #f3f4f6;
    color: #ef4444;
    font-weight: 600;
}
.failed-table tr:hover {
    background: #f9fafb;
}
</style>
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Failed Transactions</h1>
        <form method="GET" class="d-flex" style="gap:1rem;">
            <input type="text" name="search" class="form-control" placeholder="Search reference, phone, user..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <strong>Failed Transactions List</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-width: 950px; margin: 0 auto;">
                <table class="table failed-table table-responsive-sm" style="min-width:700px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Service</th>
                            <th>Network</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $i => $tx): ?>
                        <tr>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($tx['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($tx['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($tx['service_type']); ?></td>
                            <td><?php echo htmlspecialchars($tx['network']); ?></td>
                            <td><?php echo htmlspecialchars($tx['phone']); ?></td>
                            <td>₦<?php echo number_format($tx['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($tx['reference']); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($tx['failure_reason'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No failed transactions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Always show pagination if there is more than one page -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
