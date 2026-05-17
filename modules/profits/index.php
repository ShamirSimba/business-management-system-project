<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Profit.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Profit & Analytics';
$profitModel = new Profit($conn);

// Get businesses for current user
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$default_business_id = $business['id'] ?? null;
$stmt->close();

// Get business_id from URL parameter, or use default
$business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : $default_business_id;

$year = $_GET['year'] ?? date('Y');
$from = "$year-01-01";
$to = "$year-12-31";
$profit_data = $profitModel->calculate($business_id, $from, $to);
$monthly = $profitModel->getMonthlyBreakdown($business_id, $year);
include_once '../../includes/header.php';
include_once '../../includes/layout-start.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Profit & Analytics</h2>
                <div class="flex gap-1">
                    <select id="year-select" class="form-control" style="max-width:120px;">
                        <?php for ($y = date('Y')-5; $y <= date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                <div class="kpi-card"><div class="kpi-label">Total Revenue</div><div class="kpi-value">TZS <?= number_format($profit_data['revenue'],2) ?></div></div>
                <div class="kpi-card"><div class="kpi-label">Total COGS</div><div class="kpi-value">TZS <?= number_format($profit_data['cogs'],2) ?></div></div>
                <div class="kpi-card"><div class="kpi-label">Total Expenses</div><div class="kpi-value">TZS <?= number_format($profit_data['expenses'],2) ?></div></div>
                <div class="kpi-card" style="background: linear-gradient(135deg, #10b981, #059669);"><div class="kpi-label" style="color:#fff;">Net Profit</div><div class="kpi-value" style="color:#fff;">TZS <?= number_format($profit_data['net_profit'],2) ?></div></div>
            </div>
            <div class="card mb-3">
                <h3>Profit Formula</h3>
                <p><b>Revenue</b> − <b>COGS</b> − <b>Expenses</b> = <b>Net Profit</b></p>
                <p class="text-muted">TZS <?= number_format($profit_data['revenue'],2) ?> − TZS <?= number_format($profit_data['cogs'],2) ?> − TZS <?= number_format($profit_data['expenses'],2) ?> = <span style="color:#10b981; font-weight:bold;">TZS <?= number_format($profit_data['net_profit'],2) ?></span></p>
            </div>
            <div class="flex gap-2 mb-3">
                <div class="card" style="flex:1;">
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
                                <?php if (empty($monthly)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No data available for this year.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($monthly as $m): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($m['month_name']) ?></td>
                                            <td>TZS <?= number_format($m['revenue'], 2) ?></td>
                                            <td>TZS <?= number_format($m['cogs'], 2) ?></td>
                                            <td>TZS <?= number_format($m['expenses'], 2) ?></td>
                                            <td>TZS <?= number_format($m['gross_profit'], 2) ?></td>
                                            <td style="color:<?= $m['net_profit'] >= 0 ? '#10b981' : '#ef4444' ?>; font-weight:bold;">TZS <?= number_format($m['net_profit'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <div class="card" style="flex:1;">
                    <h3>Yearly Profit Trend</h3>
                    <div style="position: relative; height: 300px;">
                        <canvas id="profit-chart"></canvas>
                    </div>
                </div>
                <div class="card" style="flex:1;">
                    <h3>Revenue vs Expenses</h3>
                    <div style="position: relative; height: 300px;">
                        <canvas id="comparison-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('year-select').addEventListener('change', function() {
    window.location.href = '?business_id=<?= $business_id ?>&year=' + this.value;
});

// Profit trend data
const profitData = <?= json_encode(array_column($monthly, 'net_profit')) ?>;
const monthLabels = <?= json_encode(array_column($monthly, 'month_short')) ?>;
const ctx1 = document.getElementById('profit-chart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Net Profit',
            data: profitData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Revenue vs Expenses
const revenueData = <?= json_encode(array_column($monthly, 'revenue')) ?>;
const expensesData = <?= json_encode(array_column($monthly, 'expenses')) ?>;
const ctx2 = document.getElementById('comparison-chart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [
            { label: 'Revenue', data: revenueData, backgroundColor: '#3b82f6' },
            { label: 'Expenses', data: expensesData, backgroundColor: '#ef4444' }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php include_once '../../includes/footer.php'; ?>