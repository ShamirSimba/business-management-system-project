<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Sale Details';
$saleModel = new Sale($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sale = $saleModel->getById($id);
if (!$sale) {
    header('Location: index.php');
    exit;
}
$items = $sale['items'] ?? [];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Sale #<?= $sale['id'] ?></h2>
            <div class="mb-2">
                <b>Date:</b> <?= date('M d, Y H:i', strtotime($sale['created_at'])) ?> &nbsp;|
                <b>Payment:</b> <span class="badge badge-info"><?= ucfirst($sale['payment_method']) ?></span> &nbsp;|
                <b>Status:</b> <span class="badge <?= $sale['status']=='completed'?'badge-success':'badge-danger' ?>"><?= ucfirst($sale['status']) ?></span>
            </div>
            <div class="kpi-card mb-2"><b>Total Amount:</b> TZS <?= number_format($sale['total_amount'], 2) ?></div>
            <div class="table-wrapper mb-2">
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
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= $item['qty'] ?></td>
                                <td>TZS <?= number_format($item['unit_price'],2) ?></td>
                                <td>TZS <?= number_format($item['subtotal'],2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mb-2">
                <b>Total Amount:</b> <span class="kpi-value">TZS <?= number_format($sale['total'],2) ?></span>
            </div>
            <a href="receipt.php?id=<?= $sale['id'] ?>" class="btn btn-primary" target="_blank">Print Receipt</a>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>// View sale page for BMS