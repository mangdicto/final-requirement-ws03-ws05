<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
require '../functions/password_strength.php';
checkLogin();
checkRole('superadmin');

$id = intval(base64_decode($_GET['id']));
$success_msg = "";
$error_msg = "";

$admin_data = $conn->query("SELECT first_name, last_name, email FROM users WHERE id=$id AND role='admin'")->fetch_assoc();
if(!$admin_data){
    header("Location: manage_admin.php?error=Admin+not+found");
    exit();
}
$display_name = $admin_data['first_name'] . " " . $admin_data['last_name'];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $plain_password = $_POST['new_password'];
    
    // Validate password strength
    $password_errors = [];
    if (!validatePasswordStrength($plain_password, $password_errors)) {
        $error_msg = "Password requirements not met:<br> - " . implode("<br> - ", $password_errors);
    } else {
        $new_password = password_hash($plain_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='admin'");
        $stmt->bind_param("si", $new_password, $id);
        
        if($stmt->execute()){
            $superadmin_id = $_SESSION['user_id'];
            $action_type = "Reset Admin Password";
            $description = "Super Admin reset password of Admin: $display_name";

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("iiss", $superadmin_id, $id, $action_type, $description);
            $log_stmt->execute();

            $success_msg = "Password Reset Successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security - Reset Admin Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <?php echo getPasswordStrengthJS(); ?>
    <style>
        :root {
            --super-navy: #000000;
            --sidebar-bg: #000000;
            --accent-gold: #f59e0b;
            --bg-color: #f1f5f9;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        .sidebar { width: 280px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 25px; position: fixed; height: 100%; border-right: 4px solid var(--accent-gold); }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px; text-align: center; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 2px; }
        .sidebar-section { margin-bottom: 30px; }
        .sidebar-section h3 { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 15px; padding-left: 10px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 15px; margin-bottom: 8px; border-radius: 10px; display: block; transition: 0.3s; font-size: 0.95rem; }
        .sidebar a:hover { background: rgba(245, 158, 11, 0.1); color: var(--accent-gold); padding-left: 20px; }
        .logout-btn { margin-top: auto; background: #ef4444; color: white !important; text-align: center; border-radius: 10px; }

        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); display: flex; align-items: center; justify-content: center; }

        .security-card { 
            background: white; width: 100%; max-width: 500px; border-radius: 20px; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;
            text-align: center;
        }

        .card-header { background: #fee2e2; padding: 30px; border-bottom: 2px solid #fecaca; }
        .lock-icon { font-size: 40px; margin-bottom: 10px; }
        .card-header h2 { color: #991b1b; font-size: 1.2rem; font-weight: 600; }

        .card-body { padding: 40px; }
        .admin-info { margin-bottom: 25px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
        .admin-info p { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .admin-info strong { font-size: 1rem; color: var(--super-navy); }

        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        
        input { 
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; 
            outline: none; transition: 0.3s; font-size: 1rem;
        }
        input:focus { border-color: var(--accent-gold); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }

        button { 
            width: 100%; padding: 15px; background: var(--super-navy); color: white; border: none; 
            border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        button:hover { background: var(--accent-gold); color: var(--super-navy); transform: translateY(-2px); }

        .msg-banner { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid var(--danger); text-align: left; }
        
        .back-link { display: block; margin-top: 25px; text-decoration: none; color: #94a3b8; font-size: 0.85rem; transition: 0.3s; }
        .back-link:hover { color: var(--danger); }
        
        .password-strength-container {
            margin-top: 8px;
            margin-bottom: 10px;
        }
        .password-strength-bar-bg {
            background-color: #e2e8f0;
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
        }
        .password-strength-bar {
            width: 0%;
            height: 5px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .password-requirements {
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .req-item {
            margin: 3px 0;
            transition: 0.2s;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Super Admin</h2>
        <div class="sidebar-section">
            <h3>Overview</h3>
            <a href="dashboard.php">Control Panel</a>
        </div>
        <div class="sidebar-section">
            <h3>Admin Management</h3>
            <a href="add_admin.php">Add New Admin</a>
            <a href="manage_admin.php" class="active">Manage Admins</a>
        </div>
        <a href="../logout.php" class="logout-btn">Logout System</a>
    </div>

    <div class="main-content">
        <div class="security-card">
            <div class="card-header">
                <div class="lock-icon">🔒</div>
                <h2>Reset Admin Password</h2>
            </div>

            <div class="card-body">
                <div class="admin-info">
                    <p>Target Administrator</p>
                    <strong><?php echo htmlspecialchars($display_name); ?></strong><br>
                    <small><?php echo htmlspecialchars($admin_data['email']); ?></small>
                </div>

                <?php if($success_msg): ?>
                    <div class="msg-banner success">✓ <?php echo $success_msg; ?></div>
                <?php endif; ?>

                <?php if($error_msg): ?>
                    <div class="msg-banner error">⚠ <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validatePasswordForm();">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label>New Secure Password</label>
                        <input type="password" name="new_password" id="new_password" placeholder="••••••••" required autofocus>
                        <div class="password-strength-container">
                            <div id="password-strength-display">Password Strength: <span style="color: #ef4444;">Weak</span></div>
                            <div class="password-strength-bar-bg">
                                <div id="password-strength-bar" class="password-strength-bar"></div>
                            </div>
                        </div>
                        <?php echo getPasswordRequirementsHTML(); ?>
                    </div>
                    <button type="submit">Update Password</button>
                </form>

                <a href="manage_admin.php" class="back-link">Cancel and Go Back</a>
            </div>
        </div>
    </div>

</body>
</html>