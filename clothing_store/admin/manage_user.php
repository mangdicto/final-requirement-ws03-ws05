<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

// Query para sa Active/Regular Users (yung hindi archived)
$result = $conn->query("SELECT * FROM users WHERE role='regular' AND status != 'archived'");

// Query para sa Archived Users (yung status ay 'archived')
$archived_result = $conn->query("SELECT * FROM users WHERE role='regular' AND status = 'archived'");

// Para sa sidebar badge count (optional pero maganda tingnan)
$pending_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='pending'");
$total_pending = $pending_count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Clothing Store Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #f59e0b;
            --bg-color: #f1f5f9;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .section-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h2 { color: var(--primary-color); }
        
        .add-btn { background: var(--accent-color); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .add-btn:hover { background: #2563eb; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.75rem; font-weight: 600; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; color: #334155; }
        
        .status-pill { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-archived { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .action-link { text-decoration: none; font-weight: 600; font-size: 0.85rem; padding: 5px 10px; border-radius: 5px; transition: 0.2s; }
        .btn-archive { color: var(--danger); border: 1px solid var(--danger); }
        .btn-archive:hover { background: var(--danger); color: white; }
        .btn-reset { color: var(--accent-color); border: 1px solid var(--accent-color); margin-left: 5px; }
        .btn-reset:hover { background: var(--accent-color); color: white; }
        .btn-restore { color: var(--success); border: 1px solid var(--success); }
        .btn-restore:hover { background: var(--success); color: white; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--success); }
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
        <a href="manage_user.php" class="active">Manage Users</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert-success">
                <strong>Success!</strong> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <div class="header-flex">
                <h2>Manage Active Users</h2>
                <a href="add_user.php" class="add-btn">+ Add New User</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $id = base64_encode($row['id']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="status-pill status-active"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <a href="user_action.php?id=<?php echo $id; ?>&action=archive" class="action-link btn-archive" onclick="return confirm('Archive this user?')">Archive</a>
                                    <a href="reset_user_password.php?id=<?php echo $id; ?>" class="action-link btn-reset">Reset Password</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' align='center'>No active users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="section-card">
            <h2 style="color: #64748b; margin-bottom: 20px;">Archived Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($archived_result && $archived_result->num_rows > 0) {
                        while($row = $archived_result->fetch_assoc()) {
                            $id = base64_encode($row['id']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="status-pill status-archived"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <a href="user_action.php?id=<?php echo $id; ?>&action=restore" class="action-link btn-restore" onclick="return confirm('Restore this user?')">Restore</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' align='center'>No archived users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" style="text-decoration: none; color: var(--accent-color); font-size: 0.9rem;">← Back to Admin Dashboard</a>
    </div>

</body>
</html>