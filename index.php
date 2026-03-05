<?php
session_start();
include 'config/db.php';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

// Fetch latest products
$featured = $conn->query("
    SELECT p.id, p.name, p.price, p.stock, p.image, p.category_id, c.name AS category
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 6
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>NSBM Campus Store</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/footer.css">

</head>
<body>


<?php include 'partials/navbar.php'; ?>



<!-- HERO SECTION -->
<section class="hero">

    <div class="hero-content">

        <h1>Campus Essentials</h1>

        <p>
            Minimal. Modern. Made for NSBM students.
        </p>

        <a href="products.php" class="shop-btn">
            Shop Collection
        </a>

    </div>

</section>



<!-- CATEGORY SECTION -->
<section class="featured">

    <h2>Featured Categories</h2>

    <div class="categories">

        <?php while($cat = $categories->fetch_assoc()) { ?>

            <div class="category-card">

                <a href="products.php?category=<?php echo $cat['id']; ?>">

                    <img src="assets/images/<?php echo strtolower($cat['name']); ?>.jpg" onerror="this.src='assets/images/placeholder.jpg'">

                    <p>
                        <?php echo $cat['name']; ?>
                    </p>

                </a>

            </div>

        <?php } ?>

    </div>

</section>



<!-- PRODUCT SECTION -->
<section class="featured-products">

    <h2>Latest Products</h2>

    <div class="products-grid">

        <?php while($row = $featured->fetch_assoc()) { ?>

            <div class="product-card">

                <?php if($row['image']) { ?>

                    <img src="<?php echo $row['image']; ?>">

                <?php } ?>

                <h3>
                    <?php echo $row['name']; ?>
                </h3>

                <p class="category">
                    <?php echo $row['category']; ?>
                </p>

                <p class="price">
                    Rs. <?php echo $row['price']; ?>
                </p>

                <a href="products.php?category=<?php echo $row['category_id']; ?>" class="btn">
                    View Product
                </a>

            </div>

        <?php } ?>

    </div>

</section>




<?php include 'partials/footer.php'; ?>

</body>
</html>
