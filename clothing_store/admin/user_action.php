<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin(); 
checkRole('admin');

if(isset($_GET['id']) && isset($_GET['action'])){
    $id = intval(base64_decode($_GET['id']));
    $action = $_GET['action'];
    $admin_id = $_SESSION['user_id']; // Kinuha ang admin ID mula sa session

    if($action === 'archive'){
        $stmt = $conn->prepare("UPDATE users SET status='archived' WHERE id=?");
        $stmt->bind_param("i", $id);
        $log_type = "Archive User";
    } elseif($action === 'restore'){
        $stmt = $conn->prepare("UPDATE users SET status='active' WHERE id=?");
        $stmt->bind_param("i", $id);
        $log_type = "Restore User";
    } elseif($action === 'reset'){
        $new_pass = password_hash("123456", PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $new_pass, $id);
        $log_type = "Reset Password";
    }

    if(isset($stmt) && $stmt->execute()){
        // --- INSERT LOG TO DATABASE ---
        $desc = "Admin performed $log_type on User ID: $id";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("iiss", $admin_id, $id, $log_type, $desc);
        $log_stmt->execute();

        header("Location: manage_user.php?msg=User+" . ucfirst($action) . "d+Successfully");
        exit();
    } else {
        // --- STYLED ERROR PAGE PARA SA MGA FAILURES ---
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Action Failed</title>
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
                <h2>Action Failed</h2>
                <p>Unable to process the <strong><?php echo $action; ?></strong> request for this user. Please try again or contact support.</p>
                <a href="manage_user.php" class="btn">Back to Manage Users</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    header("Location: manage_user.php");
    exit();
}
?>