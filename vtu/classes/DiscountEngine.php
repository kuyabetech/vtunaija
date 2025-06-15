<?php
class DiscountEngine {
    public function getDiscount($service_type, $network = null, $amount = 0) {
        $db = DB::getInstance()->getConnection();
        
        $query = "SELECT * FROM discount_rules 
                 WHERE service_type = ? 
                 AND is_active = TRUE 
                 AND start_date <= NOW() 
                 AND (end_date IS NULL OR end_date >= NOW()) 
                 AND min_amount <= ?";
        
        $params = [$service_type, $amount];
        
        if ($network) {
            $query .= " AND (network IS NULL OR network = ?)";
            $params[] = $network;
        }
        
        $query .= " ORDER BY discount_value DESC LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rule) {
            return null;
        }
        
        return [
            'rule_id' => $rule['id'],
            'discount_type' => $rule['discount_type'],
            'discount_value' => $rule['discount_value'],
            'calculated_discount' => $this->calculateDiscount($rule, $amount),
            'original_amount' => $amount,
            'discounted_amount' => $this->applyDiscount($rule, $amount)
        ];
    }
    
    private function calculateDiscount($rule, $amount) {
        if ($rule['discount_type'] == 'percentage') {
            return ($amount * $rule['discount_value']) / 100;
        }
        return min($rule['discount_value'], $amount);
    }
    
    private function applyDiscount($rule, $amount) {
        return $amount - $this->calculateDiscount($rule, $amount);
    }
    
    public function logDiscountApplication($transaction_id, $discount_data) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO applied_discounts 
                             (transaction_id, rule_id, discount_type, discount_value, original_amount, final_amount)
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $transaction_id,
            $discount_data['rule_id'],
            $discount_data['discount_type'],
            $discount_data['discount_value'],
            $discount_data['original_amount'],
            $discount_data['discounted_amount']
        ]);
    }
    
    public function getDiscountedPlans($userId) {
        // Dummy implementation: return empty array or some sample discounts
        return [];
    }
}