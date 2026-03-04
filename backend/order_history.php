<?php
session_start();
include 'config/db.php';

// Only logged-in customers
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for this customer
$orders = $conn->prepare("
    SELECT id, order_date 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
$orders->bind_param("i", $user_id);
$orders->execute();
$result = $orders->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>My Order History</h2>
    <p>Welcome, <?php echo $_SESSION['user_name']; ?> | <a href="products.php">Shop More</a> | <a href="logout.php">Logout</a></p>

    <?php if($result->num_rows > 0) { ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Details</th>
            </tr>
            <?php while($order = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['order_date']; ?></td>
                <td><a href="order_details_customer.php?id=<?php echo $order['id']; ?>">View Items</a></td>
            </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>You have no orders yet. <a href="products.php">Shop now!</a></p>
    <?php } ?>
</body>
</html>

