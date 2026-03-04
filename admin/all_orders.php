<?php
session_start();
include '../config/db.php';

/* ===== ADMIN PROTECTION ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ===== FETCH ORDERS ===== */
$orders = $conn->query("
    SELECT o.id, o.total_amount, o.order_date , u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Orders</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_layout.php'; ?>



<!-- MAIN CONTENT -->
<div class="admin-container">

    <h2>All Orders</h2>

    <?php if ($orders->num_rows > 0) { ?>

        <table class="admin-table">

            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total (Rs)</th>
                <th>Date</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $orders->fetch_assoc()) { ?>

                <tr>
                    <td>#<?php echo $row['id']; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['customer_name']); ?>
                    </td>

                    <td>
                        Rs <?php echo number_format($row['total_amount'], 2); ?>
                    </td>

                    <td>
                        <?php echo date("d M Y, h:i A", strtotime($row['order_date'])); ?>
                    </td>

                    <td>
                        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="view-btn">
                            View
                        </a>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p class="welcome-text">No orders found.</p>

    <?php } ?>

</div>


<!-- FOOTER -->
<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>
<?php include 'admin_layout_close.php'; ?>

</body>
</html>
