<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

// Pending items submitted by regular users
$pending_result = $conn->query("SELECT * FROM items WHERE status='pending' ORDER BY created_at DESC");

// Rejected items submitted by regular users
$rejected_result = $conn->query("SELECT * FROM items WHERE status='rejected' ORDER BY created_at DESC");

// Kuhanin ang count para sa sidebar badge
$total_pending = $pending_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Approvals - Clothing Store Admin</title>
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
        
        h2 { color: var(--primary-color); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        h2.rejected-title { color: #64748b; margin-top: 20px; }

        /* Table Styling */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.75rem; font-weight: 600; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; color: #334155; vertical-align: middle; }
        
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        
        /* Action Buttons */
        .btn { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; display: inline-block; }
        .btn-approve { background-color: #d1fae5; color: #065f46; margin-right: 5px; }
        .btn-approve:hover { background-color: var(--success); color: white; }
        .btn-reject { background-color: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background-color: var(--danger); color: white; }
        
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
        <a href="pending_items.php" class="active">
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
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert-success">
                Action processed successfully!
            </div>
        <?php endif; ?>

        <div class="section-card">
            <h2>Pending Items for Approval</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Size/Color</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Submitted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($pending_result && $pending_result->num_rows > 0) {
                            while($row = $pending_result->fetch_assoc()) {
                                $id = base64_encode($row['id']);

                                $user_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id=?");
                                $user_stmt->bind_param("i",$row['added_by']);
                                $user_stmt->execute();
                                $user_res = $user_stmt->get_result();
                                $user = $user_res->fetch_assoc();
                                $fullname = ($user) ? $user['first_name'] . " " . $user['last_name'] : "Unknown User";
                                ?>
                                <tr>
                                    <td><img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="item-img"></td>
                                    <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($row['size']); ?> / <?php echo htmlspecialchars($row['color']); ?></td>
                                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><small><?php echo htmlspecialchars($fullname); ?></small></td>
                                    <td>
                                        <a href="approve_action.php?action=approve&id=<?php echo $id; ?>" class="btn btn-approve">Approve</a>
                                        <a href="approve_action.php?action=reject&id=<?php echo $id; ?>" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this item?')">Reject</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' align='center'>No pending items found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <h2 class="rejected-title">Rejected Items History</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Submitted By</th>
                            <th>Status Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($rejected_result && $rejected_result->num_rows > 0) {
                            while($row = $rejected_result->fetch_assoc()) {
                                $user_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id=?");
                                $user_stmt->bind_param("i",$row['added_by']);
                                $user_stmt->execute();
                                $user_res = $user_stmt->get_result();
                                $user = $user_res->fetch_assoc();
                                $fullname = ($user) ? $user['first_name'] . " " . $user['last_name'] : "Unknown User";
                                ?>
                                <tr>
                                    <td><img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="item-img" style="filter: grayscale(1);"></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($fullname); ?></td>
                                    <td><span style="color: var(--danger); font-weight: 600;">Rejected</span></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' align='center'>No rejected items found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="dashboard.php" style="text-decoration: none; color: var(--accent-color); font-size: 0.9rem;">← Back to Admin Dashboard</a>
    </div>

</body>
</html>