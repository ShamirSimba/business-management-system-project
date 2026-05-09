<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Sales History';
$saleModel = new Sale($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$payment_method = $_GET['payment_method'] ?? '';
$sales = $saleModel->getAll($business_id, $from, $to, $payment_method);
$summary = $saleModel->getSummary($business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Sales History</h2>
                <a href="create.php" class="btn btn-primary">Record New Sale</a>
            </div>
            <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                <div class="kpi-card"><div class="kpi-label">Today's Revenue</div><div class="kpi-value">TZS <?= number_format($summary['today'],2) ?></div></div>
                <div class="kpi-card"><div class="kpi-label">This Month</div><div class="kpi-value">TZS <?= number_format($summary['month'],2) ?></div></div>
                <div class="kpi-card"><div class="kpi-label">Total Sales</div><div class="kpi-value"><?= $summary['count'] ?></div></div>
            </div>
            <form method="GET" class="flex gap-1 mb-2">
                <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
                <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
                <select name="payment_method" class="form-control" style="max-width:180px;">
                    <option value="">All Methods</option>
                    <option value="cash" <?= $payment_method=='cash'?'selected':'' ?>>Cash</option>
                    <option value="card" <?= $payment_method=='card'?'selected':'' ?>>Card</option>
                    <option value="mobile" <?= $payment_method=='mobile'?'selected':'' ?>>Mobile</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No sales found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= $sale['id'] ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></td>
                                    <td>
                                        <?php
                                            $stmt_items = $conn->prepare("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = ?");
                                            $stmt_items->bind_param("i", $sale['id']);
                                            $stmt_items->execute();
                                            $items_result = $stmt_items->get_result()->fetch_assoc();
                                            $stmt_items->close();
                                            echo $items_result['count'];
                                        ?>
                                    </td>
                                    <td>TZS <?= number_format($sale['total_amount'], 2) ?></td>
                                    <td><span class="badge badge-info"><?= ucfirst($sale['payment_method']) ?></span></td>
                                    <td><span class="badge <?= $sale['status']=='completed'?'badge-success':'badge-danger' ?>"><?= ucfirst($sale['status']) ?></span></td>
                                    <td>
                                        <a href="view.php?id=<?= $sale['id'] ?>" class="btn btn-success">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>