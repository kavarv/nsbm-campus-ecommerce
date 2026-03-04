<?php
/* Shared Admin Layout Wrapper */
?>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="logo">
            NSBM ADMIN
        </div>

        <a href="dashboard.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
           Dashboard
        </a>

        <a href="add_product.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>">
           Add Product
        </a>
        
        <a href="manage_products.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'active' : ''; ?>">
           Manage Products
        </a>

        <a href="all_orders.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'all_orders.php' ? 'active' : ''; ?>">
           Orders
        </a>

        <a href="../index.php">View Store</a>

        <a href="../logout.php">Logout</a>

    </aside>


    <!-- MAIN AREA -->
    <div class="admin-main">

        <!-- TOP BAR -->
        <div class="admin-topbar">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </div>

        <!-- PAGE CONTENT START -->
        <div class="admin-content">
