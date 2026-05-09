<?php
// Dashboard index page for BMS

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../classes/Profit.php';
require_once __DIR__ . '/../../classes/Investment.php';
require_once __DIR__ . '/../../auth/session.php';

$page_title = 'Dashboard';

$businessModel = new Business($conn);
$productModel = new Product($conn);
$saleModel = new Sale($conn);
$profitModel = new Profit($conn);

$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : null;
if (!$current_business_id && !empty($businesses)) {
    $current_business_id = $businesses[0]['id'];
}

$current_business = $current_business_id ? $businessModel->getById($current_business_id) : null;

$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');
$profitData = $profitModel->calculate($current_business_id, $startOfMonth, $endOfMonth);

$products = $current_business_id ? $productModel->getAll($current_business_id) : [];
$lowStockProducts = $current_business_id ? $productModel->getLowStock($current_business_id) : [];
$recentSales = $current_business_id ? array_slice($saleModel->getAll($current_business_id), 0, 10) : [];

$low_stock_count = count($lowStockProducts);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
// require_once __DIR__ . '/../../includes/topbar.php';
?>  

        <div class="container">
            <div class="flex justify-between align-center mb-3">
            <h2>Dashboard</h2>
            <div class="sidebar-user-role"><?= htmlspecialchars($current_user['role'] ?? '') ?></div> 

            </div>
            <section class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">

                <div class="kpi-card">
                    <div class="flex justify-between align-center">
                        <div>
                            <p class="kpi-label">Total Revenue</p>
                            <p class="kpi-value">TZS <?= number_format($profitData['revenue'], 2) ?></p>
                        </div>
                        <div class="kpi-icon" style="background: rgba(39, 174, 96, 0.14); color: var(--color-success);"></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="flex justify-between align-center">
                        <div>
                            <p class="kpi-label">Total Expenses</p>
                            <p class="kpi-value">TZS <?= number_format($profitData['expenses'], 2) ?></p>
                        </div>
                        <div class="kpi-icon" style="background: rgba(230, 126, 34, 0.14); color: var(--color-warning);"></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="flex justify-between align-center">
                        <div>
                            <p class="kpi-label">Net Profit</p>
                            <p class="kpi-value">TZS <?= number_format($profitData['net_profit'], 2) ?></p>
                        </div>
                        <div class="kpi-icon" style="background: rgba(30, 58, 95, 0.14); color: var(--color-primary);"></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="flex justify-between align-center">
                        <div>
                            <p class="kpi-label">Total Products</p>
                            <p class="kpi-value"><?= count($products) ?> <span style="font-size:0.9rem; color: var(--color-muted);">(<?= $low_stock_count ?> low stock)</span></p>
                        </div>
                        <div class="kpi-icon" style="background: rgba(96, 108, 130, 0.14); color: var(--color-text);"></div>
                    </div>
                </div>
            </section>

            <section class="grid mt-3" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="flex justify-between align-center mb-2">
                        <div>
                            <h3>Monthly Sales</h3>
                            <p class="text-muted">Last 6 months</p>
                        </div>
                    </div>
                    <canvas id="salesChart" height="240"></canvas>
                </div>
                <div class="card">
                    <div class="flex justify-between align-center mb-2">
                        <div>
                            <h3>Profit vs Expenses</h3>
                            <p class="text-muted">Last 6 months</p>
                        </div>
                    </div>
                    <canvas id="profitChart" height="240"></canvas>
                </div>
            </section>

            <section class="grid mt-3" style="grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <div class="card">
                    <div class="flex justify-between align-center mb-3">
                        <div>
                            <h3>Recent Sales</h3>
                        </div>
                        <a href="../sales/index.php" class="btn btn-primary">View All</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentSales)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No recent sales found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <?php
                                            $stmt = $conn->prepare("SELECT SUM(qty) as count_items FROM sale_items WHERE sale_id = ?");
                                            $stmt->bind_param('i', $sale['id']);
                                            $stmt->execute();
                                            $count_items = $stmt->get_result()->fetch_assoc()['count_items'] ?? 0;
                                            $stmt->close();
                                            $statusClass = $sale['status'] === 'completed' ? 'badge-success' : 'badge-warning';
                                        ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($sale['created_at'])) ?></td>
                                            <td><?= intval($count_items) ?></td>
                                            <td>TZS <?= number_format($sale['total_amount'], 2) ?></td>
                                            <td><?= ucfirst($sale['payment_method']) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= ucfirst($sale['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="flex justify-between align-center mb-3">
                        <div>
                            <h3>Low Stock Alert</h3>
                            <p class="text-muted">Products below threshold</p>
                        </div>
                    </div>
                    <?php if (empty($lowStockProducts)): ?>
                        <p class="text-muted">No low stock products.</p>
                    <?php else: ?>
                        <div class="grid" style="gap: 1rem;">
                            <?php foreach ($lowStockProducts as $product): ?>
                                <div class="card" style="padding: 1rem; border: 1px solid rgba(230, 126, 34, 0.15);">
                                    <h4><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="text-muted">Stock: <?= intval($product['stock_qty']) ?> / Threshold: <?= intval($product['low_stock_threshold']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-3 text-right">
                        <a href="../inventory/index.php" class="btn btn-warning">Manage Inventory</a>
                    </div>
                </div>
            </section>

            <section class="grid mt-3" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="../sales/create.php" class="btn btn-primary">Record Sale</a>
                <a href="../inventory/create.php" class="btn btn-success">Add Product</a>
                <a href="../investments/create.php" class="btn btn-warning">Add Investment</a>
            </section>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get the current business ID
        const businessId = <?= json_encode($current_business_id) ?>;
        
        // Only initialize charts if we have a business selected
        if (businessId) {
            // Fetch monthly sales data for the last 6 months for the sales chart
            fetch('<?= BASE_URL ?>/api/v1/sales?business_id=' + businessId + '&from=' + getDateSixMonthsAgo() + '&to=' + getTodayDate())
                .then(response => {
                    // Check for authorization errors - silently skip if API auth fails
                    if (response.status === 401) {
                        console.log('Skipping sales chart (API auth not configured)');
                        return null;
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data || !data.data) return;
                    
                    // Aggregate sales by month
                    const monthlyData = aggregateSalesByMonth(data.data);
                    if (monthlyData.labels.length > 0) {
                        initSalesChart(monthlyData.labels, monthlyData.values);
                    }
                })
                .catch(err => console.log('Sales chart data skipped:', err));
        }
    });
    
    // Helper functions for date calculations
    function getTodayDate() {
        return new Date().toISOString().split('T')[0];
    }
    
    function getDateSixMonthsAgo() {
        const date = new Date();
        date.setMonth(date.getMonth() - 6);
        return date.toISOString().split('T')[0];
    }
    
    function aggregateSalesByMonth(sales) {
        const monthlyMap = {};
        
        sales.forEach(sale => {
            const date = new Date(sale.created_at);
            const monthKey = date.toLocaleString('en-US', { year: 'numeric', month: 'short' });
            
            if (!monthlyMap[monthKey]) {
                monthlyMap[monthKey] = 0;
            }
            monthlyMap[monthKey] += parseFloat(sale.total_amount || 0);
        });
        
        return {
            labels: Object.keys(monthlyMap),
            values: Object.values(monthlyMap)
        };
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php';
