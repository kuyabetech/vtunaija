<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

// Network data plans (could be loaded from database or API)
$dataPlans = [
    'MTN' => [
        ['name' => '1GB', 'amount' => 500, 'validity' => '30 days'],
        ['name' => '2GB', 'amount' => 1000, 'validity' => '30 days'],
        ['name' => '5GB', 'amount' => 2000, 'validity' => '30 days'],
    ],
    'GLO' => [
        ['name' => '1.2GB', 'amount' => 500, 'validity' => '30 days'],
        ['name' => '2.9GB', 'amount' => 1000, 'validity' => '30 days'],
        ['name' => '5.8GB', 'amount' => 2000, 'validity' => '30 days'],
    ],
    'AIRTEL' => [
        ['name' => '1GB', 'amount' => 500, 'validity' => '30 days'],
        ['name' => '2GB', 'amount' => 1000, 'validity' => '30 days'],
        ['name' => '5GB', 'amount' => 2000, 'validity' => '30 days'],
    ],
    '9MOBILE' => [
        ['name' => '1GB', 'amount' => 500, 'validity' => '30 days'],
        ['name' => '2.5GB', 'amount' => 1000, 'validity' => '30 days'],
        ['name' => '5GB', 'amount' => 2000, 'validity' => '30 days'],
    ]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'classes/VtuService.php';
    
    $network = sanitizeInput($_POST['network']);
    $plan_id = sanitizeInput($_POST['plan_id']);
    $phone = sanitizeInput($_POST['phone']);
    
    if (empty($network) || empty($plan_id) || empty($phone)) {
        $error = 'All fields are required';
    } else {
        // Find selected plan
        $selectedPlan = null;
        foreach ($dataPlans[$network] as $plan) {
            if ($plan['name'] == $plan_id) {
                $selectedPlan = $plan;
                break;
            }
        }
        
        if (!$selectedPlan) {
            $error = 'Invalid data plan selected';
        } else {
            $vtu = new VtuService();
            $result = $vtu->buyData($network, $phone, $selectedPlan['amount'], $selectedPlan['name'], $user['id']);
            
            if ($result['status']) {
                $success = "Data purchase successful! {$selectedPlan['name']} data bundle has been added to {$phone}";
            } else {
                $error = $result['message'];
            }
        }
    }
}

// After user authentication
require_once 'classes/RecommendationEngine.php';
$recommendationEngine = new RecommendationEngine();
$recommendations = $recommendationEngine->getRecommendedDataPlans($user['id']);

include 'includes/header.php';
?>


<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Buy Data Bundle</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="network">Network</label>
                            <select class="form-control" id="network" name="network" required>
                                <option value="">Select Network</option>
                                <option value="MTN">MTN</option>
                                <option value="GLO">Glo</option>
                                <option value="AIRTEL">Airtel</option>
                                <option value="9MOBILE">9mobile</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="plan_id">Data Plan</label>
                            <select class="form-control" id="plan_id" name="plan_id" required>
                                <option value="">Select Network first</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <p>Wallet Balance: <strong>₦<?php echo number_format($user['wallet_balance'], 2); ?></strong></p>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Buy Data</button>
                    </form>
                    
                    <div id="planDetails" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6>Based on Your History</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recommendations['based_on_history'])): ?>
                    <div class="list-group">
                        <?php foreach ($recommendations['based_on_history'] as $plan): ?>
                            <button type="button" class="list-group-item list-group-item-action" onclick="fillDataForm('<?php echo $plan['network']; ?>', '<?php echo $plan['name']; ?>')">
                                <?php echo $plan['network']; ?> - <?php echo $plan['name']; ?><br>
                                <small>₦<?php echo number_format($plan['customer_price'], 2); ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No history yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6>Popular in Your Network</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recommendations['popular_in_network'])): ?>
                    <div class="list-group">
                        <?php foreach ($recommendations['popular_in_network'] as $plan): ?>
                            <button type="button" class="list-group-item list-group-item-action" onclick="fillDataForm('<?php echo $plan['network']; ?>', '<?php echo $plan['name']; ?>')">
                                <?php echo $plan['network']; ?> - <?php echo $plan['name']; ?><br>
                                <small>₦<?php echo number_format($plan['customer_price'], 2); ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No popular plans data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6>Special Discounts</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recommendations['discounted_plans'])): ?>
                    <div class="list-group">
                        <?php foreach ($recommendations['discounted_plans'] as $plan): ?>
                            <button type="button" class="list-group-item list-group-item-action" onclick="fillDataForm('<?php echo $plan['network']; ?>', '<?php echo $plan['name']; ?>')">
                                <?php echo $plan['network']; ?> - <?php echo $plan['name']; ?><br>
                                <small class="text-success"><del>₦<?php echo number_format($plan['base_price'], 2); ?></del> ₦<?php echo number_format($plan['customer_price'], 2); ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No current discounts</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function fillDataForm(network, planName) {
    document.getElementById('network').value = network;
    
    // Trigger change event to load plans
    const event = new Event('change');
    document.getElementById('network').dispatchEvent(event);
    
    // Wait for plans to load then select the matching one
    setTimeout(() => {
        const planSelect = document.getElementById('plan_id');
        for (let i = 0; i < planSelect.options.length; i++) {
            if (planSelect.options[i].text.includes(planName)) {
                planSelect.selectedIndex = i;
                planSelect.dispatchEvent(new Event('change'));
                break;
            }
        }
    }, 300);
}
</script>

<script>
// Dynamic plan loading based on selected network
document.getElementById('network').addEventListener('change', function() {
    const network = this.value;
    const planSelect = document.getElementById('plan_id');
    const planDetails = document.getElementById('planDetails');
    
    planSelect.innerHTML = '<option value="">Select Plan</option>';
    planDetails.innerHTML = '';
    
    if (!network) return;
    
    const plans = <?php echo json_encode($dataPlans); ?>;
    const networkPlans = plans[network];
    
    if (networkPlans) {
        networkPlans.forEach(plan => {
            const option = document.createElement('option');
            option.value = plan.name;
            option.textContent = `${plan.name} - ₦${plan.amount}`;
            option.dataset.amount = plan.amount;
            option.dataset.validity = plan.validity;
            planSelect.appendChild(option);
        });
    }
});

// Show plan details when selected
document.getElementById('plan_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const planDetails = document.getElementById('planDetails');
    
    if (selectedOption.value) {
        planDetails.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5>Plan Details</h5>
                    <p><strong>Name:</strong> ${selectedOption.value}</p>
                    <p><strong>Amount:</strong> ₦${selectedOption.dataset.amount}</p>
                    <p><strong>Validity:</strong> ${selectedOption.dataset.validity}</p>
                </div>
            </div>
        `;
    } else {
        planDetails.innerHTML = '';
    }
});
</script>


<?php include 'includes/spinner.php'; ?>
