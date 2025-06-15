<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function getUserById($id) {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByEmail($email) {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addWalletTransaction($user_id, $amount, $reference, $status, $method, $provider, $description = '') {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO wallet_transactions (user_id, amount, reference, status, payment_method, payment_provider, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $amount, $reference, $status, $method, $provider, $description]);
}

function generateReferralCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function processReferral($referred_id) {
    $db = DB::getInstance()->getConnection();
    
    // Check if referral exists in URL
    if (isset($_COOKIE['ref'])) {
        $referral_code = sanitizeInput($_COOKIE['ref']);
        
        // Get referrer
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$referral_code]);
        $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($referrer && $referrer['id'] != $referred_id) {
            // Create referral record
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
            $stmt->execute([$referrer['id'], $referred_id]);
            
            return true;
        }
    }
    
    return false;
}

function completeReferral($referred_id) {
    $db = DB::getInstance()->getConnection();
    $settings = getSettings();
    
    // Find pending referrals for this user
    $stmt = $db->prepare("SELECT * FROM referrals WHERE referred_id = ? AND status = 'pending'");
    $stmt->execute([$referred_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($referrals as $referral) {
        // Give bonus to referrer
        $bonus = $settings['referral_bonus'];
        
        $stmt = $db->prepare("UPDATE users SET referral_balance = referral_balance + ? WHERE id = ?");
        $stmt->execute([$bonus, $referral['referrer_id']]);
        
        // Update referral status
        $stmt = $db->prepare("UPDATE referrals SET status = 'completed', bonus_amount = ?, completed_at = NOW() WHERE id = ?");
        $stmt->execute([$bonus, $referral['id']]);
        
        // Notify referrer
        $user = getUserById($referral['referrer_id']);
        $message = "You earned ₦{$bonus} referral bonus from {$user['name']}";
        sendNotification($referral['referrer_id'], 'referral_bonus', $message);
    }
}

// Fetch settings from the database or return default settings
function getSettings() {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT `key`, `value` FROM settings");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
    // Provide a default value if referral_bonus is not set
    if (!isset($settings['referral_bonus'])) {
        $settings['referral_bonus'] = 100; // Default bonus amount
    }
    return $settings;
}

// Simple notification function stub
function sendNotification($user_id, $type, $message) {
    // Implement notification logic here (e.g., save to DB, send email, etc.)
    // For now, just return true as a placeholder
    return true;
}

// CSRF token generator
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token verifier
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getUserAvatar($user) {
    if (!empty($user['avatar'])) {
        return $user['avatar'];
    }
    // Return a default avatar image path
    return 'assets/images/default-avatar.jpg';
}

// Remove duplicate isTwoFactorEnabled function if it exists elsewhere
// More utility functions...
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmailNotification($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kuyabetech@gmail.com'; // your Gmail
        $mail->Password = 'dlix xtim dhtx biau'; // App password (good job using this!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // More explicit
        $mail->Port = 587;

        // From and To
        $mail->setFrom('kuyabetech@gmail.com', 'VTUNaija'); // match this with SMTP account
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Send
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // logs detailed error
        return false;
    }
}


function requireTwoFactor($user) {
    // Check if user has 2FA enabled and is logging in
    if (!empty($user['two_factor_enabled']) && $user['two_factor_enabled']) {
        $_SESSION['2fa_required'] = true;
        $_SESSION['2fa_user_id'] = $user['id'];
        redirect('verify-2fa.php');
        exit;
    }
}

/**
 * Get environment variable from .env or $_ENV
 */
function env($key, $default = null) {
    if (isset($_ENV[$key])) return $_ENV[$key];
    if (getenv($key) !== false) return getenv($key);
    // Simple .env loader (loads once)
    static $envLoaded = false;
    if (!$envLoaded && file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($k, $v) = array_map('trim', explode('=', $line, 2));
            $_ENV[$k] = $v;
        }
        $envLoaded = true;
        if (isset($_ENV[$key])) return $_ENV[$key];
    }
    return $default;
}

