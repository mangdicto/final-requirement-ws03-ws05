<?php
require '../config/database.php'; // Idinagdag para sa count query
require '../functions/auth.php';
checkLogin();
checkRole('admin');

// Query para makuha ang bilang ng pending items
$pending_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='pending'");
$pending_data = $pending_count_query->fetch_assoc();
$total_pending = $pending_data['total'];

// Dagdag na query para sa ibang statistics (Consistent sa hiningi mo)
$approved_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='approved'");
$total_approved = $approved_count_query->fetch_assoc()['total'];

$user_count_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='regular'");
$total_users = $user_count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clothing Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000; 
            --secondary-color: #334155;
            --accent-color: #f59e0b; 
            --text-light: #f8fafc;
            --bg-color: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: var(--bg-color); min-height: 100vh; }

        /* Sidebar Design */
        .sidebar {
            width: 260px;
            background-color: var(--primary-color);
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 1px solid var(--secondary-color);
            padding-bottom: 10px;
        }

        .sidebar h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #94a3b8;
            margin: 20px 0 10px 10px;
            letter-spacing: 1px;
        }

        .sidebar a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar a:hover { background-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--secondary-color); border-left: 4px solid var(--accent-color); }

        .badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .logout-btn { margin-top: auto; background-color: #ef4444; text-align: center; justify-content: center !important; }
        .logout-btn:hover { background-color: #b91c1c !important; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .page-title { color: var(--primary-color); margin-bottom: 25px; font-size: 1.8rem; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .stat-card h4 { color: #64748b; font-size: 0.9rem; margin-bottom: 10px; }
        .stat-card .number { font-size: 2.2rem; font-weight: 600; color: var(--primary-color); }
        .stat-card.urgent { border-top: 4px solid #f59e0b; }
        .stat-card.success { border-top: 4px solid #10b981; }
        .stat-card.info { border-top: 4px solid var(--accent-color); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        
        <a href="dashboard.php" class="active">Dashboard</a>

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
        <h2 class="page-title">Admin Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card urgent">
                <h4>Pending Approvals</h4>
                <div class="number"><?php echo $total_pending; ?></div>
                <p style="font-size: 0.8rem; color: #f59e0b; margin-top: 5px;">Items needing review</p>
            </div>

            <div class="stat-card success">
                <h4>Total Live Items</h4>
                <div class="number"><?php echo $total_approved; ?></div>
                <p style="font-size: 0.8rem; color: #10b981; margin-top: 5px;">Active in catalog</p>
            </div>

            <div class="stat-card info">
                <h4>Registered Users</h4>
                <div class="number"><?php echo $total_users; ?></div>
                <p style="font-size: 0.8rem; color: var(--accent-color); margin-top: 5px;">Regular user accounts</p>
            </div>
        </div>
    </div>

</body>
</html>