<!-- Admin Header -->
<?php
// Fetch site name from settings table for display in admin header
$siteName = 'VTUNaija';
try {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT site_name FROM settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['site_name'])) {
        $siteName = $row['site_name'];
    }
} catch (Exception $e) {
    // fallback to default
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script type="module" src="../assets/js/main.js" defer></script>
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: #e5e7eb;
        }
        .admin-navbar {
            background: var(--card-bg);
            border-bottom: 4px solid var(--primary);
            font-family: 'Inter', Arial, sans-serif;
        }
        .admin-navbar .navbar-brand {
            color: var(--primary);
            font-size: 1.3rem;
            letter-spacing: 2px;
        }
        .admin-navbar .nav-link, .admin-navbar .navbar-brand {
            color: var(--primary) !important;
        }
        .admin-navbar .nav-link.active {
            color: #fff !important;
            background: var(--primary);
            border-radius: 8px;
        }
        .admin-sidebar .nav-link {
            color: var(--primary);
            font-size: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 8px 0 0 8px;
            margin-bottom: 0.5rem;
            transition: background 0.2s, color 0.2s;
        }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
            background: var(--primary);
            color: #fff;
        }
        .admin-sidebar {
            min-height: 100vh;
            box-shadow: 2px 0 16px rgba(37,99,235,0.06);
            background: var(--card-bg);
        }
        .sidebar-header {
            border-bottom: 2px solid var(--primary);
        }
        .sidebar-header .navbar-brand {
            color: var(--primary);
            font-size: 1.3rem;
            letter-spacing: 2px;
        }
        @media (max-width: 991.98px) {
            .admin-sidebar { display: none !important; }
            .flex-grow-1 { margin-left: 0 !important; }
        }
        @media (max-width: 575.98px) {
            .admin-navbar .navbar-brand {
                font-size: 1rem;
            }
            .admin-navbar .nav-link {
                font-size: 0.95rem;
                padding: 0.7rem 1rem;
            }
            .sidebar-header {
                padding: 1rem 0 !important;
            }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="admin-sidebar d-none d-lg-block" style="width:220px;min-height:100vh;position:fixed;top:0;left:0;z-index:1050;">
        <div class="sidebar-header text-center py-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-cogs me-2"></i><?php echo htmlspecialchars($siteName); ?> Admin
            </a>
        </div>
        <ul class="nav flex-column mt-4">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
            <li class="nav-item"><a class="nav-link" href="services.php"><i class="fas fa-cogs me-2"></i>Services</a></li>
            <li class="nav-item"><a class="nav-link" href="analytics-dashboard.php"><i class="fas fa-chart-line me-2"></i>Analytics</a></li>
            <li class="nav-item"><a class="nav-link" href="report.php"><i class="fas fa-file-alt me-2"></i>Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="fas fa-history me-2"></i>Transactions</a></li>
            <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
            <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </nav>
    <!-- Main Content Wrapper -->
    <div class="flex-grow-1" style="margin-left:220px;">
        <!-- Top Navbar (for mobile) -->
        <nav class="navbar navbar-expand-lg admin-navbar shadow-sm d-lg-none">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php"><i class="fas fa-cogs me-2"></i><?php echo htmlspecialchars($siteName); ?> Admin</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="adminNav">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="transactions.php">Transactions</a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
