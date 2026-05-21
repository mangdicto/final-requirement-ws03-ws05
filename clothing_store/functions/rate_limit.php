<?php
// functions/rate_limit.php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function checkRateLimit($email, $ip) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                            WHERE email = ? AND ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['attempts'] >= 5) {
        return false;
    }
    return true;
}

function recordFailedAttempt($email, $ip) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
}

function clearLoginAttempts($email, $ip) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ? AND ip_address = ?");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
}

function getRemainingAttempts($email, $ip) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                            WHERE email = ? AND ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $remaining = 5 - $row['attempts'];
    return $remaining > 0 ? $remaining : 0;
}
?>