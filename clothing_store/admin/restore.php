<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

$id = intval(base64_decode($_GET['id']));

$stmt = $conn->prepare("UPDATE users SET status='active' WHERE id=? AND role='regular'");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: manage_user.php");
exit();
?>