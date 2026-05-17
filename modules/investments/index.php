<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Investment.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Investments & Expenses';
$businessModel = new Business($conn);
$investmentModel = new Investment($conn);
$businesses = $businessModel->getAll($_SESSION['user_id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);
$type_filter = $_GET['type'] ?? '';
$investments = $current_business_id ? $investmentModel->getAll($current_business_id) : [];
if ($type_filter) {
    $investments = array_filter($investments, function($inv) use ($type_filter) {
        return $inv['type'] === $type_filter;
    });
}
$total_capital = $investmentModel->getTotalCapital($current_business_id);
$total_expenses = $investmentModel->getTotalExpenses($current_business_id);
$net_investment = $total_capital - $total_expenses;
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
		<div class="container">
			<div class="flex justify-between align-center mb-3">
				<h2>Investments & Expenses</h2>
				<div class="flex gap-1">
					<a href="create.php?business_id=<?= $current_business_id ?>&type=capital" class="btn btn-success">Add Investment</a>
					<a href="create.php?business_id=<?= $current_business_id ?>&type=expense" class="btn btn-warning">Add Expense</a>
				</div>
			</div>
			<div class="grid mb-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
				<div class="kpi-card"><div class="kpi-label">Total Capital</div><div class="kpi-value">TZS <?= number_format($total_capital, 2) ?></div></div>
				<div class="kpi-card"><div class="kpi-label">Total Expenses</div><div class="kpi-value">TZS <?= number_format($total_expenses, 2) ?></div></div>
				<div class="kpi-card"><div class="kpi-label">Net Investment</div><div class="kpi-value">TZS <?= number_format($net_investment, 2) ?></div></div>
			</div>
			<div class="flex gap-1 mb-2">
				<a href="?business_id=<?= $current_business_id ?>" class="btn <?= $type_filter==''?'btn-primary':'btn' ?>">All</a>
				<a href="?business_id=<?= $current_business_id ?>&type=capital" class="btn <?= $type_filter=='capital'?'btn-success':'btn' ?>">Capital</a>
				<a href="?business_id=<?= $current_business_id ?>&type=expense" class="btn <?= $type_filter=='expense'?'btn-warning':'btn' ?>">Expense</a>
			</div>
			<div class="table-wrapper">
				<table>
					<thead>
						<tr>
							<th>Date</th>
							<th>Type</th>
							<th>Amount</th>
							<th>Note</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($investments)): ?>
							<tr><td colspan="5" class="text-center text-muted">No investments found.</td></tr>
						<?php else: ?>
							<?php foreach ($investments as $inv): ?>
								<tr>
									<td><?= htmlspecialchars($inv['date']) ?></td>
									<td><span class="badge <?= $inv['type']=='capital'?'badge-success':'badge-warning' ?>"><?= ucfirst($inv['type']) ?></span></td>
									<td>TZS <?= number_format($inv['amount'], 2) ?></td>
									<td><?= htmlspecialchars($inv['note']) ?></td>
									<td>
								<a href="edit.php?id=<?= $inv['id'] ?>&business_id=<?= $current_business_id ?>" class="btn btn-success">Edit</a>
								<form action="../../handlers/investment_handler.php" method="POST" style="display:inline;">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?= $inv['id'] ?>">
									<input type="hidden" name="business_id" value="<?= $current_business_id ?>">
											<button type="submit" class="btn btn-danger" data-confirm-delete>Delete</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>