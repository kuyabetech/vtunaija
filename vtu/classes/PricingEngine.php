<?php
class PricingEngine {
    private $markupPercentage;
    private $fixedMarkup;
    
    public function __construct() {
        // Load pricing configuration from database or settings
        $this->loadConfiguration();
    }
    
    private function loadConfiguration() {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM pricing_config LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            $this->markupPercentage = isset($config['markup_percentage']) ? $config['markup_percentage'] : 0;
            $this->fixedMarkup = isset($config['fixed_markup']) ? $config['fixed_markup'] : 0;
        } else {
            // Default values
            $this->markupPercentage = 2.5; // 2.5%
            $this->fixedMarkup = 0;
        }
    }
    
    public function calculateCustomerPrice($costPrice, $serviceType = null) {
        $markup = $this->fixedMarkup;
        
        // Apply percentage markup
        $markup += ($costPrice * $this->markupPercentage) / 100;
        
        // Round up to nearest 10 Naira
        $customerPrice = ceil(($costPrice + $markup) / 10) * 10;
        
        return $customerPrice;
    }
    
    public function getDataPlans($network) {
        $db = DB::getInstance()->getConnection();
        
        // Get base plans from database
        $stmt = $db->prepare("SELECT * FROM data_plans 
                             WHERE network = ? 
                             AND is_active = TRUE
                             ORDER BY base_price");
        $stmt->execute([$network]);
        $basePlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Apply pricing
        $plans = [];
        foreach ($basePlans as $plan) {
            $plans[] = [
                'name' => $plan['name'],
                'description' => $plan['description'],
                'validity' => $plan['validity'],
                'base_price' => $plan['base_price'],
                'customer_price' => $this->calculateCustomerPrice($plan['base_price']),
                'discount' => $this->getDiscountForPlan($plan['id'])
            ];
        }
        
        return $plans;
    }
    
    private function getDiscountForPlan($plan_id) {
        $discountEngine = new DiscountEngine();
        return $discountEngine->getDiscount('data', null, 0); // Pass appropriate parameters
    }
    
    public function updatePricingConfiguration($percentage, $fixed) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE pricing_config 
                             SET markup_percentage = ?, fixed_markup = ?");
        $stmt->execute([$percentage, $fixed]);
        
        // Reload configuration
        $this->loadConfiguration();
        
        return true;
    }
}