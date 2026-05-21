<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
require '../functions/password_strength.php';
checkLogin();
checkRole('superadmin');

$success_msg = "";
$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $plain_password = $_POST['password'];
    
    // Validate password strength
    $password_errors = [];
    if (!validatePasswordStrength($plain_password, $password_errors)) {
        $error_msg = "Password requirements not met:<br> - " . implode("<br> - ", $password_errors);
    } else {
        $password = password_hash($plain_password, PASSWORD_DEFAULT);
        $role = 'admin';
        $status = 'active';

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0){
            $error_msg = "Error: Email already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,email,password,role,status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss",$first_name,$last_name,$email,$password,$role,$status);
            
            if($stmt->execute()){
                // Log the activity
                $superadmin_id = $_SESSION['user_id'];
                $new_admin_id = $conn->insert_id;
                $action_type = "Add Admin";
                $description = "Super Admin added a new admin: $first_name $last_name";
                
                $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, user_id, action_type, description) VALUES (?, ?, ?, ?)");
                $log_stmt->bind_param("iiss", $superadmin_id, $new_admin_id, $action_type, $description);
                $log_stmt->execute();
                
                $success_msg = "Admin Account Created Successfully!";
                
                // Clear password field after success
                $plain_password = "";
            } else {
                $error_msg = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <?php echo getPasswordStrengthJS(); ?>
    <style>
        :root {
            --super-navy: #000000;
            --sidebar-bg: #000000;
            --accent-gold: #f59e0b;
            --bg-color: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        .sidebar { width: 280px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 25px; position: fixed; height: 100%; border-right: 4px solid var(--accent-gold); }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px; text-align: center; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 2px; }
        .sidebar-section { margin-bottom: 30px; }
        .sidebar-section h3 { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 15px; padding-left: 10px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 15px; margin-bottom: 8px; border-radius: 10px; display: block; transition: 0.3s; font-size: 0.95rem; }
        .sidebar a:hover { background: rgba(245, 158, 11, 0.1); color: var(--accent-gold); padding-left: 20px; }
        .sidebar a.active { background: var(--accent-gold); color: var(--super-navy); font-weight: 600; }
        .logout-btn { margin-top: auto; background: #ef4444; color: white !important; text-align: center; border-radius: 10px; }

        .main-content { 
            margin-left: 280px; 
            padding: 40px; 
            width: calc(100% - 280px); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh;
        }

        .form-container { 
            background: white; 
            width: 100%; 
            max-width: 600px; 
            border-radius: 20px; 
            overflow-y: auto;  /* ✅ Para mag-scroll kung sobra ang content */
            max-height: 90vh;  /* ✅ Limitahan ang height */
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
            border-top: 6px solid var(--accent-gold);
        }

        .form-header { background: var(--super-navy); color: white; padding: 25px 30px; text-align: center; }
        .form-header h2 { font-weight: 600; font-size: 1.5rem; color: var(--accent-gold); }
        .form-header p { font-size: 0.85rem; color: #94a3b8; margin-top: 5px; }

        .form-body { 
            padding: 30px;  /* ✅ Binawasan ang padding para magkasya */
        }
        
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        
        .input-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.7rem; font-weight: 600; color: #64748b; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        
        input { 
            width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 10px; 
            outline: none; transition: 0.3s; font-size: 0.9rem; background: #f8fafc;
        }
        input:focus { border-color: var(--accent-gold); background: #fff; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }

        button { 
            width: 100%; padding: 14px; background: var(--super-navy); color: white; border: none; 
            border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
            margin-top: 10px; margin-bottom: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        button:hover { background: var(--accent-gold); color: var(--super-navy); transform: translateY(-2px); }

        .success-banner { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; font-weight: 600; border: 1px solid #10b981; }
        .error-banner { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; font-weight: 600; border: 1px solid #ef4444; }
        
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #94a3b8; font-size: 0.85rem; transition: 0.3s; }
        .back-link:hover { color: var(--super-navy); }
        
        .password-strength-container {
            margin-top: 5px;
            margin-bottom: 8px;
        }
        .password-strength-bar-bg {
            background-color: #e2e8f0;
            height: 4px;
            border-radius: 5px;
            margin-top: 5px;
        }
        .password-strength-bar {
            width: 0%;
            height: 4px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .password-requirements {
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 8px;
            margin-top: 8px;
            border: 1px solid #e2e8f0;
        }
        .req-item {
            margin: 2px 0;
            transition: 0.2s;
            font-size: 0.65rem;
        }
        
        #password-strength-display {
            font-size: 0.75rem;
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
            <a href="add_admin.php" class="active">Add New Admin</a>
            <a href="manage_admin.php">Manage Admins</a>
        </div>
        <a href="../logout.php" class="logout-btn">Logout System</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2>Admin Registration</h2>
                <p>Create a new administrator account with full inventory access.</p>
            </div>

            <div class="form-body">
                <?php if($success_msg): ?>
                    <div class="success-banner">✓ <?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if($error_msg): ?>
                    <div class="error-banner">⚠ <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validatePasswordForm();">
                    <?php echo csrfField(); ?>
                    <div class="grid-inputs">
                        <div class="input-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" placeholder="Juan" required>
                        </div>
                        <div class="input-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" placeholder="Dela Cruz" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="admin@store.com" required>
                    </div>

                    <div class="input-group">
                        <label>Initial Password</label>
                        <input type="password" name="password" id="new_password" placeholder="••••••••" required>
                        <div class="password-strength-container">
                            <div id="password-strength-display">Password Strength: <span style="color: #ef4444;">Weak</span></div>
                            <div class="password-strength-bar-bg">
                                <div id="password-strength-bar" class="password-strength-bar"></div>
                            </div>
                        </div>
                        <?php echo getPasswordRequirementsHTML(); ?>
                    </div>

                    <!-- ✅ SUBMIT BUTTON - NASA TAMA NA POSITION -->
                    <button type="submit">Create Admin Account</button>
                </form>

                <a href="manage_admin.php" class="back-link">← Cancel and Return to List</a>
            </div>
        </div>
    </div>

</body>
</html>