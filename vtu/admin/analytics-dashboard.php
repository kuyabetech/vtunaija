<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin-auth.php';
require_once '../classes/AnalyticsEngine.php';

$analytics = new AnalyticsEngine();
$transactionTrends = $analytics->getTransactionTrends('30d');
$userStats = $analytics->getUserAcquisitionStats('30d');
$revenuePrediction = $analytics->predictNextWeekRevenue();
$topServices = $analytics->getTopPerformingServices();

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Analytics Dashboard</h2>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group">
                <button class="btn btn-outline-secondary" onclick="updateChartData('7d')">7 Days</button>
                <button class="btn btn-outline-secondary" onclick="updateChartData('30d')">30 Days</button>
                <button class="btn btn-outline-secondary" onclick="updateChartData('90d')">90 Days</button>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Transaction Trends</h4>
                </div>
                <div class="card-body">
                    <canvas id="transactionTrendsChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Revenue Prediction</h4>
                </div>
                <div class="card-body text-center">
                    <?php if ($revenuePrediction): ?>
                        <h2 class="text-primary">₦<?php echo number_format($revenuePrediction, 2); ?></h2>
                        <p>Predicted revenue for next week</p>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-success" style="width: 75%"></div>
                        </div>
                        <small class="text-muted">Based on last 4 weeks data</small>
                    <?php else: ?>
                        <div class="alert alert-info">Not enough data for prediction</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>User Acquisition</h4>
                </div>
                <div class="card-body">
                    <canvas id="userAcquisitionChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Top Performing Services</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Network</th>
                                    <th>Transactions</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topServices as $service): ?>
                                    <tr>
                                        <td><?php echo ucfirst($service['service_type']); ?></td>
                                        <td><?php echo $service['network']; ?></td>
                                        <td><?php echo number_format($service['transaction_count']); ?></td>
                                        <td>₦<?php echo number_format($service['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data for charts
const transactionData = {
    dates: <?php echo json_encode(array_column($transactionTrends, 'date')); ?>,
    amounts: <?php echo json_encode(array_column($transactionTrends, 'total_amount')); ?>,
    types: <?php echo json_encode(array_column($transactionTrends, 'service_type')); ?>
};

const userData = {
    dates: <?php echo json_encode(array_column($userStats, 'date')); ?>,
    newUsers: <?php echo json_encode(array_column($userStats, 'new_users')); ?>,
    activatedUsers: <?php echo json_encode(array_column($userStats, 'activated_users')); ?>
};

// Initialize charts
const transactionCtx = document.getElementById('transactionTrendsChart').getContext('2d');
const transactionChart = new Chart(transactionCtx, {
    type: 'line',
    data: {
        labels: transactionData.dates,
        datasets: [
            {
                label: 'Total Amount',
                data: transactionData.amounts,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        const index = context.dataIndex;
                        return 'Service: ' + transactionData.types[index];
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

const userCtx = document.getElementById('userAcquisitionChart').getContext('2d');
const userChart = new Chart(userCtx, {
    type: 'bar',
    data: {
        labels: userData.dates,
        datasets: [
            {
                label: 'New Users',
                data: userData.newUsers,
                backgroundColor: 'rgba(54, 162, 235, 0.5)'
            },
            {
                label: 'Activated Users',
                data: userData.activatedUsers,
                backgroundColor: 'rgba(75, 192, 192, 0.5)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function updateChartData(period) {
    // In a real implementation, this would fetch new data via AJAX
    alert('Would fetch data for ' + period + ' period via AJAX');
}
</script>

<?php include '../includes/admin-footer.php'; ?>