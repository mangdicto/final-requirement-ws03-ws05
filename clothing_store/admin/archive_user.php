<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
checkLogin();
checkRole('admin');

// CSRF protection for GET requests
if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    die("CSRF validation failed. Please refresh the page and try again.");
}

if(!isset($_GET['id'])) die("No ID");

$id = intval(base64_decode($_GET['id']));
$admin_id = $_SESSION['user_id'];

// Get user info for logging
$user_info = $conn->query("SELECT first_name, last_name FROM users WHERE id=$id");
$user = $user_info->fetch_assoc();
$user_name = ($user) ? $user['first_name'] . " " . $user['last_name'] : "User ID: $id";

$stmt = $conn->prepare("UPDATE users SET status='archived' WHERE id=? AND role='regular'");
$stmt->bind_param("i",$id);

if($stmt->execute()){
    // Log the activity
    $action_type = "Archive User";
    $description = "Admin archived user: $user_name";
    
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("iiss", $admin_id, $id, $action_type, $description);
    $log_stmt->execute();
    
    header("Location: manage_user.php?msg=User+Archived+Successfully");
} else {
    // Error page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Archive Failed</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <style>
            body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; font-family: 'Poppins', sans-serif; margin: 0; }
            .error-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #ef4444; max-width: 400px; }
            .icon { font-size: 50px; color: #ef4444; margin-bottom: 15px; }
            h2 { color: #1e293b; margin: 0 0 10px; }
            p { color: #64748b; margin-bottom: 25px; font-size: 0.95rem; line-height: 1.5; }
            .btn { background: #f59e0b; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; transition: 0.3s; }
            .btn:hover { background: #1e40af; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="icon">⚠️</div>
            <h2>Archive Failed</h2>
            <p>Unable to archive this user. Please try again.</p>
            <a href="manage_user.php" class="btn">Back to Manage Users</a>
        </div>
    </body>
    </html>
    <?php
}
exit();
?>