<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

// Get system stats
$db = DB::getInstance()->getConnection();

// Server health
$load = sys_getloadavg();
$memory = $this->getMemoryUsage();
$disk = $this->getDiskUsage();

// Database stats
$stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$stmt = $db->query("SELECT COUNT(*) as active_sessions FROM sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
$activeSessions = $stmt->fetch(PDO::FETCH_ASSOC)['active_sessions'];

// Transaction stats
$stmt = $db->query("SELECT 
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 ELSE 0 END) as last_hour,
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as last_day,
    SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
    COUNT(*) as total
    FROM vtu_transactions");
$txnStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Provider status
$vtuAggregator = new VtuAggregator();
$providerStatus = $vtuAggregator->getProviderStatus();
$providerBalances = $vtuAggregator->getBalanceAcrossProviders();

// Recent errors
$stmt = $db->query("SELECT * FROM system_errors ORDER BY created_at DESC LIMIT 10");
$recentErrors = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>System Monitor</h2>
        </div>
        <div class="col-md-6 text-right">
            <span class="badge badge-success">Last updated: <?php echo date('H:i:s'); ?></span>
            <button class="btn btn-sm btn-outline-secondary ml-2" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
    
    <div class="row">
        <!-- Server Health -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Server Health</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>CPU Load</h6>
                        <div class="progress">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: <?php echo min(100, $load[0] * 100 / 4); ?>%"
                                 aria-valuenow="<?php echo $load[0]; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="4">
                                <?php echo round($load[0], 2); ?> (1m)
                            </div>
                        </div