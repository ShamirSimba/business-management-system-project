<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Add Product';
$productModel = new Product($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$categories = $productModel->getCategories($business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Add Product</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="../../handlers/inventory_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" class="form-control" list="category-list" required>
                    <datalist id="category-list">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="cost_price">Cost Price</label>
                    <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="stock_qty">Initial Stock Qty</label>
                    <input type="number" id="stock_qty" name="stock_qty" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="low_stock_threshold">Low Stock Threshold</label>
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// Create inventory item page for BMS