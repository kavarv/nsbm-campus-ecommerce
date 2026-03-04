<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$result = $conn->query("
    SELECT p.id, p.name, p.price, p.stock, p.image, p.sizes, c.name AS category
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - NSBM Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .admin-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .admin-table th { background: #1a1a1a; color: #fff; padding: 14px 16px; text-align: left; font-size: 13px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .admin-table td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #1a1a1a; vertical-align: middle; background: #ffffff; }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tbody tr:hover td { background: #f5f7fa; }
        .admin-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .stock-in  { background: #e6f4ed; color: #0a7e3e; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .stock-out { background: #fdecea; color: #c41e3a; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .action-wrap { display: flex; gap: 8px; }
        .edit-btn { padding: 6px 14px; background: #e8f0fe; color: #1a56db; border: 1px solid #c7d7fb; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .edit-btn:hover { background: #d0e2fd; }
        .del-btn  { padding: 6px 14px; background: #fff; color: #c41e3a; border: 1.5px solid #c41e3a; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; cursor: pointer; }
        .del-btn:hover { background: #c41e3a; color: #fff; }
        .add-btn  { padding: 10px 22px; background: #1a1a1a; color: #fff; border-radius: 4px; font-size: 14px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .add-btn:hover { background: #333; }

        .confirm-backdrop { display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; }
        .confirm-backdrop.open { display:flex; }
        .confirm-box { background:#fff; border-radius:8px; padding:36px 32px; width:min(380px,90vw); text-align:center; box-shadow:0 12px 40px rgba(0,0,0,0.15); }
        .confirm-box h3 { font-size:20px; margin-bottom:8px; }
        .confirm-box p  { color:#666; font-size:14px; margin-bottom:28px; }
        .confirm-actions { display:flex; gap:12px; justify-content:center; }
        .cancel-btn { padding:10px 24px; background:#f0f0f0; color:#1a1a1a; border:none; border-radius:4px; font-size:14px; font-weight:600; cursor:pointer; }
        .cancel-btn:hover { background:#e0e0e0; }
    </style>
</head>
<body>

<?php include 'admin_layout.php'; ?>

<div class="admin-container">

    <div class="top-bar">
        <h2>Manage Products</h2>
        <a href="add_product.php" class="add-btn">+ Add New Product</a>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="msg" style="background:#e6f4ed;color:#0a7e3e;border:1px solid #b7e4ca;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="msg" style="background:#fdecea;color:#c41e3a;border:1px solid #f5c6c6;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Sizes</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if($row['image']): ?>
                        <img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="">
                    <?php else: ?>
                        <span style="color:#999;font-size:12px;">No image</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td>Rs <?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo $row['sizes'] ? htmlspecialchars($row['sizes']) : '—'; ?></td>
                <td>
                    <?php if($row['stock'] > 0): ?>
                        <span class="stock-in"><?php echo $row['stock']; ?> in stock</span>
                    <?php else: ?>
                        <span class="stock-out">Out of stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-wrap">
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                        <button class="del-btn" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')">Delete</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<div class="confirm-backdrop" id="confirmBackdrop">
    <div class="confirm-box">
        <h3>Delete Product?</h3>
        <p id="confirmText">Are you sure you want to delete this product? This cannot be undone.</p>
        <div class="confirm-actions">
            <button class="cancel-btn" onclick="closeConfirm()">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="del-btn">Delete</a>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© 2026 NSBM Campus Store Admin</p>
</footer>
<?php include 'admin_layout_close.php'; ?>

<script>
function confirmDelete(id, name) {
    document.getElementById('confirmText').textContent = 'Are you sure you want to delete "' + name + '"? This cannot be undone.';
    document.getElementById('confirmDeleteBtn').href = 'delete_product.php?id=' + id;
    document.getElementById('confirmBackdrop').classList.add('open');
}
function closeConfirm() {
    document.getElementById('confirmBackdrop').classList.remove('open');
}
document.getElementById('confirmBackdrop').addEventListener('click', function(e) {
    if(e.target === this) closeConfirm();
});
</script>

</body>
</html>