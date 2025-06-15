<?php
class AnalyticsEngine {
    public function getTransactionTrends($period = '7d') {
        $db = DB::getInstance()->getConnection();
        
        $dateCondition = $this->getDateCondition($period);
        
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count,
                    SUM(amount) as total_amount,
                    service_type
                  FROM vtu_transactions
                  WHERE status = 'successful'
                  AND $dateCondition
                  GROUP BY DATE(created_at), service_type
                  ORDER BY date";
        
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserAcquisitionStats($period = '30d') {
        $db = DB::getInstance()->getConnection();
        
        $dateCondition = $this->getDateCondition($period);
        
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_users,
                    SUM(CASE WHEN wallet_balance > 0 THEN 1 ELSE 0 END) as activated_users
                  FROM users
                  WHERE $dateCondition
                  GROUP BY DATE(created_at)
                  ORDER BY date";
        
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function predictNextWeekRevenue() {
        $db = DB::getInstance()->getConnection();
        
        // Get last 4 weeks data
        $stmt = $db->query("
            SELECT 
                WEEK(created_at, 3) as week_number,
                SUM(amount) as weekly_revenue
            FROM vtu_transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
            AND status = 'successful'
            GROUP BY WEEK(created_at, 3)
            ORDER BY week_number
        ");
        $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($weeklyData) < 2) {
            return null; // Not enough data
        }
        
        // Simple linear regression
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        $n = count($weeklyData);
        
        foreach ($weeklyData as $i => $week) {
            $x = $i + 1;
            $y = $week['weekly_revenue'];
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Predict next week (x = $n + 1)
        $prediction = $slope * ($n + 1) + $intercept;
        
        return max(0, $prediction); // Don't return negative predictions
    }
    
    public function getTopPerformingServices($limit = 5) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT 
                service_type,
                network,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount
            FROM vtu_transactions
            WHERE status = 'successful'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY service_type, network
            ORDER BY total_amount DESC
            LIMIT $limit
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getDateCondition($period) {
        $intervals = [
            '7d' => 'INTERVAL 7 DAY',
            '30d' => 'INTERVAL 30 DAY',
            '90d' => 'INTERVAL 90 DAY',
            '12m' => 'INTERVAL 12 MONTH'
        ];
        
        if (isset($intervals[$period])) {
            return "created_at >= DATE_SUB(NOW(), " . $intervals[$period] . ")";
        }
        
        // Default to 30 days
        return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}