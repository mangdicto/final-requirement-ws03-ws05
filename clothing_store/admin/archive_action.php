<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval(base64_decode($_GET['id']));
    $action = $_GET['action'];
    $admin_id = $_SESSION['user_id'];

    // --- LOGGING START (Kuhanin ang info ng item bago i-update) ---
    $get_info = $conn->query("SELECT item_name, added_by FROM items WHERE id=$id");
    $info = $get_info->fetch_assoc();
    $item_name = $info['item_name'] ?? 'Unknown Item';
    $owner_id = $info['added_by'] ?? 0;
    // --- LOGGING END ---

    // Restore = approved, Archive = archived
    $new_status = ($action === 'restore') ? 'approved' : 'archived';
    $log_type = ($action === 'restore') ? 'Restore Item' : 'Archive Item';

    $stmt = $conn->prepare("UPDATE items SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $id);
    
    if ($stmt->execute()) {
        // --- INSERT LOG TO DATABASE ---
        $desc = "Admin " . $action . "d item: $item_name";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, item_id, action_type, description) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("iiiss", $admin_id, $owner_id, $id, $log_type, $desc);
        $log_stmt->execute();

        header("Location: view_item.php?msg=Item+" . ucfirst($action) . "d+Successfully");
        exit();
    } else {
        // --- STYLED ERROR PAGE SAKALING MAG-FAIL ---
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Processing...</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body { 
                    display: flex; justify-content: center; align-items: center; 
                    height: 100vh; background: #f1f5f9; font-family: 'Poppins', sans-serif; 
                }
                .error-card { 
                    background: white; padding: 40px; border-radius: 15px; 
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; 
                    border-top: 5px solid #ef4444; max-width: 400px;
                }
                .error-icon { font-size: 50px; color: #ef4444; margin-bottom: 20px; }
                h2 { color: #1e293b; margin-bottom: 10px; }
                p { color: #64748b; margin-bottom: 25px; line-height: 1.6; }
                .btn { 
                    background: #3b82f6; color: white; padding: 12px 25px; 
                    text-decoration: none; border-radius: 8px; font-weight: 600; 
                    display: inline-block; transition: 0.3s;
                }
                .btn:hover { background: #1e40af; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-icon">⚠️</div>
                <h2>System Error</h2>
                <p>We encountered a problem while trying to <?php echo $action; ?> the item. <br><strong>Error:</strong> <?php echo $conn->error; ?></p>
                <a href="view_item.php" class="btn">Return to Inventory</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    header("Location: view_item.php");
    exit();
}
?>