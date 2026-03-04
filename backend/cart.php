<?php
// Start session safely
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

include 'config/db.php';

// Allow any logged-in user (admin or customer)
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


// UPDATE QUANTITY
if(isset($_POST['update_cart'])) {

    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("
        UPDATE cart 
        SET quantity=? 
        WHERE id=? AND user_id=?
    ");

    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
}


// REMOVE ITEM
if(isset($_POST['remove_item'])) {

    $cart_id = $_POST['cart_id'];

    $stmt = $conn->prepare("
        DELETE FROM cart 
        WHERE id=? AND user_id=?
    ");

    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
}


// FETCH CART ITEMS
$stmt = $conn->prepare("
    SELECT 
        c.id AS cart_id,
        p.name,
        p.price,
        p.image,
        c.quantity

    FROM cart c

    JOIN products p ON c.product_id = p.id

    WHERE c.user_id=?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

    <title>Cart - NSBM Store</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/footer.css">

</head>
<body>


<?php include 'partials/navbar.php'; ?>


<main class="cart-page">

    <h2>Your Shopping Cart</h2>


    <?php if($result->num_rows > 0) { ?>

        <div class="cart-grid">

        <?php
        $total = 0;

        while($row = $result->fetch_assoc()) {

            $subtotal = $row['price'] * $row['quantity'];

            $total += $subtotal;
        ?>

            <div class="cart-item">

                <?php if(!empty($row['image'])) { ?>

                    <img src="<?php echo $row['image']; ?>">

                <?php } ?>


                <div class="cart-details">

                   <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                   

                    <p>Price: Rs. <?php echo $row['price']; ?></p>


                    <form method="POST">

                        <input type="number"
                               name="quantity"
                               value="<?php echo $row['quantity']; ?>"
                               min="1"
                               required>

                        <input type="hidden"
                               name="cart_id"
                               value="<?php echo $row['cart_id']; ?>">


                        <button type="submit"
                                name="update_cart"
                                class="btn">

                            Update

                        </button>


                        <button type="submit"
                                name="remove_item"
                                class="btn remove">

                            Remove

                        </button>

                    </form>


                    <p class="subtotal">
                        Subtotal: Rs. <?php echo $subtotal; ?>
                    </p>


                </div>

            </div>

        <?php } ?>


        </div>


        <div class="cart-summary">

            <h3>Total: Rs. <?php echo $total; ?></h3>

            <a href="checkout.php" class="btn checkout">
                Proceed to Checkout
            </a>

        </div>


    <?php } else { ?>


        <p class="empty">

            Your cart is empty.

            <a href="products.php">Shop Now</a>

        </p>


    <?php } ?>


</main>


<?php include 'partials/footer.php'; ?>


</body>
</html>
