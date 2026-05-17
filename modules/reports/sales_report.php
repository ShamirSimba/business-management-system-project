<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Sales Report';
$saleModel = new Sale($conn);
$businessModel = new Business($conn);

// Get all user's businesses
$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);
$business_id = $current_business_id;

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$payment_method = $_GET['payment_method'] ?? '';
$sales = isset($_GET['from']) ? $saleModel->getAll($business_id, $from, $to, $payment_method) : [];
$total = 0;
$total_items = 0;
foreach ($sales as $sale) {
    $total += $sale['total_amount'];
    // Count items per sale
    $stmt_items = $conn->prepare("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = ?");
    $stmt_items->bind_param("i", $sale['id']);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result()->fetch_assoc();
    $stmt_items->close();
    $total_items += $items_result['count'];
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Sales Report</h2>
            <form method="GET" class="card mb-3">
                <input type="hidden" name="business_id" value="<?= $current_business_id ?>">
                <div class="flex gap-1 align-end">
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All</option>
                            <option value="cash" <?= $payment_method=='cash'?'selected':'' ?>>Cash</option>
                            <option value="card" <?= $payment_method=='card'?'selected':'' ?>>Card</option>
                            <option value="mobile" <?= $payment_method=='mobile'?'selected':'' ?>>Mobile</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <?php if (isset($_GET['from'])): ?>
                        <button type="button" class="btn btn-success" onclick="exportPDF('sales')">Export PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportExcel('sales')">Export Excel</button>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($_GET['from']) && !empty($sales)): ?>
                <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="kpi-card"><div class="kpi-label">Total Sales</div><div class="kpi-value"><?= count($sales) ?></div></div>
                    <div class="kpi-card"><div class="kpi-label">Total Items</div><div class="kpi-value"><?= $total_items ?></div></div>
                    <div class="kpi-card"><div class="kpi-label">Total Revenue</div><div class="kpi-value">TZS <?= number_format($total, 2) ?></div></div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sale ID</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['date']) ?></td>
                                    <td><?= $sale['id'] ?></td>
                                    <td><?= $sale['items_count'] ?></td>
                                    <td>TZS <?= number_format($sale['total'], 2) ?></td>
                                    <td><span class="badge badge-info"><?= ucfirst($sale['payment_method']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card mt-2">
                    <b>Total Revenue:</b> <span class="kpi-value">TZS <?= number_format($total, 2) ?></span>
                </div>
            <?php elseif (isset($_GET['from'])): ?>
                <div class="alert alert-info">No sales found for the selected date range.</div>
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
        <input type="hidden" name="from" value="<?= $from ?>">
        <input type="hidden" name="to" value="<?= $to ?>">
        <input type="hidden" name="payment_method" value="<?= $payment_method ?>">
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
        <input type="hidden" name="from" value="<?= $from ?>">
        <input type="hidden" name="to" value="<?= $to ?>">
        <input type="hidden" name="payment_method" value="<?= $payment_method ?>">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>