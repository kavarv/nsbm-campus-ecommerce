<?php
session_start();
include '../config/db.php';

/* ===== ADMIN PROTECTION ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ===== CHECK ORDER ID ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: all_orders.php");
    exit;
}

$order_id = (int) $_GET['id'];


/* ===== FETCH ORDER INFO ===== */
$stmt = $conn->prepare("
    SELECT o.order_date, o.total_amount, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($order_date, $total_amount, $customer_name);

if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: all_orders.php");
    exit;
}
$stmt->close();


/* ===== FETCH ORDER ITEMS ===== */
$items_stmt = $conn->prepare("
    SELECT p.name, oi.quantity, oi.price, oi.size
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$result = $items_stmt->get_result();

$grand_total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>


<?php include 'admin_layout.php'; ?>


<!-- MAIN CONTENT -->
<div class="admin-container">

    <h2>Order Details</h2>

    <!-- ORDER SUMMARY -->
    <div class="order-summary">
        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>

        <p><strong>Customer:</strong>
            <?php echo htmlspecialchars($customer_name); ?>
        </p>

        <p><strong>Date:</strong>
            <?php echo date("d M Y, h:i A", strtotime($order_date)); ?>
        </p>
    </div>


    <!-- ITEMS TABLE -->
    <?php if ($result->num_rows > 0) { ?>

        <table class="admin-table">

            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price (Rs)</th>
                <th>Total (Rs)</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) {

                $total = $row['quantity'] * $row['price'];
                $grand_total += $total;
            ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>

                    <td>
                        <?php echo $row['size'] ? htmlspecialchars($row['size']) : '—'; ?>
                    </td>

                    <td><?php echo $row['quantity']; ?></td>

                    <td>Rs <?php echo number_format($row['price'], 2); ?></td>

                    <td>Rs <?php echo number_format($total, 2); ?></td>
                </tr>

            <?php } ?>


            <!-- GRAND TOTAL -->
            <tr class="grand-total">
                <td colspan="4">Grand Total</td>
                <td>Rs <?php echo number_format($grand_total, 2); ?></td>
            </tr>

        </table>

    <?php } else { ?>

        <p class="welcome-text">No items found for this order.</p>

    <?php } ?>


    <!-- BACK BUTTON -->
    <a href="all_orders.php" class="back-btn">← Back to Orders</a>

</div>


<!-- FOOTER -->
<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>
<?php include 'admin_layout_close.php'; ?>

</body>
</html>