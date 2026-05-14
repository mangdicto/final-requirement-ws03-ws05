<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../config/database.php';  
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

function checkLogin(){
    if(!isset($_SESSION['user_id'])){
        header("Location: /clothing_store/login.php");
        exit();
    }
}

checkRememberMe();

function checkRole($role){
    if($_SESSION['role'] != $role){
        die("Access Denied");
    }
}
?>