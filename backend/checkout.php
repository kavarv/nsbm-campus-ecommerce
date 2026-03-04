<?php
session_start();
include 'config/db.php';

// Only allow logged-in customers
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items (including size)
$stmt = $conn->prepare("
    SELECT c.id AS cart_id, p.name, p.price, c.quantity, c.size
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle checkout
if(isset($_POST['checkout'])) {

    // Step 1: Calculate total from cart
    $stmt_total = $conn->prepare("
        SELECT SUM(p.price * c.quantity) AS total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt_total->bind_param("i", $user_id);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result()->fetch_assoc();
    $total = $total_result['total'];
    $stmt_total->close();

    // Step 2: Insert into orders table
    $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt_order->bind_param("id", $user_id, $total);
    $stmt_order->execute();
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // Step 3: Insert each cart item into order_items (including size)
    $stmt_items = $conn->prepare("
        SELECT c.product_id, c.quantity, p.price, c.size
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt_items->bind_param("i", $user_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result();

    $stmt_insert = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");
    while($item = $items->fetch_assoc()) {
        $stmt_insert->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['size']);
        $stmt_insert->execute();
    }
    $stmt_insert->close();
    $stmt_items->close();

    // Step 4: Clear the cart
    $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id=?");
    $stmt_clear->bind_param("i", $user_id);
    $stmt_clear->execute();
    $stmt_clear->close();

    $message = "Purchase completed successfully! Thank you.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - NSBM Store</title>
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<main class="checkout-page">
    <h2>Checkout</h2>

    <?php if(isset($message)) { ?>
        <p class="success"><?php echo $message; ?></p>
        <a href="products.php" class="btn shop-more">Shop More</a>
    <?php } else { ?>
        <?php if($result->num_rows > 0) { 
            $total = 0; ?>
            <div class="checkout-grid">
                <?php while($row = $result->fetch_assoc()) { 
                    $subtotal = $row['price'] * $row['quantity'];
                    $total += $subtotal;
                ?>
                    <div class="checkout-item">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <?php if($row['size']): ?>
                            <p>Size: <?php echo htmlspecialchars($row['size']); ?></p>
                        <?php endif; ?>
                        <p>Price: Rs. <?php echo $row['price']; ?></p>
                        <p>Quantity: <?php echo $row['quantity']; ?></p>
                        <p>Subtotal: Rs. <?php echo $subtotal; ?></p>
                    </div>
                <?php } ?>
            </div>

            <div class="checkout-summary">
                <h3>Total: Rs. <?php echo $total; ?></h3>
                <form method="POST" action="">
                    <button type="submit" name="checkout" class="btn checkout-btn">Confirm Purchase</button>
                </form>
            </div>
        <?php } else { ?>
            <p class="empty">Your cart is empty. <a href="products.php">Shop Now</a></p>
        <?php } ?>
    <?php } ?>
</main>

<?php include 'partials/footer.php'; ?>
</body>
</html>