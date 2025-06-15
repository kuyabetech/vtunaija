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

// Get referrals
$query = "
    SELECT r.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, 
           ref.name AS referrer_name, ref.email AS referrer_email
    FROM referrals r
    LEFT JOIN users u ON r.referred_id = u.id
    LEFT JOIN users ref ON r.referrer_id = ref.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$stmt = $db->prepare("SELECT COUNT(*) as total FROM referrals r LEFT JOIN users u ON r.referred_id = u.id $whereClause");
$stmt->execute($params);
$totalReferrals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalReferrals / $perPage);

include '../includes/admin-header.php';
?>
<style>
.referral-table th, .referral-table td {
    padding: 0.8rem 1rem;
    font-size: 0.97rem;
}
.referral-table th {
    background: #f3f4f6;
    color: #2563eb;
    font-weight: 600;
}
.referral-table tr:hover {
    background: #f9fafb;
}
</style>
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Referral System</h1>
        <form method="GET" class="d-flex" style="gap:1rem;">
            <input type="text" name="search" class="form-control" placeholder="Search user or referrer..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <strong>Referrals List</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table referral-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>User Email</th>
                            <th>Referrer</th>
                            <th>Referrer Email</th>
                            <th>Date Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $i => $ref): ?>
                        <tr>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($ref['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($ref['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($ref['referrer_name']); ?></td>
                            <td><?php echo htmlspecialchars($ref['referrer_email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($ref['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($referrals)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No referrals found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