/**
 * Send bulk email notifications to multiple recipients.
 * Returns the number of successfully sent emails.
 */
/**
 * Send bulk email notifications with enhanced error handling and performance
 * 
 * @param array $recipients List of email addresses
 * @param string $subject Email subject
 * @param string $body HTML email content
 * @param array $attachments Optional array of file paths to attach
 * @param int $batchSize Number of emails to send per SMTP connection
 * @return array ['sent' => int, 'failed' => array]
 */
function sendEmailNotifications(
    array $recipients, 
    string $subject, 
    string $body, 
    array $attachments = [], 
    int $batchSize = 50
): array {
    // Load SMTP configuration from environment with validation
    $smtpConfig = [
        'host' => trim(env('SMTP_HOST', 'smtp.gmail.com'), " '\""),
        'username' => trim(env('SMTP_USERNAME'), " '\""),
        'password' => trim(env('SMTP_PASSWORD'), " '\""),
        'port' => (int)env('SMTP_PORT', 587),
        'from' => filter_var(trim(env('SMTP_FROM', env('SMTP_USERNAME')), " '\""), FILTER_VALIDATE_EMAIL) ?: trim(env('SMTP_USERNAME'), " '\""),
        'from_name' => trim(env('SMTP_FROM_NAME', 'VTU Platform'), " '\""),
        'encryption' => trim(env('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS), " '\""),
    ];

    // Validate critical SMTP configuration
    if (empty($smtpConfig['username']) || empty($smtpConfig['password'])) {
        $results = [
            'sent' => 0,
            'failed' => $recipients,
            'errors' => ['SMTP is not configured. Please set SMTP_USERNAME and SMTP_PASSWORD in your .env file.']
        ];
        return $results;
    }

    $results = [
        'sent' => 0,
        'failed' => [],
        'errors' => []
    ];

    $mail = null;
    $currentBatch = 0;

    foreach ($recipients as $index => $email) {
        try {
            // Create new PHPMailer instance for each batch
            if ($currentBatch % $batchSize === 0) {
                if ($mail !== null && $mail->getSMTPInstance()->connected()) {
                    $mail->smtpClose();
                }

                $mail = new PHPMailer(true);
                configureMailer($mail, $smtpConfig);
            }

            // Validate email address
            $cleanEmail = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
            if (!$cleanEmail) {
                throw new InvalidArgumentException("Invalid email address: $email");
            }

            // Prepare email
            $mail->clearAddresses();
            $mail->addAddress($cleanEmail);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            // Add attachments if specified
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }

            // Send email
            $mail->send();
            $results['sent']++;
            $currentBatch++;

        } catch (Exception $e) {
            $errorMsg = "Failed to send to $email: " . $e->getMessage();
            $results['failed'][] = $email;
            $results['errors'][$email] = $errorMsg;
            
            // Log detailed error
            error_log($errorMsg);
            
            // Reset mailer for next attempt
            if ($mail !== null && $mail->getSMTPInstance()->connected()) {
                $mail->smtpClose();
            }
            $currentBatch = 0;
        }
    }

    // Clean up
    if ($mail !== null && $mail->getSMTPInstance()->connected()) {
        $mail->smtpClose();
    }

    return $results;
}

/**
 * Configure PHPMailer instance with SMTP settings
 */
function configureMailer(PHPMailer $mail, array $config): void
{
    // Server settings
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    // From address
    $mail->setFrom($config['from'], $config['from_name']);

    // Use TLS if encryption is set, else no encryption
    if (!empty($config['encryption'])) {
        $mail->SMTPSecure = $config['encryption'];
    } else {
        $mail->SMTPSecure = false;
    }

    // Security settings
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    // Debug output (only in development)
    if (env('APP_ENV') === 'development') {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP Debug: $str");
        };
    }

    // Timeout settings
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;
}