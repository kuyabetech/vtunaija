<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';

$db = DB::getInstance()->getConnection();

// Default date range (last 30 days)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if (!strtotime($startDate) || !strtotime($endDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
}

// Get summary stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(amount) as total_amount,
        AVG(amount) as avg_amount,
        COUNT(DISTINCT user_id) as unique_users
    FROM vtu_transactions
    WHERE status = 'successful'
    AND created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Get transactions by service type
$stmt = $db->prepare("
    SELECT 
        service_type,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM vtu_transactions
    WHERE status = 'successful'
    AND created_at BETWEEN ? AND ?
    GROUP BY service_type
    ORDER BY total_amount DESC
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$byService = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get transactions by network
$stmt = $db->prepare("
    SELECT 
        network,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM vtu_transactions
    WHERE status = 'successful'
    AND service_type IN ('airtime', 'data')
    AND created_at BETWEEN ? AND ?
    GROUP BY network
    ORDER BY total_amount DESC
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$byNetwork = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily transaction totals
$stmt = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM vtu_transactions
    WHERE status = 'successful'
    AND created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$dailyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
  <div class="dashboard-header">
    <h1 class="dashboard-title">Transaction Reports</h1>
    <button class="btn btn-success" onclick="exportToExcel()">
      <i class="fas fa-file-excel"></i> Export to Excel
    </button>
  </div>
  
  <div class="date-filter">
    <form method="GET" class="date-filter-form">
      <div class="date-filter-group">
        <label for="start_date" class="date-filter-label">From</label>
        <input type="date" class="date-filter-input" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
      </div>
      <div class="date-filter-group">
        <label for="end_date" class="date-filter-label">To</label>
        <input type="date" class="date-filter-input" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
      </div>
      <button type="submit" class="btn btn-primary">Apply</button>
    </form>
  </div>
  
  <div class="stats-grid">
    <div class="stat-card stat-card-primary">
      <h3 class="stat-card-title">Total Transactions</h3>
      <p class="stat-card-value"><?php echo number_format($summary['total_transactions']); ?></p>
    </div>
    
    <div class="stat-card stat-card-success">
      <h3 class="stat-card-title">Total Amount</h3>
      <p class="stat-card-value">₦<?php echo number_format($summary['total_amount'], 2); ?></p>
    </div>
    
    <div class="stat-card stat-card-info">
      <h3 class="stat-card-title">Avg. Amount</h3>
      <p class="stat-card-value">₦<?php echo number_format($summary['avg_amount'], 2); ?></p>
    </div>
    
    <div class="stat-card stat-card-warning">
      <h3 class="stat-card-title">Unique Users</h3>
      <p class="stat-card-value"><?php echo number_format($summary['unique_users']); ?></p>
    </div>
  </div>
  
  <div class="data-section">
    <div class="data-card">
      <div class="data-card-header">
        <h3 class="data-card-title">Transactions by Service Type</h3>
      </div>
      <div class="data-card-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>Service Type</th>
              <th>Count</th>
              <th>Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($byService as $service): ?>
              <tr>
                <td><?php echo ucfirst($service['service_type']); ?></td>
                <td><?php echo number_format($service['count']); ?></td>
                <td>₦<?php echo number_format($service['total_amount'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="data-card">
      <div class="data-card-header">
        <h3 class="data-card-title">Transactions by Network</h3>
      </div>
      <div class="data-card-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>Network</th>
              <th>Count</th>
              <th>Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($byNetwork as $network): ?>
              <tr>
                <td><?php echo $network['network']; ?></td>
                <td><?php echo number_format($network['count']); ?></td>
                <td>₦<?php echo number_format($network['total_amount'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="chart-card">
    <div class="chart-card-header">
      <h3 class="chart-card-title">Daily Transaction Volume</h3>
    </div>
    <div class="chart-card-body">
      <canvas id="dailyChart"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function exportToExcel() {
  // This would be implemented with a proper export function
  alert('Export to Excel functionality would be implemented here');
}

// Prepare data for chart
const dailyData = {
  dates: <?php echo json_encode(array_column($dailyTotals, 'date')); ?>,
  counts: <?php echo json_encode(array_column($dailyTotals, 'count')); ?>,
  amounts: <?php echo json_encode(array_column($dailyTotals, 'total_amount')); ?>
};

// Create chart
const ctx = document.getElementById('dailyChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: dailyData.dates,
    datasets: [
      {
        label: 'Transaction Count',
        data: dailyData.counts,
        backgroundColor: 'rgba(37, 99, 235, 0.5)',
        borderColor: 'rgba(37, 99, 235, 1)',
        borderWidth: 1,
        yAxisID: 'y'
      },
      {
        label: 'Amount (₦)',
        data: dailyData.amounts,
        backgroundColor: 'rgba(5, 150, 105, 0.5)',
        borderColor: 'rgba(5, 150, 105, 1)',
        borderWidth: 1,
        type: 'line',
        yAxisID: 'y1'
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    scales: {
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        title: {
          display: true,
          text: 'Transaction Count'
        }
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        grid: {
          drawOnChartArea: false,
        },
        title: {
          display: true,
          text: 'Amount (₦)'
        }
      }
    }
  }
});
</script>
<?php include '../includes/admin-footer.php'; ?>