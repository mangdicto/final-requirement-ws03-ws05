<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('superadmin');

$success_msg = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'admin';
    $status = 'active';

    $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,email,password,role,status) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$first_name,$last_name,$email,$password,$role,$status);
    
    if($stmt->execute()){
        $success_msg = "Admin Account Created Successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --super-navy: #000000;
            --sidebar-bg: #000000;
            --accent-gold: #f59e0b;
            --bg-color: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        /* Sidebar Consistency */
        .sidebar { width: 280px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 25px; position: fixed; height: 100%; border-right: 4px solid var(--accent-gold); }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px; text-align: center; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 2px; }
        .sidebar-section { margin-bottom: 30px; }
        .sidebar-section h3 { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 15px; padding-left: 10px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 15px; margin-bottom: 8px; border-radius: 10px; display: block; transition: 0.3s; font-size: 0.95rem; }
        .sidebar a:hover { background: rgba(245, 158, 11, 0.1); color: var(--accent-gold); padding-left: 20px; }
        .sidebar a.active { background: var(--accent-gold); color: var(--super-navy); font-weight: 600; }
        .logout-btn { margin-top: auto; background: #ef4444; color: white !important; text-align: center; border-radius: 10px; }

        /* Main Content */
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); display: flex; flex-direction: column; align-items: center; justify-content: center; }

        .form-container { 
            background: white; width: 100%; max-width: 600px; border-radius: 20px; 
            overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
            border-top: 6px solid var(--accent-gold);
        }

        .form-header { background: var(--super-navy); color: white; padding: 30px; text-align: center; }
        .form-header h2 { font-weight: 600; font-size: 1.5rem; color: var(--accent-gold); }
        .form-header p { font-size: 0.85rem; color: #94a3b8; margin-top: 5px; }

        .form-body { padding: 40px; }
        
        /* Grid Layout for Names */
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        
        .input-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        
        input { 
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; 
            outline: none; transition: 0.3s; font-size: 1rem; background: #f8fafc;
        }
        input:focus { border-color: var(--accent-gold); background: #fff; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }

        button { 
            width: 100%; padding: 15px; background: var(--super-navy); color: white; border: none; 
            border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s;
            margin-top: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        button:hover { background: var(--accent-gold); color: var(--super-navy); transform: translateY(-2px); }

        .success-banner { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; font-size: 0.9rem; font-weight: 600; border: 1px solid #10b981; }
        
        .back-link { display: block; text-align: center; margin-top: 25px; text-decoration: none; color: #94a3b8; font-size: 0.9rem; transition: 0.3s; }
        .back-link:hover { color: var(--super-navy); }
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

                <form method="POST">
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
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit">Create Admin Account</button>
                </form>

                <a href="manage_admin.php" class="back-link">← Cancel and Return to List</a>
            </div>
        </div>
    </div>

</body>
</html>