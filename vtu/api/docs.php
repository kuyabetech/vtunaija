<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$apiKey = isset($_SESSION['api_key']) ? $_SESSION['api_key'] : (isset($_GET['demo']) ? 'DEMO_KEY' : '');

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
.api-docs-container {
  max-width: 1200px;
  margin: 3rem auto 2rem auto;
  font-family: 'Inter', Arial, sans-serif;
}
.list-group-item-action {
  background: #fff;
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: 8px;
  margin-bottom: 0.5rem;
  font-family: inherit;
  font-weight: 600;
  transition: background 0.2s, color 0.2s;
}
.list-group-item-action.active, .list-group-item-action:hover {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(37,99,235,0.08);
  color: var(--text);
  border: none;
}
.card-title, h1, h2, h4, h5 {
  color: var(--primary);
  font-family: inherit;
}
.badge-success, .badge-primary {
  font-family: inherit;
  font-size: 1rem;
  border-radius: 8px;
  padding: 0.4em 1em;
}
.badge-success {
  background: #ecfdf5;
  color: var(--success);
}
.badge-primary {
  background: var(--primary);
  color: #fff;
}
pre, code {
  background: #f9fafb;
  color: var(--primary);
  border-radius: 8px;
  padding: 1rem;
  font-family: 'Fira Mono', 'Consolas', monospace;
  font-size: 1rem;
}
.table {
  background: #fff;
  color: var(--text);
  border-radius: 8px;
  overflow: hidden;
}
.table th {
  background: #f3f4f6;
  color: var(--primary);
  font-family: inherit;
}
.table td, .table th {
  border-bottom: 1px solid var(--border);
}
.input-group .form-control {
  background: #f9fafb;
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: 8px;
  font-family: inherit;
  padding: 0.375rem 0.75rem;
}
.input-group-append .btn {
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-family: inherit;
  font-weight: 700;
  box-shadow: 0 2px 8px var(--primary-light);
}
.alert-info {
  background: #f9fafb;
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: 8px;
}
</style>

<div class="container api-docs-container mt-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#overview" class="list-group-item list-group-item-action">Overview</a>
                <a href="#authentication" class="list-group-item list-group-item-action">Authentication</a>
                <a href="#endpoints" class="list-group-item list-group-item-action">API Endpoints</a>
                <a href="#errors" class="list-group-item list-group-item-action">Errors</a>
                <a href="#rate-limiting" class="list-group-item list-group-item-action">Rate Limiting</a>
                <a href="#support" class="list-group-item list-group-item-action">Support</a>
            </div>
            
            <?php if (!empty($apiKey)): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Your API Key</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="apiKey" value="<?php echo htmlspecialchars($apiKey); ?>" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <p class="small text-muted">Keep this secret!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title" id="overview">VTU API Documentation</h1>
                    <p class="lead">Integrate our VTU services directly into your application</p>
                    
                    <p>Our API allows you to programmatically access all VTU services including airtime, data, bills payment, and more. All API endpoints require authentication via an API key.</p>
                    
                    <div class="alert alert-info">
                        <strong>Base URL:</strong> <?php echo SITE_URL; ?>/api/v1/
                    </div>
                    
                    <h2 class="mt-5" id="authentication">Authentication</h2>
                    <p>All API requests must include your API key in the <code>Authorization</code> header:</p>
                    
                    <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                    
                    <p>To get an API key:</p>
                    <ol>
                        <li>Login to your account</li>
                        <li>Go to API Settings</li>
                        <li>Generate a new API key</li>
                    </ol>
                    
                    <h2 class="mt-5" id="endpoints">API Endpoints</h2>
                    
                    <h4 class="mt-4">Get Account Balance</h4>
                    <p>Check your current wallet balance</p>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-success">GET</span>
                                <code>/balance</code>
                            </div>
                        </div>
                    </div>
                    
                    <h5>Response</h5>
                    <pre><code class="json">{
  "status": true,
  "balance": 15000.50,
  "currency": "NGN"
}</code></pre>
                    
                    <h4 class="mt-4">Buy Airtime</h4>
                    <p>Purchase airtime for any Nigerian network</p>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-primary">POST</span>
                                <code>/airtime</code>
                            </div>
                        </div>
                    </div>
                    
                    <h5>Request Body</h5>
                    <pre><code class="json">{
  "network": "MTN",
  "phone": "08012345678",
  "amount": 500
}</code></pre>
                    
                    <h5>Response</h5>
                    <pre><code class="json">{
  "status": true,
  "message": "Airtime purchase successful",
  "reference": "VTU_123456789",
  "balance": 14500.50
}</code></pre>
                    
                    <!-- More endpoints would be documented here -->
                    
                    <h2 class="mt-5" id="errors">Errors</h2>
                    <p>The API uses standard HTTP status codes:</p>
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>400</td>
                                <td>Bad Request - Invalid parameters</td>
                            </tr>
                            <tr>
                                <td>401</td>
                                <td>Unauthorized - Invalid API key</td>
                            </tr>
                            <tr>
                                <td>402</td>
                                <td>Payment Required - Insufficient balance</td>
                            </tr>
                            <tr>
                                <td>429</td>
                                <td>Too Many Requests - Rate limit exceeded</td>
                            </tr>
                            <tr>
                                <td>500</td>
                                <td>Internal Server Error</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h2 class="mt-5" id="rate-limiting">Rate Limiting</h2>
                    <p>API requests are limited to:</p>
                    <ul>
                        <li>60 requests per minute</li>
                        <li>1,000 requests per hour</li>
                    </ul>
                    <p>Exceeding these limits will result in a 429 status code.</p>
                    
                    <h2 class="mt-5" id="support">Support</h2>
                    <p>For API support, please contact <a href="mailto:api-support@yourdomain.com">api-support@yourdomain.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const apiKey = document.getElementById('apiKey');
    apiKey.select();
    document.execCommand('copy');
    
    // Show tooltip
    const tooltip = new bootstrap.Tooltip(apiKey, {
        title: 'Copied!',
        trigger: 'manual'
    });
    tooltip.show();
    
    setTimeout(() => {
        tooltip.hide();
    }, 2000);
}
</script>

<?php include '../includes/footer.php'; ?>