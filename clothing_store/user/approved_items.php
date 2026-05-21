<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('regular');

        $user_id = $_SESSION['user_id'];
        $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';

        $sql = "SELECT * FROM items WHERE status='approved'";
        if(!empty($search)){
            $sql .= " AND (item_name LIKE ? OR category LIKE ? OR color LIKE ?)";
        }
        if(!empty($category)){
            $sql .= " AND category = ?";
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $conn->prepare($sql);

        $params = [];
        $types = "";

        if(!empty($search) && !empty($category)){
            $stmt->bind_param("ssss", $search, $search, $search, $category);
        } elseif(!empty($search)){
            $stmt->bind_param("sss", $search, $search, $search);
        } elseif(!empty($category)){
            $stmt->bind_param("s", $category);
        }

        if(!empty($search) || !empty($category)){
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Approved Items - Clothing Store</title>
     <div class="search-bar" style="margin-bottom: 20px;">
    <form method="GET" action="">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search by item name, category, or color..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
            <select name="category" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                <option value="">All Categories</option>
                <?php 
                $categories = ["T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Skirt", "Shoes", "Hat", "Accessories"];
                foreach($categories as $cat){
                    $selected = (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($cat)."' $selected>".htmlspecialchars($cat)."</option>";
                }
                ?>
            </select>
            <button type="submit" style="padding: 10px 20px; background: #000; color: white; border: none; border-radius: 8px; cursor: pointer;">Search</button>
            <a href="approved_items.php" style="padding: 10px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 8px;">Reset</a>
        </div>
    </form>
</div>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
        --primary-black: #000000;
        --sidebar-bg: #121212;    
        --accent-gray: #757575;    
        --text-main: #111111;       
        --text-muted: #666666;     
        --bg-light: #f5f5f5;       
        --border-color: #dddddd;   
    }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-light); min-height: 100vh; padding-top: 60px; }

        /* Top Navigation - Compact */
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
        .hamburger { font-size: 1.1rem; cursor: pointer; color: var(--primary-navy); }
        .brand { font-size: 1rem; font-weight: 700; color: var(--primary-navy); text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; }

        /* Sidebar - Narrower */
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

        /* Main Content */
        .main-content { padding: 15px; max-width: 1050px; margin: 0 auto; }
        .page-header { margin-bottom: 20px; border-left: 4px solid var(--success-green); padding-left: 12px; }
        .page-header h2 { font-size: 1.15rem; color: var(--primary-navy); }
        .page-header p { font-size: 0.75rem; color: var(--text-slate); }
        
        /* FIXED 4 COLUMNS GRID */
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 12px; 
        }

        .card { 
            background: white; border-radius: 8px; overflow: hidden; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative;
            border: 1px solid #f1f5f9; transition: 0.2s;
        }
        .card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }

        .status-badge { 
            position: absolute; top: 10px; right: 10px; 
            background: var(--success-green); color: white; 
            padding: 3px 8px; border-radius: 4px; 
            font-size: 0.6rem; font-weight: 700; z-index: 10;
        }

        .img-container { width: 100%; height: 180px; background: #f8fafc; overflow: hidden; }
        .card img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .card:hover img { scale: 1.05; }

        .card-body { padding: 12px; }
        .card-title { 
            font-size: 0.8rem; color: var(--primary-navy); margin-bottom: 4px; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .card-price { font-size: 0.85rem; font-weight: 700; color: var(--success-green); }
        
        .card-meta { 
            margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-slate);
        }

        .no-data { 
            grid-column: 1/-1; text-align: center; padding: 40px 20px; 
            background: white; border-radius: 12px; border: 1.5px dashed #e2e8f0;
        }
        .no-data i { font-size: 2.5rem; color: var(--success-green); margin-bottom: 12px; opacity: 0.3; }
        .no-data h3 { font-size: 1rem; color: var(--primary-navy); }
        .no-data p { font-size: 0.75rem; color: var(--text-slate); }
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
        <a href="approved_items.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>My Approved Items</h2>
            <p>These items are live and visible in the shop catalog.</p>
        </div>

        <div class="product-grid">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <span class="status-badge"><i class="fas fa-check"></i> LIVE</span>
                        <div class="img-container">
                            <img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Product">
                        </div>
                        <div class="card-body">
                            <h3 class="card-title" title="<?php echo htmlspecialchars($row['item_name']); ?>">
                                <?php echo htmlspecialchars($row['item_name']); ?>
                            </h3>
                            <p class="card-price">₱<?php echo number_format($row['price'], 2); ?></p>
                            
                            <div class="card-meta">
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['category']); ?></span>
                                <span><i class="fas fa-layer-group"></i> Qty: <?php echo $row['quantity']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-check-double"></i>
                    <h3>No approved items yet.</h3>
                    <p>Once the admin approves your submissions, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
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