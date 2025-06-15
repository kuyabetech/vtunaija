<?php
class TransactionRouter {
    private $routingRules = [
        'airtime' => [
            'default' => 'vendor_a',
            'MTN' => ['vendor_a', 'vendor_b'],
            'GLO' => ['vendor_b', 'vendor_c'],
            'AIRTEL' => ['vendor_a', 'vendor_c'],
            '9MOBILE' => ['vendor_c', 'vendor_b']
        ],
        'data' => [
            'default' => 'vendor_b',
            'MTN' => ['vendor_b', 'vendor_a'],
            'GLO' => ['vendor_c', 'vendor_b'],
            'AIRTEL' => ['vendor_a', 'vendor_c'],
            '9MOBILE' => ['vendor_c', 'vendor_a']
        ],
        'electricity' => [
            'default' => 'vendor_c'
        ],
        'cable' => [
            'default' => 'vendor_a'
        ]
    ];
    
    private $performanceMetrics = [];
    
    public function __construct() {
        $this->loadPerformanceMetrics();
    }
    
    private function loadPerformanceMetrics() {
        $db = DB::getInstance()->getConnection();
        
        // Get success rates for each vendor/service combination
        $stmt = $db->query("
            SELECT provider, service_type, network,
                   COUNT(*) as total,
                   SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successes
            FROM vtu_transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
            GROUP BY provider, service_type, network
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $successRate = $row['total'] > 0 ? ($row['successes'] / $row['total']) * 100 : 0;
            
            $this->performanceMetrics[$row['service_type']][$row['network']][$row['provider']] = [
                'success_rate' => $successRate,
                'total_transactions' => $row['total']
            ];
        }
    }
    
    public function getOptimalProvider($service_type, $network = null) {
        $network = $network ?: 'default';
        
        // Get configured providers for this service/network
        $configuredProviders = $this->routingRules[$service_type][$network] ?? 
                              $this->routingRules[$service_type]['default'] ?? 
                              ['vendor_a'];
        
        // If it's a string (single provider), convert to array
        if (is_string($configuredProviders)) {
            $configuredProviders = [$configuredProviders];
        }
        
        // If we have performance data, sort by success rate
        if (isset($this->performanceMetrics[$service_type][$network])) {
            $metrics = $this->performanceMetrics[$service_type][$network];
            
            usort($configuredProviders, function($a, $b) use ($metrics) {
                $rateA = $metrics[$a]['success_rate'] ?? 0;
                $rateB = $metrics[$b]['success_rate'] ?? 0;
                
                return $rateB <=> $rateA; // Sort descending
            });
        }
        
        // Return the best available provider
        return $configuredProviders[0] ?? 'vendor_a';
    }
    
    public function recordTransactionOutcome($provider, $service_type, $network, $success) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO provider_performance 
            (provider, service_type, network, success, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$provider, $service_type, $network, $success ? 1 : 0]);
        
        // Update local metrics cache
        $key = $service_type . '.' . $network . '.' . $provider;
        
        if (!isset($this->performanceMetrics[$service_type][$network][$provider])) {
            $this->performanceMetrics[$service_type][$network][$provider] = [
                'success_rate' => $success ? 100 : 0,
                'total_transactions' => 1
            ];
        } else {
            $current = $this->performanceMetrics[$service_type][$network][$provider];
            $newTotal = $current['total_transactions'] + 1;
            $newSuccesses = $current['success_rate'] * $current['total_transactions'] / 100;
            $newSuccesses += $success ? 1 : 0;
            
            $this->performanceMetrics[$service_type][$network][$provider] = [
                'success_rate' => ($newSuccesses / $newTotal) * 100,
                'total_transactions' => $newTotal
            ];
        }
    }
    
    public function getRoutingMatrix() {
        $matrix = [];
        
        foreach ($this->routingRules as $service => $networks) {
            foreach ($networks as $network => $providers) {
                $matrix[$service][$network] = [
                    'configured_providers' => is_array($providers) ? $providers : [$providers],
                    'performance_metrics' => $this->performanceMetrics[$service][$network] ?? []
                ];
            }
        }
        
        return $matrix;
    }
}