<?php 
require '../config/database.php'; 
require '../functions/auth.php'; 
require '../functions/csrf.php';
checkLogin(); 
checkRole('regular'); 

$categories = ["T-Shirt", "Shirt", "Pants", "Jeans", "Jacket", "Dress", "Skirt", "Shoes", "Hat", "Accessories"]; 
$message = ""; 

if($_SERVER["REQUEST_METHOD"]=="POST"){ 
    // Verify CSRF token
    verifyCSRFToken($_POST['csrf_token'] ?? '');
    
    // Negative number validation
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    if($price < 0){
        $message = "<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Error: Price cannot be negative!</div>";
    } elseif($quantity < 0){
        $message = "<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Error: Quantity cannot be negative!</div>";
    } else {
        $photoName = time() . "_" . $_FILES['photo']['name']; 
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName); 

        $stmt = $conn->prepare("INSERT INTO items (item_name,category,size,color,price,quantity,photo,added_by,status) VALUES (?,?,?,?,?,?,?,?,'pending')"); 
        $stmt->bind_param("sssssisi", 
            $_POST['item_name'], 
            $_POST['category'], 
            $_POST['size'], 
            $_POST['color'], 
            $price, 
            $quantity, 
            $photoName, 
            $_SESSION['user_id'] 
        ); 

        if($stmt->execute()){
            $item_id = $conn->insert_id; 
            $user_id = $_SESSION['user_id'];
            $item_name = $_POST['item_name'];
            $action_type = "Submit Item (Pending)";
            $description = "Regular user submitted a new item for approval: $item_name";

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, item_id, action_type, description) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("iiss", $user_id, $item_id, $action_type, $description);
            $log_stmt->execute();

            $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Item Submitted Successfully!</div>"; 
        } else {
            $message = "<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Error: " . $conn->error . "</div>";
        }
    }
} 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Clothing Store</title>
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
        .sidebar a:hover, .sidebar a.active{ background: #f59e0b; color: white; }
        .logout-btn { margin-top: auto; background: #ef4444; font-size: 0.8rem !important; justify-content: center; }

        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: none; z-index: 1000; }
        .overlay.active { display: block; }

        .main-content { padding: 20px; max-width: 550px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; }
        
        .form-card {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); width: 100%; border: 1px solid #f1f5f9;
        }

        .form-card h2 { color: var(--primary-navy); margin-bottom: 5px; text-align: center; font-size: 1.2rem; }
        .form-card p { color: var(--text-slate); text-align: center; margin-bottom: 20px; font-size: 0.75rem; }

        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--primary-navy); margin-bottom: 5px; }
        
        .form-group input, .form-group select {
            width: 100%; padding: 8px 12px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            background: #f8fafc; font-size: 0.85rem; transition: 0.2s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: var(--accent-blue); background: white;
        }

        .submit-btn {
            width: 100%; padding: 12px; background: var(--primary-black); color: white;
            border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .submit-btn:hover { background: #f59e0b; }

        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; width: 100%; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        .back-link { margin-top: 15px; color: var(--text-slate); text-decoration: none; font-size: 0.75rem; transition: 0.3s; }
        .back-link:hover { color: var(--accent-blue); }
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
        <a href="add_item.php" style="background: rgba(255,255,255,0.1); color: white;"><i class="fas fa-plus-circle"></i> Add Item</a>
        <a href="pending_items.php"><i class="fas fa-clock"></i> Pending</a>
        <a href="approved_items.php"><i class="fas fa-check-circle"></i> Approved</a>
        <a href="rejected_items.php"><i class="fas fa-times-circle"></i> Rejected</a>
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <?php echo $message; ?>

        <div class="form-card">
            <h2>Add New Item</h2> 
            <p>Fill in details for admin approval.</p>

            <form method="POST" enctype="multipart/form-data"> 
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Item Name</label>
                    <input type="text" name="item_name" placeholder="e.g. Vintage Jacket" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Category</label>
                    <select name="category" required> 
                        <option value="">-- Select --</option> 
                        <?php foreach($categories as $cat){ 
                            echo "<option value='".htmlspecialchars($cat)."'>".htmlspecialchars($cat)."</option>"; 
                        } ?> 
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-ruler-combined"></i> Size</label>
                        <input type="text" name="size" placeholder="M, L, XL" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-palette"></i> Color</label>
                        <input type="text" name="color" placeholder="Blue, Black" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-coins"></i> Price (₱)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" min="0" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-boxes"></i> Qty</label>
                        <input type="number" name="quantity" placeholder="1" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Photo</label>
                    <input type="file" name="photo" accept="image/*" required style="padding: 5px; font-size: 0.75rem; background: none; border: 1px dashed #cbd5e1;">
                </div>
                
                <button type="submit" class="submit-btn">Submit for Approval</button> 
            </form> 
        </div>

        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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