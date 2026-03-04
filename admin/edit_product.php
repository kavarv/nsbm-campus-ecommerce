<?php

session_start();
include '../config/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if(!isset($_GET['id'])){
    header("Location: manage_products.php");
    exit;
}

$id = intval($_GET['id']);

/* Fetch product */
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if(!$product){
    header("Location: manage_products.php");
    exit;
}

/* Fetch categories */
$categories = $conn->query("SELECT * FROM categories");

/* Update product */
if(isset($_POST['update'])){

    $name        = $conn->real_escape_string(trim($_POST['name']));
    $category_id = intval($_POST['category_id']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $description = $conn->real_escape_string(trim($_POST['description']));
    $sizes       = $conn->real_escape_string(trim($_POST['sizes']));

    $image = $conn->real_escape_string($product['image']);

    if(!empty($_FILES['image']['name'])){
        $new_image   = time().'_'.basename($_FILES['image']['name']);
        $upload_path = '../uploads/'.$new_image;

        if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)){
            $image = 'uploads/'.$new_image;
        }
    }

    $sql = "UPDATE products 
            SET category_id=$category_id, 
                name='$name', 
                description='$description', 
                price=$price, 
                image='$image', 
                stock=$stock, 
                sizes='$sizes'
            WHERE id=$id";

    if($conn->query($sql)){
        $message = "✅ Product updated successfully! | Sizes saved: " . $sizes;
    } else {
        $message = "❌ Error: " . $conn->error;
    }

    /* Refresh product data */
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    /* Refresh categories */
    $categories = $conn->query("SELECT * FROM categories");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_layout.php'; ?>

<div class="admin-container">

    <h2>Edit Product</h2>

    <?php if($message != ''): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="admin-form">

        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label>Category</label>
        <select name="category_id" required>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>"
                    <?php if($cat['id'] == $product['category_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Price (Rs)</label>
        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>

        <label>Description</label>
        <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

        <label>Sizes Available <span style="font-weight:400;color:#999;">(optional — comma separated e.g. S,M,L,XL)</span></label>
        <input type="text" name="sizes" value="<?php echo htmlspecialchars($product['sizes'] ?? ''); ?>" placeholder="e.g. S,M,L,XL or leave blank if not applicable">

        <label>Current Image</label>
        <?php if($product['image']): ?>
            <img src="../<?php echo htmlspecialchars($product['image']); ?>" style="width:120px;height:120px;object-fit:cover;border-radius:6px;margin-bottom:10px;display:block;">
        <?php endif; ?>

        <label>Change Image</label>
        <input type="file" name="image" accept="image/*">

        <div style="display:flex;gap:12px;margin-top:10px;">
            <button type="submit" name="update" class="admin-btn">Update Product</button>
            <a href="manage_products.php" style="padding:12px 24px;background:#f0f0f0;color:#1a1a1a;border-radius:4px;font-size:14px;font-weight:600;text-decoration:none;">Cancel</a>
        </div>

    </form>

</div>

<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>

<?php include 'admin_layout_close.php'; ?>

</body>
</html>