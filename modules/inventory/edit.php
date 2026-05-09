<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Edit Product';
$productModel = new Product($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $productModel->getById($id);
if (!$product) {
    header('Location: index.php');
    exit;
}
$categories = $productModel->getCategories($business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Edit Product</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="../../handlers/inventory_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" class="form-control" list="category-list" value="<?= htmlspecialchars($product['category']) ?>" required>
                    <datalist id="category-list">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="cost_price">Cost Price</label>
                    <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control" value="<?= htmlspecialchars($product['cost_price']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control" value="<?= htmlspecialchars($product['selling_price']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="low_stock_threshold">Low Stock Threshold</label>
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control" value="<?= htmlspecialchars($product['low_stock_threshold']) ?>" required>
                </div>
                <button type="submit" class="btn btn-success">Update Product</button>
            </form>
            <div class="card mt-4" style="max-width:500px; margin:auto;">
                <h3>Stock Adjustment</h3>
                <form action="../../handlers/inventory_handler.php" method="POST" class="flex gap-1 align-center">
                    <input type="hidden" name="action" value="adjust_stock">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    <input type="number" name="qty" class="form-control" placeholder="Qty (+/-)" required style="max-width:100px;">
                    <input type="text" name="reason" class="form-control" placeholder="Reason" required style="max-width:180px;">
                    <button type="submit" class="btn btn-primary">Adjust</button>
                </form>
            </div>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// Edit inventory item page for BMS