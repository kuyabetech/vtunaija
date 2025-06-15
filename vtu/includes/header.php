<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
// Fetch site name and logo from settings table
$siteName = 'VTU Platform';
$siteLogo = 'assets/images/logo.png';
try {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT site_name, site_logo FROM settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        if (!empty($row['site_name'])) {
            $siteName = $row['site_name'];
        }
        if (!empty($row['site_logo'])) {
            $siteLogo = $row['site_logo'];
        }
    }
} catch (Exception $e) {
    // fallback to default
}
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
} else {
    $user = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : htmlspecialchars($siteName); ?></title>
    <!-- Nigeria Green & White Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/flatly/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">

<!-- Favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16x16.png">
<link rel="manifest" href="/assets/favicon/site.webmanifest">

<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="assets/css/main.css"> -->
    <link rel="stylesheet" href="assets/css/mobile.css">
    
    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#007bff">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCsrfToken(); ?>">
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="announcement-bar bg-primary text-white py-2">
        <div class="container text-center">
            <p class="mb-0">Enjoy 5% bonus on all airtime purchases this week! <a href="promo.php" class="text-warning fw-bold">Learn more</a></p>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo htmlspecialchars($siteName); ?> Logo" height="40" class="d-inline-block align-top me-2">
                <span class="brand-text ms-2"><?php echo htmlspecialchars($siteName); ?></span>
            </a>
            
            <button class="navbar-toggler arcade-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation" style="background:blue;border:none;border-radius:1.2rem;padding:0.5rem 0.9rem;box-shadow:0 2px 12px #2C2A2BFF;outline:none;">
                <span style="display:inline-block;width:28px;height:28px;position:relative;">
                    <span style="display:block;width:100%;height:4px;background:#fff;border-radius:2px;box-shadow:0 0 8px #12110FFF;position:absolute;top:4px;left:0;"></span>
                    <span style="display:block;width:100%;height:4px;background:#fff;border-radius:2px;box-shadow:0 0 8px #0C0C0DFF;position:absolute;top:12px;left:0;"></span>
                    <span style="display:block;width:100%;height:4px;background:#fff;border-radius:2px;box-shadow:0 0 8px #C0C0C0;position:absolute;top:20px;left:0;"></span>
                </span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'airtime' ? 'active' : ''; ?>" href="airtime.php">Airtime</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'data' ? 'active' : ''; ?>" href="data.php">Data</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'bills' ? 'active' : ''; ?>" href="bills.php">Bills Payment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'cable' ? 'active' : ''; ?>" href="cable.php">Cable TV</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isLoggedIn()): ?>
                        <!-- Notification Bell -->
                        <div class="dropdown me-3">
                            <a href="#" class="d-flex align-items-center text-decoration-none position-relative" id="notifDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-bell" style="font-size:1.5rem;color:#2563eb;"></i>
                                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.7rem;display:none;">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width:320px;max-width:370px;">
                                <li class="dropdown-header">Notifications</li>
                                <li>
                                    <div id="notif-list" style="max-height:320px;overflow-y:auto;">
                                        <div class="text-center text-muted p-3" id="notif-empty">No notifications.</div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="notifications.php" class="dropdown-item text-primary text-center">View All</a></li>
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                <div class="me-2 d-none d-sm-block text-end">
                                    <small class="text-dark d-block">Wallet Balance</small>
                                    <strong style="color:dark;font-size:1.2rem;">
                                        ₦<?php echo isset($user['wallet_balance']) && $user['wallet_balance'] !== null ? number_format((float)$user['wallet_balance'], 2) : '0.00'; ?>
                                    </strong>
                                </div>
                                <img src="<?php echo getUserAvatar($user); ?>" alt="User" width="36" height="36" class="rounded-circle border border-2 border-white">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="wallet.php"><i class="fas fa-wallet me-2"></i>Wallet</a></li>
                                <li><a class="dropdown-item" href="transactions.ph"><i class="fas fa-history me-2"></i>Transactions</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="user-settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="btn btn-outline-primary me-2">Login</a>
                        <a href="/register" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation (Flutter-style) -->
    <div class="mobile-bottom-nav d-lg-none fixed-bottom">
        <a href="dashboard.php" class="nav-item<?php echo $currentPage === 'home' ? ' active' : ''; ?>" style="flex:1;text-align:center;padding:0.5rem 0;<?php echo $currentPage === 'home' ? 'color:#fff;background:linear-gradient(135deg,#2563eb 60%,#059669 100%);box-shadow:0 2px 16px #2563eb;' : 'color:#059669;'; ?>font-family:'Press Start 2P',Arial,sans-serif;font-size:0.95rem;transition:color 0.2s;">
            <i class="fas fa-home" style="font-size:1.5rem;<?php echo $currentPage === 'home' ? 'color:#fff;text-shadow:0 0 8px #059669,0 0 16px #2563eb;' : 'color:#059669;'; ?>transition:color 0.2s;"></i><br>
            <span style="font-size:0.85rem;">Home</span>
        </a>
        <a href="airtime.php" class="nav-item<?php echo $currentPage === 'airtime' ? ' active' : ''; ?>" style="flex:1;text-align:center;padding:0.5rem 0;<?php echo $currentPage === 'airtime' ? 'color:#fff;background:linear-gradient(135deg,#059669 60%,#2563eb 100%);box-shadow:0 2px 16px #059669;' : 'color:#059669;'; ?>font-family:'Press Start 2P',Arial,sans-serif;font-size:0.95rem;transition:color 0.2s;">
            <i class="fas fa-phone-alt" style="font-size:1.5rem;<?php echo $currentPage === 'airtime' ? 'color:#fff;text-shadow:0 0 8px #2563eb,0 0 16px #059669;' : 'color:#059669;'; ?>transition:color 0.2s;"></i><br>
            <span style="font-size:0.85rem;">Airtime</span>
        </a>
        <a href="data.php" class="nav-item<?php echo $currentPage === 'data' ? ' active' : ''; ?>" style="flex:1;text-align:center;padding:0.5rem 0;<?php echo $currentPage === 'data' ? 'color:#fff;background:linear-gradient(135deg,#2563eb 60%,#C0C0C0 100%);box-shadow:0 2px 16px #2563eb;' : 'color:#059669;'; ?>font-family:'Press Start 2P',Arial,sans-serif;font-size:0.95rem;transition:color 0.2s;">
            <i class="fas fa-database" style="font-size:1.5rem;<?php echo $currentPage === 'data' ? 'color:#fff;text-shadow:0 0 8px #C0C0C0,0 0 16px #2563eb;' : 'color:#059669;'; ?>transition:color 0.2s;"></i><br>
            <span style="font-size:0.85rem;">Data</span>
        </a>
        <a href="wallet.php" class="nav-item<?php echo $currentPage === 'wallet' ? ' active' : ''; ?>" style="flex:1;text-align:center;padding:0.5rem 0;<?php echo $currentPage === 'wallet' ? 'color:#fff;background:linear-gradient(135deg,#059669 60%,#C0C0C0 100%);box-shadow:0 2px 16px #059669;' : 'color:#059669;'; ?>font-family:'Press Start 2P',Arial,sans-serif;font-size:0.95rem;transition:color 0.2s;">
            <i class="fas fa-wallet" style="font-size:1.5rem;<?php echo $currentPage === 'wallet' ? 'color:#fff;text-shadow:0 0 8px #059669,0 0 16px #C0C0C0;' : 'color:#059669;'; ?>transition:color 0.2s;"></i><br>
            <span style="font-size:0.85rem;">Wallet</span>
        </a>
        <a href="profile.php" class="nav-item<?php echo $currentPage === 'account' ? ' active' : ''; ?>" style="flex:1;text-align:center;padding:0.5rem 0;<?php echo $currentPage === 'account' ? 'color:#fff;background:linear-gradient(135deg,#2563eb 60%,#059669 100%);box-shadow:0 2px 16px #2563eb;' : 'color:#059669;'; ?>font-family:'Press Start 2P',Arial,sans-serif;font-size:0.95rem;transition:color 0.2s;">
            <i class="fas fa-user" style="font-size:1.5rem;<?php echo $currentPage === 'account' ? 'color:#fff;text-shadow:0 0 8px #059669,0 0 16px #2563eb;' : 'color:#059669;'; ?>transition:color 0.2s;"></i><br>
            <span style="font-size:0.85rem;">Account</span>
        </a>
    </div>
    <style>
    .mobile-bottom-nav {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-around;
        align-items: center;
        height: 56px;
        z-index: 1000;
    }
    .mobile-bottom-nav .nav-item {
        flex: 1;
        text-align: center;
        padding: 0.5rem 0;
        border-radius: 0.5rem 0.5rem 0 0;
        font-family: Arial, sans-serif;
        font-size: 1rem;
        color: #2563eb;
        background: transparent;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .mobile-bottom-nav .nav-item.active {
        color: #fff !important;
        background: #2563eb;
        font-weight: 600;
        box-shadow: 0 2px 8px #2563eb33;
    }
    .mobile-bottom-nav .nav-item i {
        display: block;
        margin-bottom: 2px;
        font-size: 1.3rem;
        transition: color 0.2s;
        color: #2563eb;
    }
    .mobile-bottom-nav .nav-item.active i {
        color: #fff !important;
        text-shadow: none;
    }
    .mobile-bottom-nav .nav-item span {
        font-size: 0.9rem;
    }
    @media (max-width: 991.98px) {
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
            transform: translateX(0) !important;
        }
        .dropdown-menu {
            min-width: 220px;
            max-width: 90vw;
            left: auto !important;
            right: 0 !important;
        }
    }
    .dropdown-menu-end {
        right: 0;
        left: auto;
    }
    </style>
    <!-- Main Content Container -->
    <main class="main-content">
        <!-- Page-specific content will be inserted here -->
    </main>
    <script>
    // Navbar toggler for mobile
    document.addEventListener('DOMContentLoaded', function() {
        var toggler = document.querySelector('.navbar-toggler');
        var nav = document.getElementById('mainNav');
        if (toggler && nav) {
            toggler.addEventListener('click', function() {
                nav.classList.toggle('show');
            });
        }
    });

    // Highlight active nav item in mobile bottom nav
    document.addEventListener('DOMContentLoaded', function() {
        var navItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');
        navItems.forEach(function(item) {
            item.addEventListener('click', function() {
                navItems.forEach(function(i) { i.classList.remove('active'); });
                item.classList.add('active');
            });
        });
    });
    </script>
    <!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome Icons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Notification bell fetch
    <?php if (isLoggedIn()): ?>
    function fetchNotifications() {
        fetch('fetch-notifications.php')
            .then(response => response.json())
            .then(data => {
                var notifList = document.getElementById('notif-list');
                var notifBadge = document.getElementById('notif-badge');
                var notifEmpty = document.getElementById('notif-empty');
                notifList.innerHTML = '';
                if (data.length > 0) {
                    notifBadge.style.display = 'inline-block';
                    notifBadge.textContent = data.length;
                    data.forEach(function(n) {
                        var item = document.createElement('div');
                        item.className = 'dropdown-item';
                        item.innerHTML = '<div><strong>' + n.title + '</strong></div><div style="font-size:0.95em;color:#6b7280;">' + n.message + '</div><div style="font-size:0.8em;color:#aaa;">' + n.created_at + '</div>';
                        notifList.appendChild(item);
                    });
                } else {
                    notifBadge.style.display = 'none';
                    notifList.innerHTML = '<div class="text-center text-muted p-3" id="notif-empty">No notifications.</div>';
                }
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications();
        // Optionally, refresh notifications every 60 seconds
        setInterval(fetchNotifications, 60000);
    });
    <?php endif; ?>
    </script>
</body>
</html>

