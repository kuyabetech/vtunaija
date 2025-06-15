<?php
class VtuAggregator {
    private $providers = [
        'vendor_a' => [
            'name' => 'Vendor A',
            'api_url' => 'https://api.vendora.com/v1',
            'api_key' => 'YOUR_API_KEY_A',
            'enabled' => true,
            'priority' => 1
        ],
        'vendor_b' => [
            'name' => 'Vendor B',
            'api_url' => 'https://api.vendorb.net',
            'api_key' => 'YOUR_API_KEY_B',
            'enabled' => true,
            'priority' => 2
        ],
        'vendor_c' => [
            'name' => 'Vendor C',
            'api_url' => 'https://vtu-api-vendorc.com',
            'api_key' => 'YOUR_API_KEY_C',
            'enabled' => true,
            'priority' => 3
        ]
    ];
    
    public function purchaseAirtime($network, $phone, $amount) {
        // Sort providers by priority
        $providers = $this->sortProvidersByPriority();
        
        $lastError = null;
        
        foreach ($providers as $providerId => $provider) {
            try {
                $result = $this->attemptPurchase($providerId, [
                    'service' => 'airtime',
                    'network' => $network,
                    'phone' => $phone,
                    'amount' => $amount
                ]);
                
                return $result;
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                error_log("Purchase failed with {$providerId}: {$lastError}");
                
                // Mark provider as temporarily unavailable
                $this->disableProviderTemporarily($providerId);
            }
        }
        
        throw new Exception("All vendors failed: " . $lastError);
    }
    
    private function sortProvidersByPriority() {
        $providers = $this->providers;
        
        uasort($providers, function($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });
        
        return array_filter($providers, function($provider) {
            return $provider['enabled'];
        });
    }
    
    private function attemptPurchase($providerId, $data) {
        $provider = isset($this->providers[$providerId]) ? $this->providers[$providerId] : null;
        if (!$provider) {
            throw new Exception("Provider not found");
        }
        
        $payload = [
            'api_key' => $provider['api_key'],
            'service' => $data['service'],
            'network' => $data['network'],
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'reference' => 'VTU_' . time() . '_' . uniqid()
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $provider['api_url'] . '/purchase',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['status'])) {
            throw new Exception("Invalid API response");
        }
        
        if (!$result['status']) {
            throw new Exception($result['message'] ?? 'Purchase failed');
        }
        
        return [
            'status' => true,
            'provider' => $provider['name'],
            'reference' => $result['reference'] ?? $payload['reference'],
            'provider_reference' => $result['provider_reference'] ?? null,
            'raw_response' => $result
        ];
    }
    
    private function disableProviderTemporarily($providerId) {
        // In a real implementation, this would track failures
        // and disable providers that are consistently failing
        // For simplicity, we'll just log it here
        error_log("Temporarily disabling provider {$providerId} due to failure");
    }
    
    public function getProviderStatus() {
        $status = [];
        
        foreach ($this->providers as $id => $provider) {
            $status[$id] = [
                'name' => $provider['name'],
                'enabled' => $provider['enabled'],
                'priority' => $provider['priority'],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
        
        return $status;
    }
    
    public function getBalanceAcrossProviders() {
        $balances = [];
        
        foreach ($this->sortProvidersByPriority() as $providerId => $provider) {
            try {
                $balance = $this->checkProviderBalance($providerId);
                $balances[$providerId] = [
                    'name' => $provider['name'],
                    'balance' => $balance,
                    'status' => 'online'
                ];
            } catch (Exception $e) {
                $balances[$providerId] = [
                    'name' => $provider['name'],
                    'balance' => 0,
                    'status' => 'offline',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $balances;
    }
    
    private function checkProviderBalance($providerId) {
        $provider = isset($this->providers[$providerId]) ? $this->providers[$providerId] : null;
        if (!$provider) {
            throw new Exception("Provider not found");
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $provider['api_url'] . '/balance?api_key=' . $provider['api_key'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['balance'])) {
            throw new Exception("Invalid balance response");
        }
        
        return $result['balance'];
    }
}