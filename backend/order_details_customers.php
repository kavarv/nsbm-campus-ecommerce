<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: order_history.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch order info (verify it belongs to this customer)
$stmt = $conn->prepare("
    SELECT order_date 
    FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$stmt->bind_result($order_date);
if(!$stmt->fetch()) {
    $stmt->close();
    header("Location: order_history.php");
    exit;
}
$stmt->close();

// Fetch order items
$items = $conn->prepare("
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$items->bind_param("i", $order_id);
$items->execute();
$result = $items->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Order Details</h2>
    <p>Order ID: <?php echo $order_id; ?> | Date: <?php echo $order_date; ?></p>
    <p><a href="order_history.php">Back to My Orders</a></p>

    <?php if($result->num_rows > 0) { ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
            <?php $grand_total = 0; ?>
            <?php while($row = $result->fetch_assoc()) { 
                $total = $row['quantity'] * $row['price'];
                $grand_total += $total;
            ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>$<?php echo $row['price']; ?></td>
                <td>$<?php echo $total; ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="3" align="right"><strong>Grand Total:</strong></td>
                <td><strong>$<?php echo $grand_total; ?></strong></td>
            </tr>
        </table>
    <?php } else { ?>
        <p>No items found in this order.</p>
    <?php } ?>
</body>
</html>

