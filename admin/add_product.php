<?php
session_start();
include '../config/db.php';

/* ===== ADMIN PROTECTION ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

/* ===== FETCH CATEGORIES ===== */
$categories = $conn->query("SELECT * FROM categories");

/* ===== ADD PRODUCT ===== */
if (isset($_POST['add_product'])) {

    $name        = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];
    $description = trim($_POST['description']);
    $sizes       = trim($_POST['sizes']); // ← NEW

    /* ---- IMAGE UPLOAD ---- */
    $image_path = "";

    if (!empty($_FILES['image']['name'])) {

        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target    = "../uploads/" . $file_name;

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "Only JPG, PNG, WEBP images allowed.";
        } else {

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_path = "uploads/" . $file_name;
            } else {
                $message = "Image upload failed.";
            }
        }
    }

    /* ---- INSERT INTO DATABASE ---- */
    if ($message === "") {

        $stmt = $conn->prepare("
            INSERT INTO products (category_id, name, description, price, image, stock, sizes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("issdsis",  // ← added 's' for sizes
            $category_id,
            $name,
            $description,
            $price,
            $image_path,
            $stock,
            $sizes  // ← NEW
        );

        if ($stmt->execute()) {
            $message = "✅ Product added successfully!";
        } else {
            $message = "❌ Error adding product.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_layout.php'; ?>


<!-- MAIN -->
<div class="admin-container">

    <h2>Add New Product</h2>

    <?php if ($message != "") { ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data" class="admin-form">

        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Category</label>
        <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php while ($cat = $categories->fetch_assoc()) { ?>
                <option value="<?php echo $cat['id']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Price (Rs)</label>
        <input type="number" step="0.01" name="price" required>

        <label>Stock</label>
        <input type="number" name="stock" required>

        <label>Description</label>
        <textarea name="description" rows="4"></textarea>

        <!-- ── NEW: SIZES FIELD ── -->
        <label>Sizes Available <span style="font-weight:400; color:#999;">(optional — comma separated e.g. S,M,L,XL)</span></label>
        <input type="text" name="sizes" placeholder="e.g. S,M,L,XL or leave blank if not applicable">

        <label>Product Image</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="add_product" class="admin-btn">
            Add Product
        </button>

    </form>

</div>


<!-- FOOTER -->
<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>
<?php include 'admin_layout_close.php'; ?>

</body>
</html>