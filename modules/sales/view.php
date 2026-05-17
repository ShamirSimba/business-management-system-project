<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Sale Details';
$saleModel = new Sale($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : null;
$sale = $saleModel->getById($id);
if (!$sale) {
    $bid_param = $current_business_id ? '?business_id=' . $current_business_id : '';
    header('Location: index.php' . $bid_param);
    exit;
}
$items = $sale['items'] ?? [];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Sale #<?= $sale['id'] ?></h2>
                <a href="index.php?business_id=<?= $current_business_id ?>" class="btn btn-secondary">Back to Sales</a>
            </div>
            
            <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                <div class="kpi-card">
                    <div class="kpi-label">Date & Time</div>
                    <div class="kpi-value" style="font-size: 0.95rem;"><?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Payment Method</div>
                    <div class="kpi-value" style="font-size: 0.95rem;"><span class="badge badge-info"><?= ucfirst($sale['payment_method']) ?></span></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Status</div>
                    <div class="kpi-value" style="font-size: 0.95rem;"><span class="badge <?= $sale['status']=='completed'?'badge-success':'badge-danger' ?>"><?= ucfirst($sale['status']) ?></span></div>
                </div>
                <div class="kpi-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="kpi-label" style="color: #fff;">Total Amount</div>
                    <div class="kpi-value" style="color: #fff;">TZS <?= number_format($sale['total_amount'], 2) ?></div>
                </div>
            </div>
            
            <div class="card">
                <h3>Sale Items</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No items in this sale</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= $item['qty'] ?></td>
                                        <td>TZS <?= number_format($item['unit_price'],2) ?></td>
                                        <td>TZS <?= number_format($item['subtotal'],2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="receipt.php?id=<?= $sale['id'] ?>" class="btn btn-primary" target="_blank">Print Receipt</a>
                <a href="index.php?business_id=<?= $current_business_id ?>" class="btn btn-secondary">Back to Sales</a>
            </div>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// View sale page for BMS