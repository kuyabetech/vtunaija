/**
 * Dashboard-Specific JavaScript
 * Contains functionality for the admin/user dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
  initSidebarToggle();
  initChartJS();
  initDataTables();
  initDashboardFilters();
});

// ===== Sidebar Toggle =====
function initSidebarToggle() {
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
    });
  }
}

// ===== Chart.js Initialization =====
function initChartJS() {
  const charts = document.querySelectorAll('[data-chart]');
  
  charts.forEach(chartEl => {
    const ctx = chartEl.getContext('2d');
    const type = chartEl.dataset.chart;
    const data = JSON.parse(chartEl.dataset.chartData);
    
    new Chart(ctx, {
      type: type,
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  });
}

// ===== DataTables Initialization =====
function initDataTables() {
  const tables = document.querySelectorAll('.data-table');
  
  tables.forEach(table => {
    new simpleDatatables.DataTable(table, {
      perPage: 10,
      perPageSelect: [10, 25, 50, 100],
      labels: {
        placeholder: "Search...",
        perPage: "{select} entries per page",
        noRows: "No data found",
        info: "Showing {start} to {end} of {rows} entries"
      }
    });
  });
}

// ===== Dashboard Filters =====
function initDashboardFilters() {
  const dateRangePicker = document.getElementById('dashboardDateRange');
  
  if (dateRangePicker) {
    flatpickr(dateRangePicker, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      defaultDate: [new Date(Date.now() - 30 * 24 * 60 * 60 * 1000), new Date()],
      onChange: function(selectedDates) {
        if (selectedDates.length === 2) {
          updateDashboardStats(selectedDates[0], selectedDates[1]);
        }
      }
    });
    
    // Initial load with default dates
    const defaultDates = dateRangePicker._flatpickr.selectedDates;
    if (defaultDates.length === 2) {
      updateDashboardStats(defaultDates[0], defaultDates[1]);
    }
  }
}

function updateDashboardStats(startDate, endDate) {
  const start = startDate.toISOString().split('T')[0];
  const end = endDate.toISOString().split('T')[0];
  
  fetch(`/api/dashboard/stats?start=${start}&end=${end}`)
    .then(response => response.json())
    .then(data => {
      // Update transaction stats
      document.getElementById('totalTransactions').textContent = data.total_transactions;
      document.getElementById('successRate').textContent = data.success_rate + '%';
      document.getElementById('totalAmount').textContent = '₦' + data.total_amount.toLocaleString();
      
      // Update chart data
      updateChartData('transactionsChart', data.chart_data);
    });
}

function updateChartData(chartId, data) {
  const chart = Chart.getChart(chartId);
  if (chart) {
    chart.data = data;
    chart.update();
  }
}