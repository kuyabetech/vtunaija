/**
 * Utility Functions for VTU Platform
 */

// ===== Currency Formatting =====
function formatCurrency(amount, currency = 'NGN') {
  return new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2
  }).format(amount);
}

// ===== Phone Number Validation =====
function validatePhoneNumber(phone) {
  // Nigerian phone number regex
  const regex = /^(0|\+234)([789][01]\d{8})$/;
  return regex.test(phone);
}

// ===== Network Detection =====
function detectNetwork(phone) {
  const prefixes = {
    'MTN': ['0803', '0806', '0703', '0706', '0813', '0816', '0810', '0814', '0903', '0906'],
    'GLO': ['0805', '0807', '0705', '0815', '0811', '0905'],
    'AIRTEL': ['0802', '0808', '0708', '0812', '0701', '0902', '0907'],
    '9MOBILE': ['0809', '0818', '0817', '0909', '0908']
  };
  
  const prefix = phone.substring(0, 4);
  
  for (const [network, codes] of Object.entries(prefixes)) {
    if (codes.includes(prefix)) {
      return network;
    }
  }
  
  return null;
}

// ===== Session Management =====
function checkSession() {
  return fetch('/api/session/check')
    .then(response => response.json())
    .then(data => {
      if (!data.valid) {
        window.location.href = '/login?expired=1';
      }
    });
}

// Set up session checking every 5 minutes
setInterval(checkSession, 5 * 60 * 1000);

// ===== Clipboard Copy =====
function copyToClipboard(text, element = null) {
  navigator.clipboard.writeText(text).then(() => {
    if (element) {
      const originalText = element.textContent;
      element.textContent = 'Copied!';
      setTimeout(() => {
        element.textContent = originalText;
      }, 2000);
    }
  }).catch(err => {
    console.error('Failed to copy: ', err);
  });
}

// ===== Time Formatting =====
function formatTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
}

// ===== Throttle Function =====
function throttle(func, limit) {
  let lastFunc;
  let lastRan;
  return function() {
    const context = this;
    const args = arguments;
    if (!lastRan) {
      func.apply(context, args);
      lastRan = Date.now();
    } else {
      clearTimeout(lastFunc);
      lastFunc = setTimeout(function() {
        if ((Date.now() - lastRan) >= limit) {
          func.apply(context, args);
          lastRan = Date.now();
        }
      }, limit - (Date.now() - lastRan));
    }
  };
}

// ===== Export Utils =====
window.vtuUtils = {
  formatCurrency,
  validatePhoneNumber,
  detectNetwork,
  copyToClipboard,
  formatTime,
  formatDate,
  throttle
};