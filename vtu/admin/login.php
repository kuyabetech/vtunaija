<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Accept login if user exists, password matches, and user is admin by either 'is_admin' or 'role'
    $isAdmin = (
        (isset($admin['is_admin']) && $admin['is_admin'] == 1) ||
        (isset($admin['role']) && strtolower($admin['role']) === 'admin')
    );

    if ($admin && $isAdmin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid credentials or not an admin.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/flatly/bootstrap.min.css">
    <style>
        body { background: #f9fafb; }
        .login-card {
            max-width: 400px;
            margin: 5rem auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(37,99,235,0.08);
            padding: 2.5rem 2rem 2rem 2rem;
            color: #1f2937;
            font-family: 'Inter', Arial, sans-serif;
        }
        .login-title {
            color: #2563eb;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.7rem 0;
            width: 100%;
            margin-top: 0.5rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.1s;
            font-family: inherit;
            box-shadow: 0 2px 8px #2563eb33;
        }
        .btn-primary:hover {
            background: #3b82f6;
            color: #fff;
            box-shadow: 0 0 24px #2563eb33;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-title"><i class="fas fa-user-shield" style="color:#FF007F;margin-right:8px;"></i>Admin Login</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group mb-3">
                <label for="email">Admin Email</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            <div class="form-group mb-3">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
