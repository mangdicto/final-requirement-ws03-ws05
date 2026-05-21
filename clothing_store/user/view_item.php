<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('regular');

// Kinukuha ang category mula sa URL para sa filtering
$category_filter = isset($_GET['cat']) ? $_GET['cat'] : 'All';
$filter_sql = "SELECT * FROM items WHERE status='approved'";

// Logic para sa Category Filter Buttons
if($category_filter !== 'All') {
    $filter_sql .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}

if(isset($_GET['search'])){
    $search = "%".$_GET['search']."%";
    $stmt = $conn->prepare($filter_sql." AND (item_name LIKE ? OR category LIKE ? OR size LIKE ? OR color LIKE ?)");
    $stmt->bind_param("ssss",$search,$search,$search,$search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($filter_sql);
}

// Listahan ng Categories para sa Buttons
$categories = ["All", "T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Skirt", "Shoes", "Hat", "Accessories"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items - Clothing Store</title>
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

        .nav-right { flex: 0 1 300px; }
        .search-container { position: relative; width: 100%; }
        .search-container input {
            width: 100%;
            padding: 7px 12px 7px 35px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #f1f5f9;
            font-size: 0.75rem;
            outline: none;
        }
        .search-container i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-slate); font-size: 0.75rem; }

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
        .page-header { margin-bottom: 15px; }
        .page-header h2 { font-size: 1.15rem; color: var(--primary-navy); }
        .page-header p { font-size: 0.75rem; color: var(--text-slate); }

        /* Category Filter Pills */
        .category-nav {
            display: flex; gap: 6px; margin-bottom: 20px;
            overflow-x: auto; padding-bottom: 5px; scrollbar-width: none;
        }
        .cat-btn { 
            text-decoration: none; padding: 5px 12px; background: white; color: var(--text-slate); 
            border-radius: 12px; font-size: 0.7rem; border: 1px solid #e2e8f0; transition: 0.2s;
            white-space: nowrap;
        }
        .cat-btn:hover, .cat-btn.active { background: var(--primary-navy); color: white; border-color: var(--primary-navy); }

        /* FIXED 4 COLUMNS GRID */
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 12px; 
        }
        
        .card { 
            background: white; border-radius: 8px; overflow: hidden; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: 0.2s; 
            border: 1px solid #f1f5f9; display: flex; flex-direction: column;
        }
        .card:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(0,0,0,0.08); }
        
        .img-container { width: 100%; height: 180px; overflow: hidden; background: #f8fafc; position: relative; }
        .card img { width: 100%; height: 100%; object-fit: cover; }

        .card-body { padding: 10px; flex-grow: 1; }
        .card-category { font-size: 0.6rem; text-transform: uppercase; color: var(--accent-blue); font-weight: 700; }
        .card-title { 
            font-size: 0.8rem; color: var(--primary-navy); margin: 2px 0; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .card-price { font-size: 0.85rem; font-weight: 700; color: var(--primary-navy); margin-bottom: 8px; }
        
        .card-footer { 
            display: flex; justify-content: space-between; 
            padding-top: 8px; border-top: 1px solid #f1f5f9;
            font-size: 0.65rem; color: var(--text-slate);
        }
        .badge { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-weight: 600; color: var(--primary-navy); }

        .no-results { grid-column: 1 / -1; text-align: center; padding: 40px; font-size: 0.8rem; color: var(--text-slate); }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <nav class="top-nav">
        <div class="nav-left">
            <i class="fas fa-bars hamburger" id="menuBtn"></i>
            <a href="dashboard.php" class="brand">ClothingStore</a>
        </div>

        <div class="nav-right">
            <form method="GET" class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search collection..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="add_item.php"><i class="fas fa-plus-circle"></i> Add Item</a>
        <a href="pending_items.php"><i class="fas fa-clock"></i> Pending</a>
        <a href="approved_items.php"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="view_item.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-th-large"></i> View All</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Available Collection</h2>
            <p>Discover premium styles curated for you.</p>
        </div>

        <div class="category-nav">
            <?php foreach($categories as $cat): ?>
                <a href="view_item.php?cat=<?php echo $cat; ?>" 
                   class="cat-btn <?php echo ($category_filter == $cat) ? 'active' : ''; ?>">
                   <?php echo $cat; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="product-grid">
            <?php
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="card">
                        <div class="img-container">
                            <img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Product">
                        </div>
                        <div class="card-body">
                            <span class="card-category"><?php echo htmlspecialchars($row['category']); ?></span>
                            <h3 class="card-title" title="<?php echo htmlspecialchars($row['item_name']); ?>">
                                <?php echo htmlspecialchars($row['item_name']); ?>
                            </h3>
                            <p class="card-price">₱<?php echo number_format($row['price'], 2); ?></p>
                            
                            <div class="card-footer">
                                <span>Size: <span class="badge"><?php echo htmlspecialchars($row['size']); ?></span></span>
                                <span>Stock: <span class="badge"><?php echo $row['quantity']; ?></span></span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='no-results'>
                        <i class='fas fa-box-open' style='font-size: 2rem; margin-bottom: 10px; opacity: 0.2;'></i>
                        <p>No items found.</p>
                      </div>";
            }
            ?>
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