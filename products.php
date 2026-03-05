<?php
// Safe session start
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

include 'config/db.php';

// Only require login (NOT role specific)
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

// Get selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch products
if($selected_category) {

    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.description, p.sizes, p.price, p.stock, p.image, c.name AS category
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.category_id=?
        ORDER BY p.id DESC
    ");

    $stmt->bind_param("i", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT p.id, p.name, p.description, p.sizes, p.price, p.stock, p.image, c.name AS category
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ");

}


// Add to cart
if(isset($_POST['add_to_cart'])) {

    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = 1;

    // Check existing cart item
    $stmt = $conn->prepare("
        SELECT id, quantity FROM cart
        WHERE user_id=? AND product_id=?
    ");

    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($cart_id, $existing_qty);

    if($stmt->num_rows > 0){

        $stmt->fetch();
        $new_qty = $existing_qty + 1;

        $update = $conn->prepare("
            UPDATE cart SET quantity=? WHERE id=?
        ");

        $update->bind_param("ii", $new_qty, $cart_id);
        $update->execute();
        $update->close();

    } else {

        $insert = $conn->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?,?,?)
        ");

        $insert->bind_param("iii", $user_id, $product_id, $quantity);
        $insert->execute();
        $insert->close();
    }

    $stmt->close();

    $message = "Product added to cart!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - NSBM Store</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/footer.css">

</head>
<body>

<?php include 'partials/navbar.php'; ?>

<main class="products-page">

    <h2>Welcome, <?php echo $_SESSION['user_name']; ?></h2>

    <?php if(isset($message)) { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <!-- Category Filter -->
    <form method="GET" class="category-filter">

        <label>Category:</label>

        <select name="category" onchange="this.form.submit()">

            <option value="">All</option>

            <?php while($cat = $categories->fetch_assoc()) { ?>

                <option value="<?php echo $cat['id']; ?>"
                <?php if($selected_category == $cat['id']) echo "selected"; ?>>

                    <?php echo $cat['name']; ?>

                </option>

            <?php } ?>

        </select>

    </form>


    <!-- Products Grid -->
    <div class="products-grid">

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="product-card"
                 data-id="<?php echo $row['id']; ?>"
                 data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                 data-description="<?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES); ?>"
                 data-category="<?php echo htmlspecialchars($row['category'], ENT_QUOTES); ?>"
                 data-price="Rs. <?php echo number_format($row['price'], 2); ?>"
                 data-image="<?php echo htmlspecialchars($row['image'], ENT_QUOTES); ?>"
                 data-sizes="<?php echo htmlspecialchars($row['sizes'] ?? '', ENT_QUOTES); ?>"
                 data-stock="<?php echo $row['stock']; ?>"
                 onclick="openModal(this)">

                <?php if($row['image']) { ?>
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php } ?>

                <h3><?php echo htmlspecialchars($row['name']); ?></h3>

                <p class="category"><?php echo htmlspecialchars($row['category']); ?></p>
                <p class="price">Rs. <?php echo number_format($row['price'], 2); ?></p>

                <div class="card-btn-wrap">
                    <?php if($row['stock'] > 0) { ?>
                        <button type="button" class="btn"
                            onclick="event.stopPropagation(); openModal(this.closest('.product-card'))">
                            Add to Cart
                        </button>
                    <?php } else { ?>
                        <button class="btn" disabled>Out of Stock</button>
                    <?php } ?>
                </div>

            </div>

        <?php } ?>

    </div>

</main>


<!-- ── PRODUCT MODAL ── -->
<div class="modal-backdrop" id="modalBackdrop">

    <div class="modal-box" id="modalBox">

        <button class="modal-close" onclick="closeModal()">&#x2715;</button>

        <div class="modal-image-wrap">
            <img id="modalImage" src="" alt="Product Image"/>
        </div>

        <div class="modal-info">

            <p class="modal-category" id="modalCategory"></p>
            <h2 class="modal-title" id="modalTitle"></h2>
            <p class="modal-price" id="modalPrice"></p>

            <div class="modal-divider"></div>

            <p class="modal-desc" id="modalDesc"></p>

            <div id="sizeSection" style="display:none;">
                <p class="modal-size-label">SELECT SIZE</p>
                <div class="modal-sizes" id="modalSizes"></div>
            </div>

            <form method="POST" id="modalCartForm" onsubmit="return validateSize()">
                <input type="hidden" name="product_id" id="modalProductId"/>
                <input type="hidden" name="selected_size" id="selectedSizeInput"/>
                <button type="submit" name="add_to_cart" class="btn modal-cart-btn" id="modalCartBtn">
                    Add to Cart
                </button>
            </form>

        </div>

    </div>

</div>


<script>
let selectedSize = null;

function openModal(card) {
    selectedSize = null;

    const id          = card.dataset.id;
    const name        = card.dataset.name;
    const description = card.dataset.description;
    const category    = card.dataset.category;
    const price       = card.dataset.price;
    const image       = card.dataset.image;
    const sizesRaw    = card.dataset.sizes;
    const inStock     = parseInt(card.dataset.stock) > 0;

    document.getElementById('modalProductId').value      = id;
    document.getElementById('modalTitle').textContent    = name;
    document.getElementById('modalCategory').textContent = category;
    document.getElementById('modalPrice').textContent    = price;
    document.getElementById('modalDesc').textContent     = description || 'No description available.';

    // Image
    const img = document.getElementById('modalImage');
    if (image) {
        img.src = image;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    // Sizes
    const sizeSection = document.getElementById('sizeSection');
    const sizesRow    = document.getElementById('modalSizes');
    sizesRow.innerHTML = '';

    if (sizesRaw && sizesRaw.trim() !== '') {
        const sizes = sizesRaw.split(',').map(s => s.trim()).filter(s => s !== '');
        if (sizes.length > 0) {
            sizeSection.style.display = 'block';
            sizes.forEach(size => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'size-btn';
                btn.textContent = size;
                btn.onclick = () => selectSize(btn, size);
                sizesRow.appendChild(btn);
            });
        } else {
            sizeSection.style.display = 'none';
        }
    } else {
        sizeSection.style.display = 'none';
    }

    // Cart button
    const cartBtn = document.getElementById('modalCartBtn');
    cartBtn.disabled = !inStock;
    cartBtn.textContent = inStock ? 'Add to Cart' : 'Out of Stock';

    // Show modal
    document.getElementById('modalBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function selectSize(btn, size) {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedSize = size;
    document.getElementById('selectedSizeInput').value = size;
}

function validateSize() {
    const sizes = document.querySelectorAll('.size-btn');
    if (sizes.length > 0 && !selectedSize) {
        alert('Please select a size before adding to cart.');
        return false;
    }
    return true;
}

function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
    document.body.style.overflow = '';
    selectedSize = null;
}

// Close on backdrop click
document.getElementById('modalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include 'partials/footer.php'; ?>

</body>
</html>