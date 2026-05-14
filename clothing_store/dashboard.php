<?php
require 'functions/auth.php';
checkLogin();

if($_SESSION['role']=="superadmin"){
    header("Location: superadmin/dashboard.php");
}
elseif($_SESSION['role']=="admin"){
    header("Location: admin/dashboard.php");
}
elseif($_SESSION['role']=="regular"){
    header("Location: user/dashboard.php");
}
exit();
?>