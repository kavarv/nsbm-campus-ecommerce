<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
?>

<header class="navbar">

    <div class="logo">
        <a href="/nsbm_campus_ecommerce/index.php">NSBM STORE</a>
    </div>

    <nav>

        <a href="/nsbm_campus_ecommerce/index.php">Home</a>

        <a href="/nsbm_campus_ecommerce/products.php">Shop</a>

        <?php if(isset($_SESSION['user_id'])) { ?>

            <span class="user">
                Hello, <?php echo $_SESSION['user_name']; ?>
            </span>

            <?php if($_SESSION['role'] == 'admin') { ?>
                <a href="/nsbm_campus_ecommerce/admin/dashboard.php">Dashboard</a>
            <?php } else { ?>
                <a href="/nsbm_campus_ecommerce/cart.php">Cart</a>
            <?php } ?>

            <a href="/nsbm_campus_ecommerce/logout.php">Logout</a>

        <?php } else { ?>

            <a href="/nsbm_campus_ecommerce/login.php">Login</a>
            <a href="/nsbm_campus_ecommerce/register.php">Register</a>

        <?php } ?>

    </nav>

</header>