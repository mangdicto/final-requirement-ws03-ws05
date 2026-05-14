<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('superadmin');

// In-update ang query para hiwalay ang Active sa Archived
$active_result = $conn->query("SELECT * FROM users WHERE role='admin' AND status='active'");
$archived_result = $conn->query("SELECT * FROM users WHERE role='admin' AND status='archived'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins | Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --super-navy: #000000;
            --sidebar-bg: #000000;
            --accent-gold: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
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
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 { color: var(--super-navy); font-size: 1.8rem; }
        
        .btn-add { background: var(--super-navy); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.3s; border: 1px solid var(--accent-gold); }
        .btn-add:hover { background: var(--accent-gold); color: var(--super-navy); }

        .section-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .section-card h2 { font-size: 1.1rem; color: var(--super-navy); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Table Design */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 15px; background: #f8fafc; color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
        
        .status-pill { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-archived { background: #fee2e2; color: #991b1b; }

        .action-link { text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: 0.2s; }
        .archive-btn { color: var(--danger); }
        .archive-btn:hover { text-decoration: underline; }
        .reset-btn { color: var(--accent-gold); margin-left: 10px; }
        .restore-btn { color: var(--success); }

        hr { border: none; border-top: 1px dashed #cbd5e1; margin: 40px 0; }
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
        <div class="header-flex">
            <h1>Admin Management</h1>
            <a href="add_admin.php" class="btn-add">+ Add New Admin</a>
        </div>

        <div class="section-card">
            <h2><span style="color: var(--success);">●</span> Active Administrators</h2>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($active_result && $active_result->num_rows > 0): ?>
                        <?php while($row = $active_result->fetch_assoc()): ?>
                            <?php $id = base64_encode($row['id']); ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="status-pill status-active"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="archive_admin.php?action=archive&id=<?php echo $id; ?>" class="action-link archive-btn" onclick="return confirm('Archive this admin? They will lose access to the panel.')">Archive</a>
                                    <a href="reset_admin_password.php?id=<?php echo $id; ?>" class="action-link reset-btn">Reset Pass</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" align="center">No active administrators found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr>

        <div class="section-card" style="opacity: 0.85;">
            <h2 style="color: #64748b;">Archived Accounts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($archived_result && $archived_result->num_rows > 0): ?>
                        <?php while($row = $archived_result->fetch_assoc()): ?>
                            <?php $id = base64_encode($row['id']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="status-pill status-archived"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="archive_admin.php?action=restore&id=<?php echo $id; ?>" class="action-link restore-btn">Restore Admin Account</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" align="center">No archived accounts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem;">← Back to Control Panel</a>
    </div>

</body>
</html>