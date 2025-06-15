<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Handle approve/reject action
if (isset($_GET['kyc_action'], $_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $kycAction = $_GET['kyc_action'];
    $status = ($kycAction === 'approve') ? 'approved' : (($kycAction === 'reject') ? 'rejected' : null);
    if ($status) {
        $stmt = $db->prepare("UPDATE users SET kyc_status = ? WHERE id = ?");
        $stmt->execute([$status, $userId]);
        header("Location: kyc-review.php?msg=KYC status updated");
        exit;
    }
}

// Fetch user for review
$user = null;
if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../includes/admin-header.php';
?>
<!-- ...existing HTML code... -->
<td>
    <?php if (!empty($user) && $user['kyc_doc']): ?>
        <a href="/uploads/kyc/<?php echo htmlspecialchars($user['kyc_doc']); ?>" target="_blank">View Document</a>
    <?php endif; ?>
</td>
<td>
    <?php if (!empty($user) && $user['kyc_status'] === 'pending'): ?>
        <a href="kyc-review.php?kyc_action=approve&user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Approve</a>
        <a href="kyc-review.php?kyc_action=reject&user_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
    <?php elseif (!empty($user)): ?>
        <span class="badge bg-<?php echo $user['kyc_status'] === 'approved' ? 'success' : 'danger'; ?>">
            <?php echo ucfirst($user['kyc_status']); ?>
        </span>
    <?php endif; ?>
</td>
<!-- ...existing HTML code... -->
<?php include '../includes/admin-footer.php'; ?>