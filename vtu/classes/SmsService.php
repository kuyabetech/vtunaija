<?php
class SmsService {
    private $apiKey;
    private $senderId;
    private $apiUrl = 'https://v3.api.termii.com/api/sms/send';
    
    public function __construct() {
        $this->apiKey = 'TLfrLVmavhjOPUAwuGXzjlDIqOdMvqCZWdzwzhFStXDKPIrutyWDVeXYpPeEYS';
        $this->senderId = 'VTUAlert';
    }
    
    public function sendSms($phone, $message) {
        // Format phone number (ensure it starts with 234 for Nigeria)
        $phone = $this->formatPhoneNumber($phone);
        
        $data = [
            'to' => $phone,
            'from' => $this->senderId,
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic',
            'api_key' => $this->apiKey
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("SMS API returned HTTP $httpCode");
        }
        
        $result = json_decode($response, true);
        
        if ($result['code'] != 'ok') {
            throw new Exception("SMS failed: " . ($result['message'] ?? 'Unknown error'));
        }
        
        return true;
    }
    
    private function formatPhoneNumber($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to 234 format if it starts with 0
        if (strlen($phone) == 11 && substr($phone, 0, 1) == '0') {
            $phone = '234' . substr($phone, 1);
        }
        // If already starts with 234 and is 13 digits, leave as is
        // If starts with 234 and is longer than 13, trim to 13
        if (strlen($phone) > 13 && substr($phone, 0, 3) == '234') {
            $phone = substr($phone, 0, 13);
        }
        return $phone;
    }
}

// Note: The transaction notification code has been removed from here.
// It should be implemented in the appropriate service class (e.g., VtuService.php), not inside SmsService.php.
?>