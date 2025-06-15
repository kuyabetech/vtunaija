<?php
class FraudDetection {
    private $maxDailyTransactions = 20;
    private $maxDailyAmount = 100000; // ₦100,000
    private $minTimeBetweenTransactions = 30; // seconds
    
    public function checkTransaction($user_id, $amount, $service_type) {
        $db = DB::getInstance()->getConnection();
        
        // Check 1: Daily transaction count
        $stmt = $db->prepare("SELECT COUNT(*) as count 
                             FROM vtu_transactions 
                             WHERE user_id = ? 
                             AND DATE(created_at) = CURDATE()");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] >= $this->maxDailyTransactions) {
            throw new Exception("Daily transaction limit exceeded");
        }
        
        // Check 2: Daily transaction amount
        $stmt = $db->prepare("SELECT SUM(amount) as total 
                             FROM vtu_transactions 
                             WHERE user_id = ? 
                             AND DATE(created_at) = CURDATE()");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (($result['total'] + $amount) > $this->maxDailyAmount) {
            throw new Exception("Daily transaction amount limit exceeded");
        }
        
        // Check 3: Time between transactions
        $stmt = $db->prepare("SELECT created_at 
                             FROM vtu_transactions 
                             WHERE user_id = ? 
                             ORDER BY created_at DESC 
                             LIMIT 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && (time() - strtotime($result['created_at'])) < $this->minTimeBetweenTransactions) {
            throw new Exception("Please wait before making another transaction");
        }
        
        // Check 4: Unusual amount patterns
        if ($service_type == 'airtime' && $amount > 20000) {
            throw new Exception("Airtime amount seems unusually high");
        }
        
        // Check 5: Device fingerprinting (basic implementation)
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $db->prepare("SELECT COUNT(*) as count 
                             FROM user_devices 
                             WHERE user_id = ? 
                             AND user_agent = ? 
                             AND ip_address = ?");
        $stmt->execute([$user_id, $user_agent, $ip]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // New device detected - might want additional verification
            $this->flagForReview($user_id, "New device detected", [
                'user_agent' => $user_agent,
                'ip_address' => $ip
            ]);
        }
        
        return true;
    }
    
    private function flagForReview($user_id, $reason, $metadata = []) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO fraud_flags 
                             (user_id, reason, metadata) 
                             VALUES (?, ?, ?)");
        $stmt->execute([
            $user_id,
            $reason,
            json_encode($metadata)
        ]);
        
        // Could also send notification to admin
    }
    
    public function isUserFlagged($user_id) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT COUNT(*) as count 
                             FROM fraud_flags 
                             WHERE user_id = ? 
                             AND resolved_at IS NULL");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
}