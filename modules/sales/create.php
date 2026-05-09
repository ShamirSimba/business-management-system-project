<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Record New Sale';
$productModel = new Product($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$products = $productModel->getAll($business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Record New Sale</h2>
            <div class="flex gap-2">
                <div class="card" style="flex:1; min-width:320px;">
                    <h3>Products</h3>
                    <input type="text" id="product-search" class="form-control mb-2" placeholder="Search products...">
                    <div id="product-list" style="max-height:400px; overflow-y:auto;">
                        <?php foreach ($products as $prod): ?>
                            <div class="product-item" data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>" data-price="<?= $prod['selling_price'] ?>" data-stock="<?= $prod['stock_qty'] ?>">
                                <b><?= htmlspecialchars($prod['name']) ?></b> <span class="text-muted">(Stock: <?= $prod['stock_qty'] ?>)</span><br>
                                <span class="text-success">TZS <?= number_format($prod['selling_price'],2) ?></span>
                                <button type="button" class="btn btn-primary btn-sm float-right add-to-cart">Add</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card" style="flex:1; min-width:320px;">
                    <h3>Cart / Order Summary</h3>
                    <form id="sale-form" action="../../handlers/sale_handler.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="cart_json" id="cart-json">
                        <div id="cart-list"></div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Order Total:</label>
                            <span id="order-total" class="kpi-value">TZS 0.00</span>
                        </div>
                        <button type="submit" class="btn btn-success">Complete Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="../../assets/js/main.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// Create sale page for BMS