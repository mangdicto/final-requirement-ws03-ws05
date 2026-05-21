<?php
require 'config/database.php';
require 'functions/rate_limit.php';

// Kung naka-login na, diretso sa dashboard
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

$error_message = ""; 
$user_ip = getUserIP();

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Check rate limit bago mag-query
    if(!checkRateLimit($email, $user_ip)){
        $error_message = "Too many failed attempts. Please try again after 15 minutes.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND status='active'");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows>0){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])){
                // Successful login - clear attempts
                clearLoginAttempts($email, $user_ip);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // === REMEMBER ME ===
                if(isset($_POST['remember_me']) && $_POST['remember_me'] == '1'){
                    $selector = bin2hex(random_bytes(16));
                    $validator = bin2hex(random_bytes(32));
                    $token = $selector . ':' . $validator;

                    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

                    $updateStmt = $conn->prepare("UPDATE users SET remember_selector = ?, remember_validator = ?, remember_expiry = ? WHERE id = ?");
                    $updateStmt->bind_param("sssi", $selector, $hashedValidator, $expiry, $user['id']);
                    $updateStmt->execute();

                    setcookie('remember_me', $token, time() + (86400 * 30), '/', '', false, true);
                }

                header("Location: dashboard.php");
                exit();
            }
        }
        // Failed login
        recordFailedAttempt($email, $user_ip);
        $remaining = getRemainingAttempts($email, $user_ip);
        $error_message = "Invalid Email or Password! " . $remaining . " attempt(s) remaining.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login | Control Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
       :root {
        --super-navy: #000000;
        --sidebar-bg: #111111;
        --accent-gold: #f59e0b;
        --text-light: #f9f9f9;
    }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background-color: #f1f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--super-navy) 0%, #1e293b 100%);
            clip-path: polygon(0 0, 100% 0, 100% 40%, 0 80%);
            z-index: -1;
        }

        .login-container {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            border-top: 5px solid var(--accent-gold);
            text-align: center;
        }

        .login-container h2 {
            color: var(--super-navy);
            margin-bottom: 10px;
            font-size: 1.8rem;
            letter-spacing: -1px;
        }

        .login-container p {
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
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3);
        }

        .error-box {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #fee2e2;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Welcome Back</h2>
        <p>Enter your credentials to access the system.</p>

        <?php if($error_message != ""): ?>
            <div class="error-box">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="e.g. admin@system.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <div style="display: flex; align-items: center; margin: 15px 0;">
                <input type="checkbox" name="remember_me" value="1" id="rememberCheckbox">
                <label for="rememberCheckbox" style="margin-left: 8px; margin-bottom: 0; cursor: pointer;">
                    Remember Me
                </label>
            </div>

            <button type="submit">Login to Account</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="register.php" style="color: #f59e0b; text-decoration: none; font-weight: 600;">Register here</a>
            <br><br>
            &copy; 2026 Admin Control Panel. All rights reserved.
        </div>
    </div>

</body>
</html>