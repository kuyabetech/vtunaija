<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $wallet_balance = floatval($_POST['wallet_balance'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if (!$name || !$email || !$phone || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Check for duplicate email/phone
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or phone already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, wallet_balance, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $email, $phone, $hashed, $wallet_balance, $status])) {
                $success = 'User added successfully.';
            } else {
                $error = 'Failed to add user.';
            }
        }
    }
}

include '../includes/admin-header.php';
?>
<div class="container" style="max-width:600px;margin:2rem auto;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add New User</h4>
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
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="wallet_balance" class="form-label">Wallet Balance</label>
                    <input type="number" step="0.01" class="form-control" id="wallet_balance" name="wallet_balance" value="<?php echo htmlspecialchars($_POST['wallet_balance'] ?? '0'); ?>">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php if(($_POST['status'] ?? '')=='active') echo 'selected'; ?>>Active</option>
                        <option value="suspended" <?php if(($_POST['status'] ?? '')=='suspended') echo 'selected'; ?>>Suspended</option>
                        <option value="deleted" <?php if(($_POST['status'] ?? '')=='deleted') echo 'selected'; ?>>Deleted</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?>
