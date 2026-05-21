<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
checkLogin();
checkRole('admin');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $id = intval($_POST['id']);
    $name = $_POST['item_name'];
    $cat = $_POST['category'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $price = floatval($_POST['price']);
    $qty = intval($_POST['quantity']);
    $admin_id = $_SESSION['user_id'];
    
    // Negative number validation
    if($price < 0){
        die("Error: Price cannot be negative!");
    }
    if($qty < 0){
        die("Error: Quantity cannot be negative!");
    }

    $error_occurred = false;
    $error_msg = "";
    $stmt = null;
    $log_stmt = null;

    // Check kung may bagong photo na in-upload
    if(!empty($_FILES['photo']['name'])){
        $photoName = time() . "_" . $_FILES['photo']['name'];
        if(move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName)){
            $stmt = $conn->prepare("UPDATE items SET item_name=?, category=?, size=?, color=?, price=?, quantity=?, photo=? WHERE id=?");
            if(!$stmt){
                $error_occurred = true;
                $error_msg = $conn->error;
            } else {
                $stmt->bind_param("ssssdisi", $name, $cat, $size, $color, $price, $qty, $photoName, $id);
            }
        } else {
            $error_occurred = true;
            $error_msg = "Failed to move the uploaded photo to the server.";
        }
    } else {
        $stmt = $conn->prepare("UPDATE items SET item_name=?, category=?, size=?, color=?, price=?, quantity=? WHERE id=?");
        if(!$stmt){
            $error_occurred = true;
            $error_msg = $conn->error;
        } else {
            $stmt->bind_param("ssssdii", $name, $cat, $size, $color, $price, $qty, $id);
        }
    }
    if(!$error_occurred && $stmt && $stmt->execute()){
        $get_owner = $conn->query("SELECT added_by FROM items WHERE id=$id");
        $owner_id = $get_owner->fetch_assoc()['added_by'] ?? 0;

        $desc = "Admin updated details of item: $name";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, item_id, action_type, description) VALUES (?, ?, ?, 'Update Item', ?)");
        if($log_stmt){
            $log_stmt->bind_param("iiis", $admin_id, $owner_id, $id, $desc);
            $log_stmt->execute();
        }

        if($stmt){
            $stmt->close();
        }
        if($log_stmt){
            $log_stmt->close();
        }

        header("Location: view_item.php?msg=Item+Updated+Successfully");
        exit();
    } else {
        $final_error = $error_msg ?: $conn->error;
        if($stmt){
            $stmt->close();
        }
        if(isset($log_stmt) && $log_stmt){
            $log_stmt->close();
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Update Error</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f1f5f9; font-family: 'Poppins', sans-serif; margin: 0; }
                .error-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #ef4444; max-width: 450px; }
                .icon { font-size: 50px; color: #ef4444; margin-bottom: 20px; }
                h2 { color: #1e293b; margin: 0 0 10px; }
                p { color: #64748b; margin-bottom: 25px; line-height: 1.6; }
                .btn { background: #f59e0b; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; transition: 0.3s; }
                .btn:hover { background: #1e40af; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="icon">❌</div>
                <h2>Update Failed</h2>
                <p>An error occurred while updating <strong><?php echo htmlspecialchars($name); ?></strong>.<br>
                   <small><?php echo $final_error; ?></small>
                </p>
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