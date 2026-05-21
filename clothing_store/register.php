<?php
require 'config/database.php';
require 'functions/csrf.php';
require 'functions/password_strength.php';

// Kung naka-login na, diretso sa dashboard
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $first_name = trim(htmlspecialchars($_POST['first_name']));
    $last_name = trim(htmlspecialchars($_POST['last_name']));
    $email = trim(htmlspecialchars($_POST['email']));
    $plain_password = $_POST['password'];
    
    // Validate password strength
    $password_errors = [];
    if (!validatePasswordStrength($plain_password, $password_errors)) {
        $error_msg = "Password requirements not met:<br> - " . implode("<br> - ", $password_errors);
    } else {
        $password = password_hash($plain_password, PASSWORD_DEFAULT);
        $role = 'regular';
        $status = 'active';

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0){
            $error_msg = "Error: Email address is already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $role, $status);
            
            if($stmt->execute()){
                $new_user_id = $conn->insert_id;
                
                // Auto-login after registration
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['role'] = 'regular';
                
                // Log the registration
                $action_type = "User Registration";
                $description = "New user registered: $first_name $last_name";
                $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $new_user_id, $action_type, $description);
                $log_stmt->execute();
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_msg = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Clothing Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <?php echo getPasswordStrengthJS(); ?>
    <style>
       :root {
            --super-navy: #000000;
            --accent-gold: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        body::before {
            content: "";
            position: fixed;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--super-navy) 0%, #1e293b 100%);
            clip-path: polygon(0 0, 100% 0, 100% 40%, 0 80%);
            z-index: -1;
            top: 0;
            left: 0;
        }

        .register-container {
            background: white;
            width: 100%;
            max-width: 550px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            border-top: 5px solid var(--accent-gold);
            text-align: center;
        }

        .register-container h2 {
            color: var(--super-navy);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .register-container p {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: 0.3s;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        button {
            width: 100%;
            background-color: var(--super-navy);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #f59e0b;
            transform: translateY(-2px);
        }

        .error-box {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #fee2e2;
            text-align: left;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .footer-text a {
            color: var(--accent-gold);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

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

    <div class="register-container">
        <h2>Create Account</h2>
        <p>Join us and start managing your clothing inventory.</p>

        <?php if($error_msg != ""): ?>
            <div class="error-box">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validatePasswordForm();">
            <?php echo csrfField(); ?>
            
            <div class="name-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="Juan" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Dela Cruz" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="new_password" placeholder="Create a strong password" required>
                <div class="password-strength-container">
                    <div id="password-strength-display">Password Strength: <span style="color: #ef4444;">Weak</span></div>
                    <div class="password-strength-bar-bg">
                        <div id="password-strength-bar" class="password-strength-bar"></div>
                    </div>
                </div>
                <?php echo getPasswordRequirementsHTML(); ?>
            </div>

            <button type="submit">Register Account</button>
        </form>

        <div class="footer-text">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

</body>
</html>