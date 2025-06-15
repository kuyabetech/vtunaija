/**
 * VTU Platform Main JavaScript
 * Contains all core functionality for the application
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize all components
  initMobileMenu();
  initFormValidations();
  initPasswordToggles();
  initDarkModeToggle();
  initToastNotifications();
  initServiceTabs();
  initWalletActions();
  initTransactionFilters();
});

// ===== Mobile Menu Toggle =====
function initMobileMenu() {
  const menuToggle = document.querySelector('.navbar-toggler');
  const menu = document.querySelector('.navbar-collapse');
  
  if (menuToggle && menu) {
    menuToggle.addEventListener('click', function() {
      menu.classList.toggle('show');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!menu.contains(e.target) && !menuToggle.contains(e.target)) {
        menu.classList.remove('show');
      }
    });
  }
}

// ===== Form Validations =====
function initFormValidations() {
  const forms = document.querySelectorAll('.needs-validation');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      form.classList.add('was-validated');
    }, false);
  });
}

// ===== Password Visibility Toggle =====
function initPasswordToggles() {
  const toggles = document.querySelectorAll('.password-toggle');
  
  toggles.forEach(toggle => {
    toggle.addEventListener('click', function() {
      const input = this.previousElementSibling;
      const icon = this.querySelector('i');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });
}

// ===== Dark Mode Toggle =====
function initDarkModeToggle() {
  const toggle = document.getElementById('darkModeToggle');
  
  if (toggle) {
    // Check for saved preference or OS preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedMode = localStorage.getItem('darkMode');
    
    if (savedMode === 'dark' || (savedMode === null && prefersDark)) {
      document.body.classList.add('dark-mode');
      toggle.checked = true;
    }
    
    // Toggle handler
    toggle.addEventListener('change', function() {
      if (this.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'dark');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'light');
      }
    });
  }
}

// ===== Toast Notifications =====
function initToastNotifications() {
  const toasts = document.querySelectorAll('.toast');
  
  toasts.forEach(toast => {
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Auto-hide after delay
    const delay = toast.dataset.delay || 5000;
    setTimeout(() => bsToast.hide(), delay);
  });
}

// ===== Service Tabs =====
function initServiceTabs() {
  const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
  
  tabLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const target = document.querySelector(this.getAttribute('href'));
      const tabPane = target.closest('.tab-pane');
      
      // Hide all tab panes
      document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active', 'show');
      });
      
      // Show selected pane
      tabPane.classList.add('active', 'show');
      
      // Update active tab link
      this.closest('.nav').querySelectorAll('.nav-link').forEach(navLink => {
        navLink.classList.remove('active');
      });
      this.classList.add('active');
    });
  });
}

// ===== Wallet Actions =====
function initWalletActions() {
  // Fund wallet amount buttons
  document.querySelectorAll('.quick-amount').forEach(button => {
    button.addEventListener('click', function() {
      document.getElementById('amount').value = this.dataset.amount;
    });
  });
  
  // Wallet transfer form
  const transferForm = document.getElementById('transferForm');
  if (transferForm) {
    transferForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const amount = parseFloat(document.getElementById('transferAmount').value);
      const recipient = document.getElementById('recipient').value;
      const pin = document.getElementById('transferPin').value;
      
      // Validate inputs
      if (!amount || !recipient || !pin) {
        showAlert('Please fill all fields', 'danger');
        return;
      }
      
      // Submit via AJAX
      fetch('/api/wallet/transfer', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          amount: amount,
          recipient: recipient,
          pin: pin
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showAlert('Transfer successful', 'success');
          updateWalletBalance(data.newBalance);
          transferForm.reset();
        } else {
          showAlert(data.message || 'Transfer failed', 'danger');
        }
      })
      .catch(error => {
        showAlert('Network error. Please try again.', 'danger');
      });
    });
  }
}

// ===== Transaction Filters =====
function initTransactionFilters() {
  const filterForm = document.getElementById('transactionFilter');
  if (filterForm) {
    filterForm.addEventListener('change', function() {
      const formData = new FormData(filterForm);
      const params = new URLSearchParams();
      
      for (const [key, value] of formData.entries()) {
        if (value) params.append(key, value);
      }
      
      // Update URL without reload
      history.replaceState(null, null, '?' + params.toString());
      
      // Filter transactions
      filterTransactions(params);
    });
  }
}

function filterTransactions(params) {
  fetch('/api/transactions?' + params.toString())
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector('#transactionsTable tbody');
      tbody.innerHTML = '';
      
      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No transactions found</td></tr>';
        return;
      }
      
      data.forEach(txn => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${new Date(txn.created_at).toLocaleDateString()}</td>
          <td>${txn.service_type}</td>
          <td>${txn.network || 'N/A'}</td>
          <td>${txn.phone || 'N/A'}</td>
          <td>₦${txn.amount.toLocaleString()}</td>
          <td><span class="badge bg-${getStatusClass(txn.status)}">${txn.status}</span></td>
        `;
        tbody.appendChild(row);
      });
    });
}

function getStatusClass(status) {
  switch (status) {
    case 'successful': return 'success';
    case 'failed': return 'danger';
    case 'pending': return 'warning';
    default: return 'secondary';
  }
}

// ===== Helper Functions =====
function showAlert(message, type) {
  const alert = document.createElement('div');
  alert.className = `alert alert-${type} alert-dismissible fade show`;
  alert.role = 'alert';
  alert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;
  
  const container = document.getElementById('alerts');
  container.prepend(alert);
  
  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    const bsAlert = bootstrap.Alert.getInstance(alert);
    if (bsAlert) bsAlert.close();
  }, 5000);
}

function updateWalletBalance(balance) {
  const balanceElements = document.querySelectorAll('.wallet-balance');
  balanceElements.forEach(el => {
    el.textContent = '₦' + parseFloat(balance).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  });
}

// ===== API Helper =====
async function apiRequest(endpoint, method = 'GET', data = null) {
  const options = {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  };
  
  if (data) {
    options.body = JSON.stringify(data);
  }
  
  const response = await fetch(endpoint, options);
  
  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }
  
  return response.json();
}

document.getElementById('toggle-dark-mode').addEventListener('click', function() {
  document.body.classList.toggle('dark-mode');
  // Optionally, persist preference in localStorage
  if(document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', '1');
  } else {
    localStorage.removeItem('darkMode');
  }
});
if(localStorage.getItem('darkMode')) {
  document.body.classList.add('dark-mode');
}