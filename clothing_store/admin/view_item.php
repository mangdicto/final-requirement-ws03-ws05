<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

// Listahan ng categories para sa dropdown
$categories = ["T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Skirt", "Shoes", "Hat", "Accessories"];

// Idinagdag ang "AND status='approved'" para siguradong approved lang ang lalabas
$filter_sql = "SELECT * FROM items WHERE status='approved'"; 

// Handle search/filter
if(isset($_GET['search'])){
    $search = "%".$_GET['search']."%";
    $filter_sql .= " AND (item_name LIKE ? OR category LIKE ? OR size LIKE ? OR color LIKE ?)";
    $stmt = $conn->prepare($filter_sql);
    $stmt->bind_param("ssss",$search,$search,$search,$search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($filter_sql);
}

// Para sa Archived Items table sa baba
$archived_result = $conn->query("SELECT * FROM items WHERE status='archived' ORDER BY created_at DESC");

// Para sa sidebar badge count
$pending_count_query = $conn->query("SELECT COUNT(*) as total FROM items WHERE status='pending'");
$total_pending = $pending_count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #f59e0b;
            --bg-color: #f1f5f9;
            --success: #10b981;
            --danger: #ef4444;
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
        
        h2 { color: var(--primary-color); margin-bottom: 20px; }

        /* Search Bar */
        .search-container { margin-bottom: 25px; background: white; padding: 15px; border-radius: 10px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .search-container input { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; }
        .search-container button { padding: 10px 20px; background: var(--accent-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.75rem; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; vertical-align: middle; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }

        /* Buttons */
        .btn-update { background: #dcfce7; color: #166534; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.8rem; }
        .btn-archive { color: var(--danger); text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-left: 10px; }
        .btn-restore { color: var(--success); text-decoration: none; font-weight: 600; font-size: 0.8rem; }

        /* Modal Styling */
        .modal-content { background: white; width: 450px; margin: 50px auto; padding: 30px; border-radius: 15px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative; }
        .modal-content h3 { margin-bottom: 20px; color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .modal-content label { display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 10px; }
        .modal-content input, .modal-content select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .modal-footer { margin-top: 25px; display: flex; gap: 10px; }
        .btn-save { background: var(--success); color: white; flex: 1; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-cancel { background: #94a3b8; color: white; flex: 1; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <h3>Items Management</h3>
        <a href="add_item.php">Add Item</a>
        <a href="view_item.php" class="active">View Items</a>
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
        <h2>View Items (Live Inventory)</h2>

        <form method="GET" class="search-container">
            <input type="text" name="search" placeholder="Search by Name / Category / Size / Color..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>

        <div class="section-card">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Size/Color</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $id = base64_encode($row['id']);
                            ?>
                            <tr>
                                <td><img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="item-img"></td>
                                <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['size']); ?> / <?php echo htmlspecialchars($row['color']); ?></td>
                                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><span style="color:var(--success); font-weight:600; font-size:0.75rem;"><?php echo strtoupper($row['status']); ?></span></td>
                                <td>
                                    <?php if($row['status'] != 'archived'): ?>
                                        <button class="btn-update" onclick='openModal(<?php echo json_encode($row); ?>)'>Update</button>
                                        <a href="archive_action.php?id=<?php echo $id; ?>&action=archive" class="btn-archive" onclick="return confirm('Archive this item?')">Archive</a>
                                    <?php else: ?>
                                        <a href="archive_action.php?id=<?php echo $id; ?>&action=restore" class="btn-restore">Restore</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else { echo "<tr><td colspan='8' align='center'>No items found.</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="section-card">
            <h2 style="color: #64748b;">Archived Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($archived_result && $archived_result->num_rows > 0) {
                        while($row = $archived_result->fetch_assoc()) {
                            $id = base64_encode($row['id']);
                            ?>
                            <tr>
                                <td><img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="item-img" style="filter: grayscale(1);"></td>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><span style="color:var(--danger); font-weight:600; font-size:0.75rem;">ARCHIVED</span></td>
                                <td>
                                    <a href="archive_action.php?id=<?php echo $id; ?>&action=restore" class="btn-restore">Restore</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else { echo "<tr><td colspan='5' align='center'>No archived items found.</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
        
        <a href="dashboard.php" style="text-decoration:none; color:var(--accent-color); font-size:0.9rem;">← Back to Dashboard</a>
    </div>

    <div id="updateModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999; overflow-y: auto;">
        <div class="modal-content">
            <h3>Update Item Details</h3>
            <form action="update_item_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="modal_id">
                
                <label>Item Name</label>
                <input type="text" name="item_name" id="modal_name" required>
                
                <label>Category</label>
                <select name="category" id="modal_category" required>
                    <?php foreach($categories as $cat){
                        echo "<option value='".htmlspecialchars($cat)."'>".htmlspecialchars($cat)."</option>";
                    } ?>
                </select>

                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Size</label>
                        <input type="text" name="size" id="modal_size" required>
                    </div>
                    <div style="flex:1;">
                        <label>Color</label>
                        <input type="text" name="color" id="modal_color" required>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" id="modal_price" required>
                    </div>
                    <div style="flex:1;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="modal_quantity" required>
                    </div>
                </div>
                
                <label>Update Photo (Optional)</label>
                <input type="file" name="photo" accept="image/*">

                <div class="modal-footer">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(item) {
        document.getElementById('modal_id').value = item.id;
        document.getElementById('modal_name').value = item.item_name;
        document.getElementById('modal_category').value = item.category;
        document.getElementById('modal_size').value = item.size;
        document.getElementById('modal_color').value = item.color;
        document.getElementById('modal_price').value = item.price;
        document.getElementById('modal_quantity').value = item.quantity;
        document.getElementById('updateModal').style.display = 'block';
    }
    function closeModal() {
        document.getElementById('updateModal').style.display = 'none';
    }
    </script>
</body>
</html>