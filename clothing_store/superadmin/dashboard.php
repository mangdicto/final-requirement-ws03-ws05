<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('superadmin');

// --- PAGINATION LOGIC START ---
$limit = 10; // Bilang ng logs bawat pahina
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Kunin ang kabuuang bilang ng logs para sa calculation ng pages
$total_rows_query = $conn->query("SELECT COUNT(*) as total FROM activity_logs");
$total_rows = $total_rows_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
// --- PAGINATION LOGIC END ---

// Query para makuha ang Activity Logs na may LIMIT at OFFSET
$query = "SELECT l.*, 
          u1.first_name as admin_fname, u1.last_name as admin_lname, 
          u2.first_name as target_fname, u2.last_name as target_lname,
          i.item_name 
          FROM activity_logs l
          LEFT JOIN users u1 ON l.admin_id = u1.id
          LEFT JOIN users u2 ON l.user_id = u2.id
          LEFT JOIN items i ON l.item_id = i.id
          ORDER BY l.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// --- UPDATED STATS QUERIES ---

// 1. Bilang ng Active Admins (status='active')
$active_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='active'")->fetch_assoc()['total'];

// 2. Kabuuang bilang ng Logs
$total_logs = $total_rows; 

// 3. Bilang ng Archived Admins (status='archived')
$archived_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='archived'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Control Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --super-navy: #000000;
            --sidebar-bg: #000000;
            --accent-gold: #f59e0b;
            --text-light: #f9f9f9;
            --card-bg: #ffffff;
            --success-blue: #000000;
            --danger-red: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; background-color: #f1f5f9; min-height: 100vh; }

        /* Sidebar Design */
        .sidebar { width: 280px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 25px; position: fixed; height: 100%; border-right: 4px solid var(--accent-gold); }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px; text-align: center; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 2px; }
        
        .sidebar-section { margin-bottom: 30px; }
        .sidebar-section h3 { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-bottom: 15px; padding-left: 10px; }
        
        .sidebar a { 
            color: #cbd5e1; text-decoration: none; padding: 12px 15px; margin-bottom: 8px; 
            border-radius: 10px; display: block; transition: 0.3s; font-size: 0.95rem;
        }
        .sidebar a:hover { background: rgba(245, 158, 11, 0.1); color: var(--accent-gold); padding-left: 20px; }
        .sidebar a.active { background: var(--accent-gold); color: var(--super-navy); font-weight: 600; }
        
        .logout-btn { margin-top: auto; background: #ef4444; color: white !important; text-align: center; border-radius: 10px; padding: 12px; transition: 0.3s; text-decoration: none; font-weight: 600; }
        .logout-btn:hover { background: #dc2626 !important; }

        /* Main Content */
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        
        .header-title { margin-bottom: 30px; }
        .header-title h1 { color: var(--super-navy); font-size: 1.8rem; }
        .header-title p { color: #64748b; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid var(--accent-gold); }
        .stat-card h4 { font-size: 0.85rem; color: #64748b; text-transform: uppercase; }
        .stat-card .value { font-size: 2rem; font-weight: 600; color: var(--super-navy); }

        /* Logs Table Design */
        .logs-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .logs-container h3 { margin-bottom: 20px; color: var(--super-navy); display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f8fafc; color: #475569; font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; vertical-align: middle; }
        
        .badge-action { background: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; color: #475569; }
        .timestamp { color: #94a3b8; font-size: 0.8rem; }
        .actor-name { color: var(--accent-gold); font-weight: 600; }
        .description-text { color: #64748b; font-style: italic; font-size: 0.85rem; }

        /* Pagination Controls */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; }
        .page-link { 
            padding: 8px 16px; background: white; color: var(--super-navy); 
            border: 1px solid #e2e8f0; text-decoration: none; border-radius: 8px; 
            font-size: 0.9rem; font-weight: 600; transition: 0.3s;
        }
        .page-link:hover { background: var(--accent-gold); color: var(--super-navy); border-color: var(--accent-gold); }
        .page-link.active { background: var(--super-navy); color: white; border-color: var(--super-navy); }
        .page-link.disabled { color: #cbd5e1; pointer-events: none; background: #f8fafc; border-color: #f1f5f9; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Super Admin</h2>
        
        <div class="sidebar-section">
            <h3>Overview</h3>
            <a href="dashboard.php" class="active">Control Panel</a>
        </div>

        <div class="sidebar-section">
            <h3>Admin Management</h3>
            <a href="add_admin.php">Add New Admin</a>
            <a href="manage_admin.php">Manage Admins</a>
        </div>

        <a href="../logout.php" class="logout-btn">Logout System</a>
    </div>

    <div class="main-content">
        <div class="header-title">
            <h1>System Dashboard</h1>
            <p>Real-time overview of administrative statistics and activity logs.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Active Admins</h4>
                <div class="value"><?php echo $active_admins; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--success-blue);">
                <h4>Total Activity Logs</h4>
                <div class="value"><?php echo $total_logs; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #94a3b8;">
                <h4>Archived Admins</h4>
                <div class="value"><?php echo $archived_admins; ?></div>
            </div>
        </div>

        <div class="logs-container">
            <h3>Recent System Activities</h3>
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Actor (Admin)</th>
                        <th>Action</th>
                        <th>Involved Entity</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="timestamp">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?><br>
                                    <?php echo date('g:i A', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="actor-name">
                                        <?php 
                                        echo $row['admin_fname'] 
                                            ? htmlspecialchars($row['admin_fname'] . " " . $row['admin_lname']) 
                                            : "System/Regular"; 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-action"><?php echo htmlspecialchars($row['action_type']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                    if($row['item_name']) {
                                        echo "<small style='color:#94a3b8'>Item:</small><br><strong>" . htmlspecialchars($row['item_name']) . "</strong>";
                                    } elseif($row['target_fname']) {
                                        echo "<small style='color:#94a3b8'>User:</small><br><strong>" . htmlspecialchars($row['target_fname'] . " " . $row['target_lname']) . "</strong>";
                                    } else {
                                        echo "<span style='color: #cbd5e1;'>N/A</span>";
                                    }
                                    ?>
                                </td>
                                <td class="description-text">
                                    "<?php echo htmlspecialchars($row['description']); ?>"
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">No activity logs found in the database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link <?php echo ($page <= 1) ? 'disabled' : ''; ?>">Previous</a>
                    
                    <?php 
                    for ($i = 1; $i <= $total_pages; $i++): 
                    ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>