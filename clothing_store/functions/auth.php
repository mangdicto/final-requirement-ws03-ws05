<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// -------------------------------
// REMEMBER ME FUNCTIONS
// -------------------------------

function checkRememberMe() {
    if (isset($_SESSION['user_id'])) {
        return;
    }

    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $parts = explode(':', $token);
        if (count($parts) === 2) {
            list($selector, $validator) = $parts;

            global $conn; 
            $sql = "SELECT id, role, remember_validator, remember_expiry FROM users 
                    WHERE remember_selector = ? AND remember_expiry > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($validator, $row['remember_validator'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['role'] = $row['role'];

                    // Rotate validator for security
                    $newValidator = bin2hex(random_bytes(32));
                    $newHashed = password_hash($newValidator, PASSWORD_DEFAULT);
                    $newExpiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                    $updateSql = "UPDATE users SET remember_validator = ?, remember_expiry = ? WHERE id = ?";
                    $updStmt = $conn->prepare($updateSql);
                    $updStmt->bind_param("ssi", $newHashed, $newExpiry, $row['id']);
                    $updStmt->execute();

                    $newToken = $selector . ':' . $newValidator;
                    setcookie('remember_me', $newToken, time() + 86400*30, '/', '', false, true);
                } else {
                    clearRememberMe($row['id']);
                }
            } else {
                setcookie('remember_me', '', time() - 3600, '/');
            }
        }
    }
}

function clearRememberMe($user_id = null) {
    setcookie('remember_me', '', time() - 3600, '/');
    if ($user_id) {
        global $conn;
        $sql = "UPDATE users SET remember_selector = NULL, remember_validator = NULL, remember_expiry = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}

function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        // Session expired - logout
        if (isset($_SESSION['user_id'])) {
            clearRememberMe($_SESSION['user_id']);
        }
        session_unset();
        session_destroy();
        header("Location: /inventory_system/login.php?msg=Session expired. Please login again.");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// -------------------------------
// LOGIN CHECK & CACHE CONTROL
// -------------------------------

function checkLogin(){
    // Cache control headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    // Check session timeout
    checkSessionTimeout();

    if(!isset($_SESSION['user_id'])){
        header("Location: /inventory_system/login.php");
        exit();
    }
}

// -------------------------------
// BACK BUTTON FIX (JavaScript)
// -------------------------------
function preventBackButtonCache() {
    echo '<script>';
    echo 'window.addEventListener("pageshow", function(event) {';
    echo '    if (event.persisted) {';
    echo '        window.location.reload();';
    echo '    }';
    echo '});';
    echo '</script>';
}

// -------------------------------
// ROLE CHECK
// -------------------------------
function checkRole($role){
    if($_SESSION['role'] != $role){
        die("Access Denied");
    }
}

// Auto-run remember me check
checkRememberMe();
// Back button fix para sa lahat ng pages (including login, pero harmless)
preventBackButtonCache();
?>