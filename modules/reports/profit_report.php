<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Profit.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Profit Report';
$profitModel = new Profit($conn);

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$profit_data = isset($_GET['from']) ? $profitModel->calculate($business_id, $from, $to) : null;
$monthly = isset($_GET['from']) ? [] : [];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
        <div class="container">
            <h2>Profit Report</h2>
            <form method="GET" class="card mb-3">
                <div class="flex gap-1 align-end">
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <?php if (isset($_GET['from'])): ?>
                        <button type="button" class="btn btn-success" onclick="exportPDF('profit')">Export PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportExcel('profit')">Export Excel</button>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($_GET['from']) && $profit_data): ?>
                <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="kpi-card"><div class="kpi-label">Revenue</div><div class="kpi-value">TZS <?= number_format($profit_data['revenue'], 2) ?></div></div>
                    <div class="kpi-card"><div class="kpi-label">COGS</div><div class="kpi-value">TZS <?= number_format($profit_data['cogs'], 2) ?></div></div>
                    <div class="kpi-card"><div class="kpi-label">Expenses</div><div class="kpi-value">TZS <?= number_format($profit_data['expenses'], 2) ?></div></div>
                    <div class="kpi-card"><div class="kpi-label">Gross Profit</div><div class="kpi-value">TZS <?= number_format($profit_data['gross_profit'], 2) ?></div></div>
                    <div class="kpi-card" style="background: linear-gradient(135deg, #10b981, #059669);"><div class="kpi-label" style="color:#fff;">Net Profit</div><div class="kpi-value" style="color:#fff;">TZS <?= number_format($profit_data['net_profit'], 2) ?></div></div>
                </div>
                <div class="card">
                    <h3>Monthly Breakdown</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Revenue</th>
                                    <th>COGS</th>
                                    <th>Expenses</th>
                                    <th>Gross Profit</th>
                                    <th>Net Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['month_name']) ?></td>
                                        <td>TZS <?= number_format($m['revenue'], 2) ?></td>
                                        <td>TZS <?= number_format($m['cogs'], 2) ?></td>
                                        <td>TZS <?= number_format($m['expenses'], 2) ?></td>
                                        <td>TZS <?= number_format($m['gross_profit'], 2) ?></td>
                                        <td style="color:<?= $m['net_profit']>=0?'#10b981':'#ef4444' ?>; font-weight:bold;">TZS <?= number_format($m['net_profit'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif (isset($_GET['from'])): ?>
                <div class="alert alert-info">No data found for the selected date range.</div>
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
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>