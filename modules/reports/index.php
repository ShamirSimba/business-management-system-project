<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Reports';

// Get all user's businesses
$businessModel = new Business($conn);
$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
        <div class="container">
            <h2>Reports</h2>
            <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <h3>Sales Report</h3>
                    <p class="text-muted">View detailed sales history, breakdown by payment method, and summary statistics.</p>
                    <a href="sales_report.php?business_id=<?= $current_business_id ?>" class="btn btn-primary">Generate Report</a>
                </div>
                <div class="card">
                    <h3>Inventory Report</h3>
                    <p class="text-muted">Track product inventory levels, stock values, and identify slow-moving items.</p>
                    <a href="inventory_report.php?business_id=<?= $current_business_id ?>" class="btn btn-primary">Generate Report</a>
                </div>
                <div class="card">
                    <h3>Profit Report</h3>
                    <p class="text-muted">Analyze revenue, COGS, expenses, and profitability trends with detailed month-by-month breakdown.</p>
                    <a href="profit_report.php?business_id=<?= $current_business_id ?>" class="btn btn-primary">Generate Report</a>
                </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>