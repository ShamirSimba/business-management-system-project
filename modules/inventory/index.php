<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../classes/Business.php';
$page_title = 'Inventory Management';
$productModel = new Product($conn);
$businessModel = new Business($conn);

// Get all user's businesses
$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);
$business_id = $current_business_id;

$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$products = $productModel->getAll($business_id, $category_filter, $search);
$categories = $productModel->getCategories($business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Inventory Management</h2>
                <a href="create.php?business_id=<?= $current_business_id ?>" class="btn btn-primary">Add Product</a>
            </div>
            <form method="GET" class="flex gap-1 mb-2">
                <input type="hidden" name="business_id" value="<?= $current_business_id ?>">
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" class="form-control" style="max-width:200px;">
                <select name="category" class="form-control" style="max-width:180px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter==$cat?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock Qty</th>
                            <th>Low Stock Threshold</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No products found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $prod): ?>
                                <?php
                                    $status = 'In Stock';
                                    $badge = 'badge-success';
                                    if ($prod['stock_qty'] <= 0) {
                                        $status = 'Out of Stock';
                                        $badge = 'badge-danger';
                                    } elseif ($prod['stock_qty'] <= $prod['low_stock_threshold']) {
                                        $status = 'Low Stock';
                                        $badge = 'badge-warning';
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['name']) ?></td>
                                    <td><?= htmlspecialchars($prod['category']) ?></td>
                                    <td>TZS <?= number_format($prod['cost_price'],2) ?></td>
                                    <td>TZS <?= number_format($prod['selling_price'],2) ?></td>
                                    <td style="color:<?= $status=='Low Stock'?'#d97706':($status=='Out of Stock'?'#dc2626':'inherit') ?>; font-weight:bold;">
                                        <?= $prod['stock_qty'] ?>
                                    </td>
                                    <td><?= $prod['low_stock_threshold'] ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                                    <td>
                                        <a href="edit.php?id=<?= $prod['id'] ?>&business_id=<?= $current_business_id ?>" class="btn btn-success">Edit</a>
                                        <form action="../../handlers/inventory_handler.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                            <input type="hidden" name="business_id" value="<?= $current_business_id ?>">
                                            <button type="submit" class="btn btn-danger" data-confirm-delete>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>