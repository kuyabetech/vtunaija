<?php
class CryptoPayment {
    private $apiKey;
    private $apiUrl = 'https://api.blockcypher.com/v1/';
    
    public function __construct() {
        $this->apiKey = 'your_blockcypher_api_key';
    }
    
    public function createPaymentRequest($user_id, $amount_ngn, $currency = 'BTC') {
        $db = DB::getInstance()->getConnection();
        
        // Convert NGN to crypto amount (this would use current exchange rate)
        $exchange_rate = $this->getExchangeRate($currency);
        $amount_crypto = $amount_ngn / $exchange_rate;
        
        // Generate wallet address
        $wallet_address = $this->generateWalletAddress();
        
        // Save to database
        $stmt = $db->prepare("INSERT INTO crypto_payments 
                             (user_id, amount_ngn, amount_crypto, currency, wallet_address)
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $amount_ngn, $amount_crypto, $currency, $wallet_address]);
        
        return [
            'id' => $db->lastInsertId(),
            'amount_ngn' => $amount_ngn,
            'amount_crypto' => $amount_crypto,
            'currency' => $currency,
            'wallet_address' => $wallet_address,
            'qr_code_url' => $this->generateQRCode($wallet_address, $amount_crypto, $currency)
        ];
    }
    
    public function checkPaymentStatus($payment_id) {
        $db = DB::getInstance()->getConnection();
        
        // Get payment info
        $stmt = $db->prepare("SELECT * FROM crypto_payments WHERE id = ?");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment || $payment['status'] != 'pending') {
            return $payment;
        }
        
        // Check blockchain for payment
        $tx_status = $this->checkWalletAddress($payment['wallet_address'], $payment['amount_crypto']);
        
        if ($tx_status['confirmed']) {
            // Update payment status
            $stmt = $db->prepare("UPDATE crypto_payments 
                                 SET status = 'confirmed', tx_hash = ?, updated_at = NOW()
                                 WHERE id = ?");
            $stmt->execute([$tx_status['tx_hash'], $payment_id]);
            
            // Fund user's wallet
            $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$payment['amount_ngn'], $payment['user_id']]);
            
            // Record transaction
            addWalletTransaction(
                $payment['user_id'],
                $payment['amount_ngn'],
                'CRYPTO_' . $payment['id'],
                'successful',
                'crypto',
                strtoupper($payment['currency']),
                'Wallet funding via cryptocurrency'
            );
            
            $payment['status'] = 'confirmed';
        }
        
        return $payment;
    }
    
    private function getExchangeRate($currency) {
        // In production, you would call an exchange rate API
        $rates = [
            'BTC' => 25000000,  // 1 BTC = 25,000,000 NGN
            'ETH' => 1500000,    // 1 ETH = 1,500,000 NGN
            'USDT' => 1500       // 1 USDT = 1,500 NGN
        ];
        
        return $rates[$currency] ?? 0;
    }
    
    private function generateWalletAddress() {
        // In production, you would generate a unique wallet address
        // or use a payment processor like Blockcypher, Coinbase Commerce, etc.
        return '3' . bin2hex(random_bytes(20));
    }
    
    private function generateQRCode($address, $amount, $currency) {
        $data = "$currency:$address?amount=$amount";
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);
    }
    
    private function checkWalletAddress($address, $expected_amount) {
        // In production, you would check the blockchain
        // This is a mock implementation
        if (rand(1, 10) > 7) { // 30% chance of being "confirmed"
            return [
                'confirmed' => true,
                'tx_hash' => 'mock_tx_' . bin2hex(random_bytes(16))
            ];
        }
        
        return ['confirmed' => false];
    }
}