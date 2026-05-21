<?php
session_start();
require_once 'config/database.php';
require_once 'functions/auth.php';

if (isset($_SESSION['user_id'])) {
    clearRememberMe($_SESSION['user_id']);
}

// Destroy session
session_unset();
session_destroy();

setcookie('remember_me', '', time() - 3600, '/');

header("Location: login.php");
exit();