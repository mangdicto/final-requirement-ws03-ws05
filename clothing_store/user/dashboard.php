<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('regular');

$user_id = $_SESSION['user_id'];

// 1. Kuhanin ang pangalan ng User
$user_query = $conn->query("SELECT first_name FROM users WHERE id = $user_id");
$user_data = $user_query->fetch_assoc();
$user_name = $user_data['first_name'];

// 2. LOGIC PARA SA SEARCH AT CATEGORY FILTER
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

$sql = "SELECT * FROM items WHERE status = 'approved'";
if ($search !== '') {
    $sql .= " AND (item_name LIKE '%$search%' OR category LIKE '%$search%')";
}
if ($category !== '') {
    $sql .= " AND category = '$category'";
}
$sql .= " ORDER BY id DESC";
$items_query = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clothing Store</title>
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
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }

        .nav-left { display: flex; align-items: center; gap: 15px; }
        .hamburger { font-size: 1.2rem; cursor: pointer; color: var(--primary-navy); }
        .brand { font-size: 1.1rem; font-weight: 700; color: var(--primary-navy); text-transform: uppercase; letter-spacing: 1px; text-decoration: none; }

        .nav-right { flex: 0 1 350px; }
        .search-container { position: relative; width: 100%; }
        .search-container input {
            width: 100%;
            padding: 8px 12px 8px 35px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #f1f5f9;
            font-size: 0.8rem;
            outline: none;
        }
        .search-container i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-slate); font-size: 0.8rem; }

        /* Sidebar - Narrower */
        .sidebar {
            position: fixed;
            top: 0; left: -250px;
            width: 250px; height: 100%;
            background: var(--sidebar-bg); color: white;
            transition: 0.3s ease;
            z-index: 1001; padding: 25px 15px;
            display: flex; flex-direction: column;
        }
        .sidebar.active { left: 0; }
        .sidebar h2 { margin-bottom: 20px; font-size: 1.1rem; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar a {
            color: #cbd5e1; text-decoration: none; padding: 10px 12px;
            border-radius: 8px; margin-bottom: 4px; display: flex;
            align-items: center; gap: 10px; font-size: 0.85rem;
        }
        .sidebar a:hover, .sidebar a.active { background: #f59e0b; color: white; }
        .logout-btn { margin-top: auto; background: #ef4444; font-size: 0.85rem !important; justify-content: center; }

        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.3); display: none; z-index: 1000;
        }
        .overlay.active { display: block; }

        /* Main Content - Narrowed down */
        .main-content { padding: 20px; max-width: 1180px; margin: 0 auto; }
        .welcome-header { margin-bottom: 15px; }
        .welcome-header h2 { font-size: 1.3rem; color: var(--primary-navy); }
        .welcome-header p { font-size: 0.8rem; }
        
        /* Smaller Category Pills */
        .category-filters {
            display: flex; gap: 8px; margin-bottom: 25px;
            overflow-x: auto; padding-bottom: 5px;
        }
        .filter-pill {
            padding: 6px 15px; background: white; border: 1px solid #e2e8f0;
            border-radius: 15px; text-decoration: none; color: var(--text-slate);
            font-size: 0.75rem; transition: 0.2s;
        }
        .filter-pill.active { background: var(--primary-navy); color: white; border-color: var(--primary-navy); }

        /* FIXED 4 COLUMNS GRID */
        .items-grid {
            display: grid; 
            grid-template-columns: repeat(4, 1fr); /* Ito ang nagpilit sa 4 columns */
            gap: 15px;
        }
        
        .item-card {
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04); transition: 0.3s;
            border: 1px solid #f1f5f9;
        }
        .item-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.06); }
        
        /* Smaller Image Box */
        .item-img-box { width: 100%; height: 230px; background: #f8fafc; overflow: hidden; }
        .item-img-box img { width: 100%; height: 100%; object-fit: cover; }

        .item-info { padding: 12px; }
        .item-cat { font-size: 0.65rem; color: var(--accent-blue); font-weight: 700; text-transform: uppercase; }
        .item-name { 
            font-size: 0.85rem; color: var(--primary-navy); font-weight: 600; margin: 2px 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
        }
        .item-price { font-size: 0.95rem; font-weight: 700; color: var(--primary-navy); }
        
        .empty-state {
            grid-column: 1/-1; text-align: center; padding: 60px 20px; font-size: 0.85rem;
            color: var(--text-slate); background: white; border-radius: 15px;
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
        <div class="nav-right">
            <form action="dashboard.php" method="GET" class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Home</a>
        <a href="add_item.php"><i class="fas fa-plus-circle"></i> Add Item</a>
        <a href="pending_items.php"><i class="fas fa-clock"></i> Pending</a>
        <a href="approved_items.php"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-header">
            <h2>Hello, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
            <p style="color: var(--text-slate);">Latest approved items in the shop.</p>
        </div>

        <div class="category-filters">
            <a href="dashboard.php" class="filter-pill <?php echo $category == '' ? 'active' : ''; ?>">All</a>
            <?php 
            $cats = ["T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Shoes", "Accessories"];
            foreach($cats as $c): ?>
                <a href="dashboard.php?category=<?php echo $c; ?>" class="filter-pill <?php echo $category == $c ? 'active' : ''; ?>">
                    <?php echo $c; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="items-grid">
            <?php if ($items_query && $items_query->num_rows > 0): ?>
                <?php while($item = $items_query->fetch_assoc()): ?>
                    <div class="item-card">
                        <div class="item-img-box">
                            <img src="../uploads/<?php echo htmlspecialchars($item['photo']); ?>" alt="Product">
                        </div>
                        <div class="item-info">
                            <span class="item-cat"><?php echo htmlspecialchars($item['category']); ?></span>
                            <h3 class="item-name" title="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </h3>
                            <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No items found.</p>
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