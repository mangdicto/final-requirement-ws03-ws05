<?php
require '../config/database.php';
require '../functions/auth.php';
checkLogin();
checkRole('admin');

$result = $conn->query("SELECT * FROM items WHERE status='pending'");
?>

<h2>Pending Items for Approval</h2>

<table border="1" cellpadding="5">
<tr>
    <th>Photo</th>
    <th>Item Name</th>
    <th>Category</th>
    <th>Size</th>
    <th>Color</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Actions</th>
</tr>

<?php
while($row = $result->fetch_assoc()) {
    $id = base64_encode($row['id']);
    echo "<tr>";
    echo "<td><img src='../uploads/" . htmlspecialchars($row['photo']) . "' width='60'></td>";
    echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td>" . htmlspecialchars($row['size']) . "</td>";
    echo "<td>" . htmlspecialchars($row['color']) . "</td>";
    echo "<td>" . $row['price'] . "</td>";
    echo "<td>" . $row['quantity'] . "</td>";
    echo "<td>";
    echo "<a href='approve_action.php?id=$id'>Approve</a> | ";
    echo "<a href='reject_action.php?id=$id' style='color:red;'>Reject</a>";
    echo "</td>";
    echo "</tr>";
}
?>
</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>