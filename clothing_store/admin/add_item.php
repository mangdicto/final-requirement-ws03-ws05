<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
checkLogin();
checkRole('admin');

// Predefined categories for clothing & apparel
$categories = ["T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Skirt", "Shoes", "Hat", "Accessories"];

$success_msg = "";
$error_msg = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    // Negative number validation
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    if($price < 0){
        $error_msg = "Error: Price cannot be negative!";
    } elseif($quantity < 0){
        $error_msg = "Error: Quantity cannot be negative!";
    } else {
        $photoName = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName);

        $stmt = $conn->prepare("INSERT INTO items
        (item_name,category,size,color,price,quantity,photo,added_by,status)
        VALUES (?,?,?,?,?,?,?,?,'approved')");

        $stmt->bind_param("sssssisi",
            $_POST['item_name'],
            $_POST['category'],
            $_POST['size'],
            $_POST['color'],
            $price,
            $quantity,
            $photoName,
            $_SESSION['user_id']
        );

        if ($stmt->execute()) {
            $item_id = $conn->insert_id;
            $admin_id = $_SESSION['user_id'];
            $item_name = $_POST['item_name'];
            $action_type = "Add Item (Admin)";
            $description = "Admin added a new item directly: $item_name";

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, item_id, action_type, description) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("iiss", $admin_id, $item_id, $action_type, $description);
            $log_stmt->execute();

            $success_msg = "Item Added Successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
}

// Para sa sidebar badge count
$pending_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='pending'");
$total_pending = $pending_count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Item - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #f59e0b;
            --bg-color: #f1f5f9;
            --danger: #ef4444;
            --success: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        /* Sidebar Consistency */
        .sidebar { width: 260px; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar h3 { font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; margin: 20px 0 10px 10px; letter-spacing: 1px; }
        .sidebar a { color: white; text-decoration: none; padding: 12px 15px; margin-bottom: 5px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .sidebar a:hover { background-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--accent-color); }
        .badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .logout-btn { margin-top: auto; background-color: var(--danger); text-align: center; justify-content: center !important; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); display: flex; flex-direction: column; align-items: center; }
        
        .form-card { background: white; width: 100%; max-width: 800px; padding: 35px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: var(--primary-color); margin-bottom: 30px; font-weight: 600; text-align: center; border-bottom: 2px solid var(--bg-color); padding-bottom: 15px; }

        /* Two-Column Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .full-width { grid-column: span 2; }

        label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; }
        input, select { 
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; 
            outline: none; transition: 0.3s; font-size: 0.95rem; background-color: #f8fafc;
        }
        input:focus, select:focus { border-color: var(--accent-color); background-color: #fff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

        button { 
            width: 100%; padding: 15px; background: var(--primary-color); color: white; border: none; 
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        button:hover { background: var(--accent-color); transform: translateY(-2px); }

        .msg { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; text-align: center; font-weight: 600; }
        .msg-success { background: #d1fae5; color: #065f46; border: 1px solid var(--success); }
        .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid var(--danger); }
        
        .back-link { display: block; text-align: center; margin-top: 25px; text-decoration: none; color: #64748b; font-size: 0.9rem; transition: 0.3s; }
        .back-link:hover { color: var(--accent-color); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <h3>Items Management</h3>
        <a href="add_item.php" class="active">Add Item</a>
        <a href="view_item.php">View Items</a>
        <a href="pending_items.php">
            Approve Items 
            <?php if($total_pending > 0): ?>
                <span class="badge"><?php echo $total_pending; ?></span>
            <?php endif; ?>
        </a>
        <h3>Users Management</h3>
        <a href="add_user.php">Add User</a>
        <a href="manage_user.php">Manage Users</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="form-card">
            <h2>Add New Inventory Item</h2>

            <?php if($success_msg): ?>
                <div class="msg msg-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="msg msg-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Product Name</label>
                        <input type="text" name="item_name" placeholder="e.g. Vintage Denim Jacket" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">-- Select --</option>
                            <?php foreach($categories as $cat){
                                echo "<option value='".htmlspecialchars($cat)."'>".htmlspecialchars($cat)."</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Size</label>
                        <input type="text" name="size" placeholder="e.g. M, L, XL, 32" required>
                    </div>

                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" placeholder="e.g. Navy Blue" required>
                    </div>

                    <div class="form-group">
                        <label>Price (PHP)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" placeholder="0" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Product Photo</label>
                        <input type="file" name="photo" accept="image/*" required>
                    </div>
                </div>

                <button type="submit">Upload & Add to Inventory</button>
            </form>

            <a href="dashboard.php" class="back-link">← Back to Admin Dashboard</a>
        </div>
    </div>

</body>
</html>