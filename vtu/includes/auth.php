<?php
require_once 'config.php';
require_once 'functions.php';

function enableTwoFactor($user_id) {
    $db = DB::getInstance()->getConnection();
    
    // Generate secret
    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $ga->createSecret();
    
    $stmt = $db->prepare("UPDATE users SET two_factor_enabled = TRUE, two_factor_secret = ? WHERE id = ?");
    return $stmt->execute([$secret, $user_id]);
}

function verifyTwoFactorCode($user_id, $code) {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['two_factor_secret']) {
        return false;
    }
    
    $ga = new PHPGangsta_GoogleAuthenticator();
    return $ga->verifyCode($user['two_factor_secret'], $code, 2); // 2 = 2*30sec clock tolerance
}

function isTwoFactorEnabled($user_id) {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && $user['two_factor_enabled'];
}


?>