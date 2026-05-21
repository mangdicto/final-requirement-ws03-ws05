<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('superadmin');

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval(base64_decode($_GET['id']));
    $action = $_GET['action'];
    $superadmin_id = $_SESSION['user_id'];

    // Kuhanin muna ang pangalan ng Admin para sa logs
    $admin_info_query = $conn->query("SELECT first_name, last_name FROM users WHERE id=$id AND role='admin'");
    $admin_info = $admin_info_query->fetch_assoc();
    $admin_full_name = ($admin_info) ? $admin_info['first_name'] . " " . $admin_info['last_name'] : "Unknown Admin";

    // Restore = active, Archive = archived
    if ($action === 'restore') {
        $new_status = 'active';
        $log_type = "Restore Admin";
        $desc = "Super Admin restored Admin account: $admin_full_name";
    } else {
        $new_status = 'archived';
        $log_type = "Archive Admin";
        $desc = "Super Admin archived Admin account: $admin_full_name";
    }

    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=? AND role='admin'");
    $stmt->bind_param("si", $new_status, $id);
    
    if ($stmt->execute()) {
        // --- INSERT LOG TO DATABASE ---
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("iiss", $superadmin_id, $id, $log_type, $desc);
        $log_stmt->execute();

        header("Location: manage_admin.php?msg=" . urlencode("Admin Account " . ucfirst($action) . "d Successfully"));
        exit();
    } else {
        // --- STYLED ERROR PAGE PARA SA SUPER ADMIN ---
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Process Error</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; font-family: 'Poppins', sans-serif; margin: 0; }
                .error-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #ef4444; max-width: 450px; }
                .icon { font-size: 50px; color: #ef4444; margin-bottom: 20px; }
                h2 { color: #0f172a; margin: 0 0 10px; }
                p { color: #64748b; margin-bottom: 25px; line-height: 1.6; }
                .btn { background: #1e293b; color: white; padding: 12px 25px; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-block; transition: 0.3s; border: 1px solid #f59e0b; }
                .btn:hover { background: #f59e0b; color: #0f172a; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="icon">⚠️</div>
                <h2>Database Error</h2>
                <p>We encountered a problem while trying to <strong><?php echo $action; ?></strong> the admin account for <strong><?php echo htmlspecialchars($admin_full_name); ?></strong>.</p>
                <code style="display:block; background:#f8fafc; padding:10px; border-radius:5px; margin-bottom:20px; font-size:0.8rem;"><?php echo $conn->error; ?></code>
                <a href="manage_admin.php" class="btn">Return to Management</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    header("Location: manage_admin.php");
    exit();
}
?>  