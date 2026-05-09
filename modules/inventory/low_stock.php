<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Low Stock Alerts';
$productModel = new Product($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$low_stock_products = $productModel->getLowStock($business_id);
$count = count($low_stock_products);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Low Stock Alerts</h2>
                <div class="kpi-card">Total Low Stock Items: <b><?= $count ?></b></div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock Qty</th>
                            <th>Low Stock Threshold</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($low_stock_products)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No low stock items.</td></tr>
                        <?php else: ?>
                            <?php foreach ($low_stock_products as $prod): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['name']) ?></td>
                                    <td><?= htmlspecialchars($prod['category']) ?></td>
                                    <td style="color:#d97706; font-weight:bold;">
                                        <?= $prod['stock_qty'] ?>
                                    </td>
                                    <td><?= $prod['low_stock_threshold'] ?></td>
                                    <td>
                                        <button class="btn btn-primary" onclick="openRestockModal(<?= $prod['id'] ?>, '<?= htmlspecialchars($prod['name']) ?>')">Restock</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div id="restock-modal" class="modal" style="display:none;">
                <div class="modal-content" style="max-width:400px;">
                    <span class="close" onclick="closeRestockModal()">&times;</span>
                    <h3>Restock Product</h3>
                    <form id="restock-form" action="../../handlers/inventory_handler.php" method="POST">
                        <input type="hidden" name="action" value="adjust_stock">
                        <input type="hidden" name="id" id="restock-product-id">
                        <div class="form-group">
                            <label for="restock-qty">Quantity to Add</label>
                            <input type="number" id="restock-qty" name="qty" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="restock-reason">Reason</label>
                            <input type="text" id="restock-reason" name="reason" class="form-control" value="Restock" required>
                        </div>
                        <button type="submit" class="btn btn-success">Restock</button>
                    </form>
                </div>
            </div>
            <script>
                function openRestockModal(id, name) {
                    document.getElementById('restock-product-id').value = id;
                    document.getElementById('restock-qty').value = '';
                    document.getElementById('restock-modal').style.display = 'block';
                }
                function closeRestockModal() {
                    document.getElementById('restock-modal').style.display = 'none';
                }
                window.onclick = function(event) {
                    var modal = document.getElementById('restock-modal');
                    if (event.target == modal) {
                        closeRestockModal();
                    }
                }
            </script>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// Low stock inventory page for BMS