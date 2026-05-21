<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('regular');

$user_id = $_SESSION['user_id'];
// Kuhanin lang ang items na PENDING at sa user na ito lang
$result = $conn->query("SELECT * FROM items WHERE added_by = $user_id AND status = 'pending' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pending Items - Clothing Store</title>
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
        .page-header { margin-bottom: 20px; border-left: 4px solid var(--pending-orange); padding-left: 12px; }
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
        .card:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(0,0,0,0.08); }

        .status-badge { 
            position: absolute; top: 10px; right: 10px; 
            background: var(--pending-orange); color: white; 
            padding: 3px 8px; border-radius: 4px; 
            font-size: 0.6rem; font-weight: 700; z-index: 10;
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.2);
        }

        .img-container { width: 100%; height: 180px; background: #f8fafc; overflow: hidden; }
        .card img { 
            width: 100%; height: 100%; object-fit: cover; 
            opacity: 0.85; transition: 0.3s;
        }
        .card:hover img { opacity: 1; scale: 1.05; }

        .card-body { padding: 10px; }
        .card-title { 
            font-size: 0.8rem; color: var(--primary-navy); margin-bottom: 4px; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .card-price { font-size: 0.85rem; font-weight: 700; color: var(--primary-navy); }
        
        .card-meta { 
            margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-slate);
        }

        .no-data { 
            grid-column: 1/-1; text-align: center; padding: 40px 20px; 
            background: white; border-radius: 12px; border: 1.5px dashed #e2e8f0;
        }
        .no-data i { font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; }
        .no-data p { font-size: 0.8rem; color: var(--text-slate); }
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
        <a href="pending_items.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-clock"></i> Pending</a>
        <a href="approved_items.php"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>My Pending Items</h2>
            <p>Waiting for admin review and approval.</p>
        </div>

        <div class="product-grid">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <span class="status-badge"><i class="fas fa-hourglass-half"></i> PENDING</span>
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
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No pending items.</h3>
                    <p>When you add an item, it will appear here during the review process.</p>
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