<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

if (isset($_GET['action']) && isset($_GET['id'])) {
    
    $id = intval(base64_decode($_GET['id']));
    $action = $_GET['action'];
    $admin_id = $_SESSION['user_id'];

    // --- LOGGING START (Kuhanin ang info ng item bago i-update) ---
    $get_info = $conn->query("SELECT item_name, added_by FROM items WHERE id=$id");
    $info = $get_info->fetch_assoc();
    $item_name = $info['item_name'] ?? 'Unknown Item';
    $owner_id = $info['added_by'] ?? 0;
    // --- LOGGING END ---

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE items SET status='approved', approved_by=? WHERE id=?");
        $stmt->bind_param("ii", $admin_id, $id);
        $log_action = "Approve Item"; // Para sa logs
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE items SET status='rejected', approved_by=? WHERE id=?");
        $stmt->bind_param("ii", $admin_id, $id);
        $log_action = "Reject Item"; // Para sa logs
    }

    if (isset($stmt) && $stmt->execute()) {
        // --- INSERT LOG TO DATABASE ---
        $desc = "Admin " . ($action === 'approve' ? 'approved' : 'rejected') . " item: $item_name";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, item_id, action_type, description) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("iiiss", $admin_id, $owner_id, $id, $log_action, $desc);
        $log_stmt->execute();

        header("Location: pending_items.php?msg=success");
        exit();
    } else {
        // --- STYLED ERROR PAGE SAKALING MAG-FAIL ---
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Processing Error</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; font-family: 'Poppins', sans-serif; }
                .error-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #ef4444; }
                h2 { color: #1e293b; }
                p { color: #64748b; margin: 10px 0 20px; }
                .btn { background: #f59e0b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <h2>Oops! Error Occurred</h2>
                <p><?php echo "Error updating record: " . $conn->error; ?></p>
                <a href="pending_items.php" class="btn">Return to Pending Items</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>