<?php
class KycVerification {
    private $verificationApiKey;
    private $verificationApiUrl = 'https://api.kycprovider.com/v1';
    
    public function __construct() {
        $this->verificationApiKey = 'YOUR_KYC_API_KEY';
    }
    
    public function submitVerification($user_id, $data, $files) {
        $db = DB::getInstance()->getConnection();
        
        // Validate input
        $this->validateSubmission($data, $files);
        
        // Upload documents
        $frontImage = $this->uploadDocument($files['front_image']);
        $backImage = isset($files['back_image']) ? $this->uploadDocument($files['back_image']) : null;
        $selfieImage = $this->uploadDocument($files['selfie_image']);
        
        // Submit to KYC provider
        $verificationResult = $this->submitToKycProvider([
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'front_image_url' => $frontImage,
            'back_image_url' => $backImage,
            'selfie_image_url' => $selfieImage,
            'user_data' => $this->getUserData($user_id)
        ]);
        
        // Save verification record
        $stmt = $db->prepare("INSERT INTO kyc_verifications 
                             (user_id, document_type, document_number, 
                              front_image, back_image, selfie_image, status)
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $data['document_type'],
            $data['document_number'],
            $frontImage,
            $backImage,
            $selfieImage,
            $verificationResult['auto_approved'] ? 'approved' : 'pending'
        ]);
        
        $verificationId = $db->lastInsertId();
        
        // If auto-approved, update user
        if ($verificationResult['auto_approved']) {
            $this->approveVerification($verificationId, 0); // 0 = system approval
        }
        
        return $verificationId;
    }
    
    private function validateSubmission($data, $files) {
        $allowedTypes = ['nin', 'driver_license', 'voter_id', 'passport'];
        
        if (!in_array($data['document_type'], $allowedTypes)) {
            throw new Exception("Invalid document type");
        }
        
        if (empty($data['document_number'])) {
            throw new Exception("Document number is required");
        }
        
        if (empty($files['front_image']['tmp_name'])) {
            throw new Exception("Front image of document is required");
        }
        
        if (empty($files['selfie_image']['tmp_name'])) {
            throw new Exception("Selfie with document is required");
        }
        
        // Additional validation could check file types, sizes, etc.
    }
    
    private function uploadDocument($file) {
        $uploadDir = __DIR__ . '/../uploads/kyc/';
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload document");
        }
        
        return '/uploads/kyc/' . $filename;
    }
    
    private function submitToKycProvider($data) {
        $payload = [
            'api_key' => $this->verificationApiKey,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'front_image_url' => $data['front_image_url'],
            'back_image_url' => $data['back_image_url'],
            'selfie_image_url' => $data['selfie_image_url'],
            'user_data' => $data['user_data']
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->verificationApiUrl . '/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("KYC API returned HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['status'])) {
            throw new Exception("Invalid KYC API response");
        }
        
        return $result;
    }
    
    private function getUserData($user_id) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone']
        ];
    }
    
    public function approveVerification($verification_id, $admin_id) {
        $db = DB::getInstance()->getConnection();
        
        // Get verification details
        $stmt = $db->prepare("SELECT * FROM kyc_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$verification) {
            throw new Exception("Verification not found");
        }
        
        // Update verification record
        $stmt = $db->prepare("UPDATE kyc_verifications 
                             SET status = 'approved', 
                                 reviewed_by = ?, 
                                 reviewed_at = NOW() 
                             WHERE id = ?");
        $stmt->execute([$admin_id, $verification_id]);
        
        // Update user record
        $stmt = $db->prepare("UPDATE users \
                             SET kyc_verified = TRUE, \
                                 kyc_verified_at = NOW(),\
                                 transaction_limit = 500000.00\
                             WHERE id = ?");
        $stmt->execute([$verification['user_id']]);
        // Notify user
        $this->sendApprovalNotification($verification['user_id']);
        return true;
    }
    
    public function rejectVerification($verification_id, $admin_id, $reason) {
        $db = DB::getInstance()->getConnection();
        
        // Update verification record
        $stmt = $db->prepare("UPDATE kyc_verifications 
                             SET status = 'rejected', 
                                 reviewed_by = ?, 
                                 reviewed_at = NOW(),
                                 rejection_reason = ?
                             WHERE id = ?");
        $stmt->execute([$admin_id, $reason, $verification_id]);
        
        // Get user ID
        $stmt = $db->prepare("SELECT user_id FROM kyc_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Notify user
        $this->sendRejectionNotification($verification['user_id'], $reason);
        
        return true;
    }
    
    private function sendApprovalNotification($user_id) {
        $user = getUserById($user_id);
        $subject = "KYC Verification Approved";
        $message = "Dear {$user['name']},\n\nYour KYC verification has been approved. " .
                   "Your transaction limit has been increased to ₦500,000.\n\n" .
                   "Thank you for using our service.";
        
        sendEmailNotification($user['email'], $subject, $message);
    }
    
    private function sendRejectionNotification($user_id, $reason) {
        $user = getUserById($user_id);
        $subject = "KYC Verification Update";
        $message = "Dear {$user['name']},\n\nYour KYC verification was not approved. " .
                   "Reason: {$reason}\n\n" .
                   "Please correct the issues and submit again.";
        
        sendEmailNotification($user['email'], $subject, $message);
    }
    
    public function checkUserLimit($user_id, $amount) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT kyc_verified, transaction_limit FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user['kyc_verified'] && $amount > $user['transaction_limit']) {
            throw new Exception("Transaction limit exceeded. Please complete KYC verification.");
        }
        
        return true;
    }
}