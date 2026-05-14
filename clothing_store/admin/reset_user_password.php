<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

// Decode the ID from the URL
$id = intval(base64_decode($_GET['id']));

// Kuhanin ang pangalan ng user para sa mas magandang UI (Dagdag info lang, walang tinanggal)
$user_info = $conn->query("SELECT first_name, last_name FROM users WHERE id=$id");
$user = $user_info->fetch_assoc();
$display_name = ($user) ? $user['first_name'] . " " . $user['last_name'] : "User ID: $id";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='regular'");
    $stmt->bind_param("si",$newPassword,$id);
    
    if($stmt->execute()){

        // --- ACTIVITY LOGS START ---
        $admin_id = $_SESSION['user_id'];
        $action_type = "Reset User Password";
        $description = "Admin reset password of User: $display_name";

        $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("iiss", $admin_id, $id, $action_type, $description);
        $log_stmt->execute();
        // --- ACTIVITY LOGS END ---

        header("Location: manage_user.php?msg=Password+Reset+Successfully");
        exit();
    } else {
        $error_msg = "Error resetting password: " . $conn->error;
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
    <title>Reset Password - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #3b82f6;
            --bg-color: #f1f5f9;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        .sidebar { width: 260px; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar h3 { font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; margin: 20px 0 10px 10px; letter-spacing: 1px; }
        .sidebar a { color: white; text-decoration: none; padding: 12px 15px; margin-bottom: 5px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .sidebar a:hover { background-color: var(--accent-color); }
        .badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .logout-btn { margin-top: auto; background-color: var(--danger); text-align: center; justify-content: center !important; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); display: flex; flex-direction: column; align-items: center; justify-content: center; }
        
        .reset-card { background: white; width: 100%; max-width: 450px; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; }
        
        .icon-box { width: 70px; height: 70px; background: #fee2e2; color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px; }
        
        h2 { color: var(--primary-color); margin-bottom: 10px; font-weight: 600; }
        p { color: #64748b; margin-bottom: 30px; font-size: 0.9rem; }
        .username-highlight { color: var(--accent-color); font-weight: 600; }

        .form-group { text-align: left; margin-bottom: 25px; }
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        
        input { 
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; 
            outline: none; transition: 0.3s; font-size: 1rem; 
        }
        input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }

        .btn-submit { 
            width: 100%; padding: 14px; background: var(--primary-color); color: white; border: none; 
            border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn-submit:hover { background: var(--accent-color); transform: translateY(-2px); }

        .cancel-link { display: block; margin-top: 20px; text-decoration: none; color: #94a3b8; font-size: 0.85rem; transition: 0.3s; }
        .cancel-link:hover { color: var(--danger); }

        .error-msg { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <h3>Items Management</h3>
        <a href="add_item.php">Add Item</a>
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
        <div class="reset-card">
            <div class="icon-box">🔒</div>
            <h2>Reset Password</h2>
            <p>You are updating the password for:<br> 
               <span class="username-highlight"><?php echo htmlspecialchars($display_name); ?></span>
            </p>

            <?php if(isset($error_msg)): ?>
                <div class="error-msg"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>New Secure Password</label>
                    <input type="password" name="password" placeholder="••••••••" required autofocus>
                </div>

                <button type="submit" class="btn-submit">Confirm Reset</button>
            </form>

            <a href="manage_user.php" class="cancel-link">Cancel and Go Back</a>
        </div>
    </div>

</body>
</html>