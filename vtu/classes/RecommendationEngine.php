<?php
// Add this at the top if DiscountEngine is needed and exists as a separate file
require_once __DIR__ . '/DiscountEngine.php';
require_once __DIR__ . '/PricingEngine.php';

class RecommendationEngine {
    public function getRecommendedDataPlans($user_id) {
        $db = DB::getInstance()->getConnection();
        
        // Get user's historical purchases
        $stmt = $db->prepare("
            SELECT network, service_type, amount, COUNT(*) as count
            FROM vtu_transactions
            WHERE user_id = ?
            AND service_type = 'data'
            AND status = 'successful'
            GROUP BY network, service_type, amount
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get current popular plans
        $stmt = $db->query("
            SELECT network, amount, COUNT(*) as count
            FROM vtu_transactions
            WHERE service_type = 'data'
            AND status = 'successful'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY network, amount
            ORDER BY count DESC
            LIMIT 5
        ");
        $popular = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get discounted plans
        $discountEngine = new DiscountEngine();
        $allPlans = $this->getAllDataPlans();
        $discountedPlans = [];
        
        foreach ($allPlans as $plan) {
            $discount = $discountEngine->getDiscount('data', $plan['network'], $plan['base_price']);
            if ($discount && $discount['discount_value'] > 0) {
                $plan['discount'] = $discount;
                $discountedPlans[] = $plan;
            }
        }
        
        // Prepare recommendations
        $recommendations = [
            'based_on_history' => $this->enrichPlans($history),
            'popular_in_network' => $this->enrichPlans($popular),
            'discounted_plans' => $discountedPlans
        ];
        
        return $recommendations;
    }
    
    private function enrichPlans($plans) {
        $pricingEngine = new PricingEngine();
        $enriched = [];
        
        foreach ($plans as $plan) {
            $networkPlans = $pricingEngine->getDataPlans($plan['network']);
            
            foreach ($networkPlans as $p) {
                if ($p['base_price'] == $plan['amount']) {
                    $enriched[] = $p;
                    break;
                }
            }
        }
        
        return $enriched;
    }
    
    private function getAllDataPlans() {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT p.*, n.name as network_name
            FROM data_plans p
            JOIN networks n ON p.network = n.code
            WHERE p.is_active = TRUE
            ORDER BY p.network, p.base_price
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPersonalizedAirtimeAmounts($user_id) {
        $db = DB::getInstance()->getConnection();
        
        // Get user's typical airtime amounts
        $stmt = $db->prepare("
            SELECT amount, COUNT(*) as count
            FROM vtu_transactions
            WHERE user_id = ?
            AND service_type = 'airtime'
            AND status = 'successful'
            GROUP BY amount
            ORDER BY count DESC
            LIMIT 3
        ");
        $stmt->execute([$user_id]);
        $amounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no history, return default amounts
        if (empty($amounts)) {
            return [500, 1000, 2000];
        }
        
        // Return top 3 amounts
        return array_map(function($a) {
            return $a['amount'];
        }, $amounts);
    }
}