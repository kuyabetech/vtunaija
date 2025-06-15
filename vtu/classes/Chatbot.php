<?php
class Chatbot {
    private $nlpClient;
    
    public function __construct() {
        // Initialize NLP client (could be Dialogflow, Rasa, or custom solution)
        $this->nlpClient = new NlpClient();
    }
    
    public function startSession($user_id = null) {
        $db = DB::getInstance()->getConnection();
        
        $session_token = bin2hex(random_bytes(32));
        
        $stmt = $db->prepare("INSERT INTO chat_sessions 
                             (user_id, session_token) 
                             VALUES (?, ?)");
        $stmt->execute([$user_id, $session_token]);
        
        return [
            'session_id' => $db->lastInsertId(),
            'session_token' => $session_token
        ];
    }
    
    public function processMessage($session_id, $message) {
        $db = DB::getInstance()->getConnection();
        
        // Save user message
        $this->saveMessage($session_id, 'user', $message);
        
        // Get session context
        $stmt = $db->prepare("SELECT * FROM chat_sessions WHERE id = ?");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Detect intent using NLP
        $intent = $this->nlpClient->detectIntent($message);
        
        // Get response
        if ($intent['requires_human']) {
            $this->transferToHuman($session_id);
            $response = "I'm transferring you to a support agent. Please hold...";
        } else {
            $response = $this->generateResponse($intent, $session);
        }
        
        // Save bot response
        $this->saveMessage($session_id, 'bot', $response);
        
        return $response;
    }
    
    public function chat($session_id, $message) {
        // Process a chat message and return the bot's response
        return $this->processMessage($session_id, $message);
    }
    
    private function saveMessage($session_id, $sender, $message) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO chat_messages 
                             (session_id, sender, message) 
                             VALUES (?, ?, ?)");
        $stmt->execute([$session_id, $sender, $message]);
    }
    
    private function transferToHuman($session_id) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE chat_sessions 
                             SET status = 'transferred' 
                             WHERE id = ?");
        $stmt->execute([$session_id]);
        
        // Notify support agents
        $this->notifyAgents($session_id);
    }
    
    private function generateResponse($intent, $session) {
        $db = DB::getInstance()->getConnection();
        
        // Replace placeholders with actual data
        $response = $intent['response_template'];
        
        if (strpos($response, '{user_name}') !== false && $session['user_id']) {
            $user = getUserById($session['user_id']);
            $response = str_replace('{user_name}', $user['name'], $response);
        }
        
        if (strpos($response, '{wallet_balance}') !== false && $session['user_id']) {
            $user = getUserById($session['user_id']);
            $response = str_replace('{wallet_balance}', number_format($user['wallet_balance'], 2), $response);
        }
        
        // Add dynamic data for common queries
        if ($intent['intent_name'] == 'check_transaction_status') {
            $stmt = $db->prepare("SELECT * FROM vtu_transactions 
                                 WHERE user_id = ? 
                                 ORDER BY created_at DESC 
                                 LIMIT 1");
            $stmt->execute([$session['user_id']]);
            $txn = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($txn) {
                $response .= "\n\nYour last transaction:";
                $response .= "\nType: " . ucfirst($txn['service_type']);
                $response .= "\nAmount: ₦" . number_format($txn['amount'], 2);
                $response .= "\nStatus: " . ucfirst($txn['status']);
                $response .= "\nDate: " . date('M j, Y H:i', strtotime($txn['created_at']));
            }
        }
        
        return $response;
    }
    
    public function getChatHistory($session_id) {
        $db = DB::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM chat_messages 
                             WHERE session_id = ? 
                             ORDER BY created_at ASC");
        $stmt->execute([$session_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}