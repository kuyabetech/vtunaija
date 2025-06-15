<?php
class DisputeManager {
    public function createDispute($transaction_id, $user_id, $reason) {
        $db = DB::getInstance()->getConnection();
        
        // Verify transaction belongs to user
        $stmt = $db->prepare("SELECT id FROM vtu_transactions 
                             WHERE id = ? AND user_id = ?");
        $stmt->execute([$transaction_id, $user_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Transaction not found or doesn't belong to user");
        }
        
        // Create dispute
        $stmt = $db->prepare("INSERT INTO disputes 
                             (transaction_id, user_id, reason) 
                             VALUES (?, ?, ?)");
        $stmt->execute([$transaction_id, $user_id, $reason]);
        
        // Notify admin
        $this->notifyAdmins($db->lastInsertId());
        
        return $db->lastInsertId();
    }
    
    public function addComment($dispute_id, $user_id, $comment, $is_internal = false) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO dispute_comments 
                             (dispute_id, user_id, comment, is_internal) 
                             VALUES (?, ?, ?, ?)");
        $stmt->execute([$dispute_id, $user_id, $comment, $is_internal]);
        
        // Notify other party
        $this->notifyOtherParty($dispute_id, $user_id);
        
        return $db->lastInsertId();
    }
    
    public function resolveDispute($dispute_id, $admin_id, $resolution, $status) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE disputes 
                             SET status = ?, resolution = ?, 
                                 resolved_by = ?, resolved_at = NOW() 
                             WHERE id = ?");
        $stmt->execute([$status, $resolution, $admin_id, $dispute_id]);
        
        // Process resolution if approved
        if ($status == 'resolved') {
            $this->processResolution($dispute_id);
        }
        
        // Notify user
        $this->notifyUser($dispute_id, $status);
        
        return true;
    }
    
    private function processResolution($dispute_id) {
        $db = DB::getInstance()->getConnection();
        
        // Get dispute details
        $stmt = $db->prepare("SELECT d.*, t.amount 
                             FROM disputes d
                             JOIN vtu_transactions t ON d.transaction_id = t.id
                             WHERE d.id = ?");
        $stmt->execute([$dispute_id]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Refund user
        $stmt = $db->prepare("UPDATE users 
                             SET wallet_balance = wallet_balance + ? 
                             WHERE id = ?");
        $stmt->execute([$dispute['amount'], $dispute['user_id']]);
        
        // Record transaction
        addWalletTransaction(
            $dispute['user_id'],
            $dispute['amount'],
            'DISPUTE_REFUND_' . $dispute['id'],
            'successful',
            'system',
            'System',
            'Refund for resolved dispute #' . $dispute['id']
        );
    }
    
    private function notifyAdmins($dispute_id) {
        // Implementation would send notifications to admin dashboard
        // and optionally via email
    }
    
    private function notifyOtherParty($dispute_id, $commenter_id) {
        // Implementation would notify the other party (user or admin)
        // about the new comment
    }
    
    private function notifyUser($dispute_id, $status) {
        // Implementation would notify the user about the resolution
    }
}