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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $wallet_balance = floatval($_POST['wallet_balance'] ?? 0);
    $status = $_POST['status'] ?? $user['status'];

    // Optional: password change
    $password = $_POST['password'] ?? '';
    $fields = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'wallet_balance' => $wallet_balance,
        'status' => $status
    ];

    if (!empty($password)) {
        $fields['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $set = [];
    $params = [];
    foreach ($fields as $k => $v) {
        $set[] = "$k = ?";
        $params[] = $v;
    }
    $params[] = $userId;

    $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    if ($stmt->execute($params)) {
        $success = "User updated successfully.";
        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update user.";
    }
}

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:600px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit User</h4>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="mb-3">
                    <label for="wallet_balance" class="form-label">Wallet Balance</label>
                    <input type="number" step="0.01" class="form-control" id="wallet_balance" name="wallet_balance" value="<?php echo htmlspecialchars($user['wallet_balance']); ?>">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php if($user['status']=='active') echo 'selected'; ?>>Active</option>
                        <option value="suspended" <?php if($user['status']=='suspended') echo 'selected'; ?>>Suspended</option>
                        <option value="deleted" <?php if($user['status']=='deleted') echo 'selected'; ?>>Deleted</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="user-view.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
