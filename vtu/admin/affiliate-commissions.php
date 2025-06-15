<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get affiliate commissions
$query = "
    SELECT a.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone
    FROM affiliate_commissions a
    LEFT JOIN users u ON a.user_id = u.id
    $whereClause
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM affiliate_commissions a LEFT JOIN users u ON a.user_id = u.id $whereClause");
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $perPage);

include '../includes/admin-header.php';
?>
<style>
.affiliate-table th, .affiliate-table td {
    padding: 0.8rem 1rem;
    font-size: 0.97rem;
}
.affiliate-table th {
    background: #f3f4f6;
    color: #059669;
    font-weight: 600;
}
.affiliate-table tr:hover {
    background: #f9fafb;
}
</style>
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Affiliate Commissions</h1>
        <form method="GET" class="d-flex" style="gap:1rem;">
            <input type="text" name="search" class="form-control" placeholder="Search user..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <strong>Commissions List</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-width:950px;margin:0 auto;">
                <table class="table affiliate-table table-responsive-sm" style="min-width:700px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $i => $row): ?>
                        <tr>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_phone']); ?></td>
                            <td>₦<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['source']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($commissions)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No affiliate commissions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
