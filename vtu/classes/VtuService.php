<?php
require_once __DIR__.'/../includes/db.php';

// Make sure Composer's autoloader is included for PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// To use PHPMailer, install it via Composer:
// composer require phpmailer/phpmailer
// Then include the Composer autoloader at the top of your entry script:
// require_once __DIR__ . '/../vendor/autoload.php';

class VtuService {
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->apiKey = VTU_API_KEY;
        $this->apiUrl = VTU_API_URL;
    }
    
    public function buyAirtime($network, $phone, $amount, $user_id) {
        $reference = 'VTU_' . time() . '_' . uniqid();
        
        try {
            // First deduct from user's wallet
            $db = DB::getInstance()->getConnection();
            
            // Check user balance
            $user = getUserById($user_id);
            if (!$user) {
                throw new Exception("User not found");
            }
            if (!isset($user['wallet_balance']) || $user['wallet_balance'] < $amount) {
                throw new Exception("Insufficient wallet balance");
            }
            
            // Deduct from wallet
            $newBalance = $user['wallet_balance'] - $amount;
            $stmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $user_id]);
            
            // Record transaction
            $stmt = $db->prepare("INSERT INTO vtu_transactions (user_id, service_type, network, phone, amount, reference, status) VALUES (?, 'airtime', ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $network, $phone, $amount, $reference]);
            
            // Call VTU API
            $response = $this->callVtuApi([
                'service' => 'airtime',
                'network' => $network,
                'phone' => $phone,
                'amount' => $amount,
                'reference' => $reference
            ]);
            
            // Update transaction status
            $status = $response['status'] == 'success' ? 'successful' : 'failed';
            $stmt = $db->prepare("UPDATE vtu_transactions SET status = ?, api_response = ? WHERE reference = ?");
            $stmt->execute([$status, json_encode($response), $reference]);

            if ($status == 'failed') {
                // Refund user if failed
                $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$amount, $user_id]);
                throw new Exception("VTU transaction failed: " . ($response['message'] ?? 'Unknown error'));
            }
            
            // Send transaction notifications
            $this->sendTransactionNotifications($user_id, 'airtime purchase', [
                'reference' => $reference,
                'amount' => $amount
            ]);
            
            return [
                'status' => true,
                'message' => 'Airtime purchase successful',
                'reference' => $reference
            ];
            
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function callVtuApi($data) {
        $data['api_key'] = $this->apiKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    // Similar methods for data, electricity, cable...
    
    public function buyData($network, $phone, $amount, $plan_id, $user_id) {
        $reference = 'DATA_' . time() . '_' . uniqid();
        
        try {
            // First deduct from user's wallet
            $db = DB::getInstance()->getConnection();
            
            // Check user balance
            $user = getUserById($user_id);
            if (!$user) {
                throw new Exception("User not found");
            }
            if ($user['wallet_balance'] < $amount) {
                throw new Exception("Insufficient wallet balance");
            }
            
            // Deduct from wallet
            $newBalance = $user['wallet_balance'] - $amount;
            $stmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $user_id]);
            
            // Record transaction
            $stmt = $db->prepare("INSERT INTO vtu_transactions (user_id, service_type, network, phone, amount, reference, status) VALUES (?, 'data', ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $network, $phone, $amount, $reference]);
            
            // Call VTU API
            $response = $this->callVtuApi([
                'service' => 'data',
                'network' => $network,
                'phone' => $phone,
                'amount' => $amount,
                'plan_id' => $plan_id,
                'reference' => $reference
            ]);
            
            // Update transaction status
            $status = $response['status'] == 'success' ? 'successful' : 'failed';
            $stmt = $db->prepare("UPDATE vtu_transactions SET status = ?, api_response = ? WHERE reference = ?");
            $stmt->execute([$status, json_encode($response), $reference]);
            
            if ($status == 'failed') {
                // Refund user if failed
                $newBalance = $user['wallet_balance'] + $amount;
                $stmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
                $stmt->execute([$newBalance, $user_id]);
                
                throw new Exception("Data purchase failed: " . ($response['message'] ?? 'Unknown error'));
            }
            
            return [
                'status' => true,
                'message' => 'Data purchase successful',
                'reference' => $reference
            ];
            
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    function sendEmailNotification($to, $subject, $body) {
        require_once 'path/to/PHPMailer/src/PHPMailer.php';
        require_once 'path/to/PHPMailer/src/SMTP.php';
        require_once 'path/to/PHPMailer/src/Exception.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kuyabetech@gmial.com';
            $mail->Password = 'erbq ohqy bngu tyyw';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('no-reply@yvtunaija.com', 'VTU Service');
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    // Example usage after successful transaction:
    // sendEmailNotification(
    //     $user['email'],
    //     'Your Airtime Purchase',
    //     "You successfully purchased ₦{$amount} airtime for {$phone}"
    // );
    
    public function checkTransactionStatus($reference) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . '/status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'reference' => $reference,
                'api_key' => $this->apiKey
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['status'])) {
            throw new Exception("Invalid response from VTU provider");
        }
        
        return $result;
    }
    
    private function sendTransactionNotifications($user_id, $service, $details) {
        $db = DB::getInstance()->getConnection();
        $user = getUserById($user_id);

        // Email notification
        $subject = "Your {$service} transaction was successful";
        $body = "Hello {$user['name']},<br><br>"
              . "Your {$service} transaction with reference {$details['reference']} "
              . "for ₦{$details['amount']} was successful.<br><br>"
              . "Thank you for using our service.";

        sendEmailNotification($user['email'], $subject, $body);

        // SMS notification
        try {
            $sms = new SmsService();
            $message = "Your {$service} of ₦{$details['amount']} was successful. Ref: {$details['reference']}";
            $sms->sendSms($user['phone'], $message);
        } catch (Exception $e) {
            error_log("SMS notification failed: " . $e->getMessage());
        }
    }
    
    // Fraud detection logic should be placed inside the buyAirtime method or another appropriate method.
    // Example (to be placed at the start of buyAirtime):
    // $fraudCheck = new FraudDetection();
    // try {
    //     $fraudCheck->checkTransaction($user_id, $amount, 'airtime');
    // } catch (Exception $e) {
    //     return [
    //         'status' => false,
    //         'message' => $e->getMessage(),
    //         'fraud_check_failed' => true
    //     ];
    // }
}

?>