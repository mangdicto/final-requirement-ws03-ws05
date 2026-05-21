<?php
require '../config/database.php';
require '../functions/auth.php';
require '../functions/csrf.php';
checkLogin();
checkRole('regular');

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Get user data
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])){
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $first_name = trim(htmlspecialchars($_POST['first_name']));
    $last_name = trim(htmlspecialchars($_POST['last_name']));
    
    $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $first_name, $last_name, $user_id);
    
    if($update_stmt->execute()){
        $success_msg = "Profile updated successfully!";
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
    } else {
        $error_msg = "Error updating profile.";
    }
}

// Handle password change
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])){
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $pass_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $pass_stmt->bind_param("i", $user_id);
    $pass_stmt->execute();
    $user_pass = $pass_stmt->get_result()->fetch_assoc();
    
    if(!password_verify($current_password, $user_pass['password'])){
        $error_msg = "Current password is incorrect.";
    } elseif($new_password !== $confirm_password){
        $error_msg = "New passwords do not match.";
    } elseif(strlen($new_password) < 8){
        $error_msg = "Password must be at least 8 characters.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if($update_stmt->execute()){
            $success_msg = "Password changed successfully!";
        } else {
            $error_msg = "Error changing password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Clothing Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #000000;
            --sidebar-bg: #121212;
            --accent-gold: #f59e0b;
            --bg-light: #f5f5f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-light); min-height: 100vh; padding-top: 60px; }

        .top-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 55px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }

        .nav-left { display: flex; align-items: center; gap: 12px; }
        .hamburger { font-size: 1.1rem; cursor: pointer; color: var(--primary-black); }
        .brand { font-size: 1rem; font-weight: 700; color: var(--primary-black); text-transform: uppercase; text-decoration: none; }

        .sidebar {
            position: fixed;
            top: 0; left: -240px;
            width: 240px; height: 100%;
            background: var(--sidebar-bg); color: white;
            transition: 0.3s ease;
            z-index: 1001; padding: 20px 15px;
            display: flex; flex-direction: column;
        }
        .sidebar.active { left: 0; }
        .sidebar h2 { margin-bottom: 15px; font-size: 1rem; border-bottom: 1px solid #334155; padding-bottom: 8px; }
        .sidebar a {
            color: #cbd5e1; text-decoration: none; padding: 8px 12px;
            border-radius: 6px; margin-bottom: 3px; display: flex;
            align-items: center; gap: 10px; font-size: 0.8rem;
        }
        .sidebar a:hover, .sidebar a.active { background: #f59e0b; color: white; }
        .logout-btn { margin-top: auto; background: #ef4444; font-size: 0.8rem !important; justify-content: center; }

        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: none; z-index: 1000; }
        .overlay.active { display: block; }

        .main-content { padding: 20px; max-width: 800px; margin: 0 auto; }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .card-header {
            background: var(--primary-black);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .card-header i {
            font-size: 40px;
            color: var(--accent-gold);
        }
        
        .card-header h2 {
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-gold);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-black);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--accent-gold);
        }
        
        .btn-warning {
            background: #dc2626;
            color: white;
        }
        
        .btn-warning:hover {
            background: #b91c1c;
        }
        
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--accent-gold);
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <nav class="top-nav">
        <div class="nav-left">
            <i class="fas fa-bars hamburger" id="menuBtn"></i>
            <a href="dashboard.php" class="brand">ClothingStore</a>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home Dashboard</a>
        <a href="add_item.php"><i class="fas fa-plus-circle"></i> Add Item</a>
        <a href="pending_items.php"><i class="fas fa-clock"></i> Pending</a>
        <a href="approved_items.php"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <?php if($success_msg): ?>
            <div class="success-msg">✓ <?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if($error_msg): ?>
            <div class="error-msg">⚠ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Update Profile Form -->
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-user-circle"></i>
                <h2>Edit Profile</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #f1f5f9;">
                        <small style="color: #64748b; font-size: 0.7rem;">Email cannot be changed</small>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-lock"></i>
                <h2>Change Password</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        menuBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
    </script>
</body>
</html>