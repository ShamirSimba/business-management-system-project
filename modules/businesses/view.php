<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../classes/Profit.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Business Overview';
$businessModel = new Business($conn);
$profitModel = new Profit($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$business = $businessModel->getById($id);
if (!$business) {
	header('Location: index.php');
	exit;
}
$summary = $businessModel->getSummary($id);
$profit = $profitModel->calculate($id, date('Y-01-01'), date('Y-12-31'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
		<div class="container">
			<div class="flex justify-between align-center mb-3">
				<h2><?= htmlspecialchars($business['name']) ?> <span class="badge" style="background:#eaf6ff; color:var(--color-primary); font-size:1rem; margin-left:1rem;"> <?= ucfirst($business['type']) ?> </span></h2>
				<a href="edit.php?id=<?= $business['id'] ?>" class="btn btn-success">Edit</a>
			</div>
			<div class="mb-2 text-muted">Created: <?= date('Y-m-d', strtotime($business['created_at'])) ?></div>
			<div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
				<div class="kpi-card">
					<div class="kpi-label">Total Capital</div>
					<div class="kpi-value">TZS <?= number_format($summary['total_investments'], 2) ?></div>
				</div>
				<div class="kpi-card">
					<div class="kpi-label">Total Sales</div>
					<div class="kpi-value">TZS <?= number_format($summary['total_sales'], 2) ?></div>
				</div>
				<div class="kpi-card">
					<div class="kpi-label">Net Profit</div>
					<div class="kpi-value">TZS <?= number_format($profit['net_profit'], 2) ?></div>
				</div>
			</div>
			<div class="card mt-3">
				<h3>Recent Activity</h3>
				<ul class="mt-2">
					<li class="text-muted">(Recent activity list placeholder)</li>
				</ul>
			</div>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>