<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Inventory Report';
$productModel = new Product($conn);
$businessModel = new Business($conn);

// Get all user's businesses
$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);
$business_id = $current_business_id;

$category_filter = $_GET['category'] ?? '';
$products = isset($_GET['category']) !== false ? $productModel->getAll($business_id, $category_filter) : [];
$categories = $productModel->getCategories($business_id);
$total_stock_value = 0;
foreach ($products as $prod) {
    $total_stock_value += ($prod['cost_price'] * $prod['stock_qty']);
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Inventory Report</h2>
            <form method="GET" class="card mb-3">
                <input type="hidden" name="business_id" value="<?= $current_business_id ?>">
                <div class="flex gap-1 align-end">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter==$cat?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <?php if (isset($_GET['category']) !== false): ?>
                        <button type="button" class="btn btn-success" onclick="exportPDF('inventory')">Export PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportExcel('inventory')">Export Excel</button>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($_GET['category']) !== false && !empty($products)): ?>
                <div class="kpi-card mb-3"><div class="kpi-label">Total Stock Value</div><div class="kpi-value">TZS <?= number_format($total_stock_value, 2) ?></div></div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Stock Qty</th>
                                <th>Stock Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $prod): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['name']) ?></td>
                                    <td><?= htmlspecialchars($prod['category']) ?></td>
                                    <td>TZS <?= number_format($prod['cost_price'], 2) ?></td>
                                    <td>TZS <?= number_format($prod['selling_price'], 2) ?></td>
                                    <td><?= $prod['stock_qty'] ?></td>
                                    <td>TZS <?= number_format($prod['cost_price'] * $prod['stock_qty'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['category']) !== false): ?>
                <div class="alert alert-info">No products found for the selected category.</div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function exportPDF(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../handlers/report_handler.php';
    form.innerHTML = `
        <input type="hidden" name="report_type" value="${type}">
        <input type="hidden" name="format" value="pdf">
        <input type="hidden" name="category" value="<?= $category_filter ?>">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportExcel(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../handlers/report_handler.php';
    form.innerHTML = `
        <input type="hidden" name="report_type" value="${type}">
        <input type="hidden" name="format" value="excel">
        <input type="hidden" name="category" value="<?= $category_filter ?>">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>