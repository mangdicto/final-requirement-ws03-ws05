<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
require '../functions/password_strength.php';
checkLogin();
checkRole('admin');

$success_msg = "";
$error_msg = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $plain_password = $_POST['password'];
    
    // Validate password strength
    $password_errors = [];
    if (!validatePasswordStrength($plain_password, $password_errors)) {
        $error_msg = "Password requirements not met:<br> - " . implode("<br> - ", $password_errors);
    } else {
        $password = password_hash($plain_password, PASSWORD_DEFAULT);
        $role = "regular";

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0){
            $error_msg = "Error: Email already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users(first_name,last_name,email,password,role,status) VALUES(?,?,?,?,?,'active')");
            $stmt->bind_param("sssss",$first_name,$last_name,$email,$password,$role);
            
            if($stmt->execute()){
                $new_user_id = $conn->insert_id;
                $admin_id = $_SESSION['user_id'];
                $action_type = "Add User";
                $description = "Admin added a new regular user: $first_name $last_name";

                $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
                $log_stmt->bind_param("iiss", $admin_id, $new_user_id, $action_type, $description);
                $log_stmt->execute();

                $success_msg = "User Added Successfully!";
            } else {
                $error_msg = "Error: " . $conn->error;
            }
        }
    }
}

$pending_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='pending'");
$total_pending = $pending_count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User - Clothing Store Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <?php echo getPasswordStrengthJS(); ?>
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #f59e0b;
            --bg-color: #f1f5f9;
            --text-dark: #334155;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        .sidebar { width: 260px; background-color: var(--primary-color); color: white; display: flex; flex-direction: column; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 30px; text-align: center; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar h3 { font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; margin: 20px 0 10px 10px; letter-spacing: 1px; }
        .sidebar a { color: white; text-decoration: none; padding: 12px 15px; margin-bottom: 5px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .sidebar a:hover { background-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--accent-color); }
        .badge { background: var(--danger); color: white; padding: 2px 8px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .logout-btn { margin-top: auto; background-color: var(--danger); text-align: center; justify-content: center !important; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); display: flex; flex-direction: column; align-items: center; }
        
        .form-card { background: white; width: 100%; max-width: 550px; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: var(--primary-color); margin-bottom: 25px; font-weight: 600; text-align: center; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; color: #64748b; margin-bottom: 8px; font-weight: 600; }
        .form-group input { 
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; 
            outline: none; transition: 0.3s; font-size: 0.95rem;
        }
        .form-group input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

        button { 
            width: 100%; padding: 12px; background: var(--accent-color); color: white; border: none; 
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        button:hover { background: #2563eb; }

        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #64748b; font-size: 0.9rem; }
        .back-link:hover { color: var(--accent-color); }

        .msg { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .msg-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid var(--danger); }
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
        <a href="add_user.php" class="active">Add User</a>
        <a href="manage_user.php">Manage Users</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="form-card">
            <h2>Add New User</h2>

            <?php if($success_msg): ?>
                <div class="msg msg-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="msg msg-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validatePasswordForm();">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="Enter first name" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Enter last name" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="email@example.com" required>
                </div>

                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="password" name="password" id="new_password" placeholder="Create a password" required>
                    <div class="password-strength-container">
                        <div id="password-strength-display">Password Strength: <span style="color: #ef4444;">Weak</span></div>
                        <div class="password-strength-bar-bg">
                            <div id="password-strength-bar" class="password-strength-bar"></div>
                        </div>
                    </div>
                    <?php echo getPasswordRequirementsHTML(); ?>
                </div>

                <button type="submit">Add User Account</button>
            </form>

            <a href="manage_user.php" class="back-link">← Back to Users List</a>
        </div>
    </div>

</body>
</html>