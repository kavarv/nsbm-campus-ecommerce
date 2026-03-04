<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$product_count = $conn->query("SELECT COUNT(*) AS total FROM products")
                      ->fetch_assoc()['total'];


$order_count = $conn->query("SELECT COUNT(*) AS total FROM orders")
                    ->fetch_assoc()['total'];

$revenue = $conn->query("SELECT SUM(total_amount) AS total FROM orders")
                ->fetch_assoc()['total'];

$revenue = $revenue ? $revenue : 0;



$recent_orders = $conn->query("
    SELECT o.id, u.name, o.order_date , o.total_amount
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_layout.php'; ?>



<div class="admin-container">

    <h2>Dashboard</h2>

    <p class="welcome-text">
        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
    </p>


    
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:40px;">

        
        <div style="background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <p style="color:#777; font-size:14px;">Total Products</p>
            <h3 style="font-size:28px; margin-top:5px;"><?php echo $product_count; ?></h3>
        </div>

        
        <div style="background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <p style="color:#777; font-size:14px;">Total Orders</p>
            <h3 style="font-size:28px; margin-top:5px;"><?php echo $order_count; ?></h3>
        </div>

        
        <div style="background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <p style="color:#777; font-size:14px;">Total Revenue</p>
            <h3 style="font-size:28px; margin-top:5px;">
                Rs <?php echo number_format($revenue, 2); ?>
            </h3>
        </div>

    </div>


    <a href="add_product.php" class="add-btn">+ Add New Product</a>


    <h2 style="margin-top:20px;">Recent Orders</h2>

    <?php if ($recent_orders->num_rows > 0) { ?>

        <table class="admin-table">

            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Action</th>
            </tr>

            <?php while ($order = $recent_orders->fetch_assoc()) { ?>

                <tr>
                    <td>#<?php echo $order['id']; ?></td>

                    <td><?php echo htmlspecialchars($order['name']); ?></td>

                    <td>
                        <?php echo date("d M Y", strtotime($order['order_date'])); ?>
                    </td>

                    <td>
                        Rs <?php echo number_format($order['total_amount'], 2); ?>
                    </td>

                    <td>
                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-btn">
                            View
                        </a>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p class="welcome-text">No orders yet.</p>

    <?php } ?>

</div>


<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>
<?php include 'admin_layout_close.php'; ?>

</body>
</html>
