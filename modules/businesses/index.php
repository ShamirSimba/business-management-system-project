<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'My Businesses';
$businessModel = new Business($conn);
$businesses = $businessModel->getAll($_SESSION['user_id']);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
		<div class="container">
			<div class="flex justify-between align-center mb-3">
				<h2>My Businesses</h2>
				<a href="create.php" class="btn btn-primary">Add New Business</a>
			</div>
			<?php if (empty($businesses)): ?>
				<div class="card text-center mt-4">You have not added any businesses yet.</div>
			<?php else: ?>
				<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
					<?php foreach ($businesses as $biz):
						$summary = $businessModel->getSummary($biz['id']);
						$typeClass = 'badge';
						$statusClass = $biz['status'] === 'active' ? 'badge-success' : 'badge-warning';
					?>
					<div class="card">
						<div class="flex justify-between align-center mb-1">
							<h3><?= htmlspecialchars($biz['name']) ?></h3>
							<span class="badge" style="background: #eaf6ff; color: var(--color-primary);">
								<?= ucfirst($biz['type']) ?>
							</span>
						</div>
						<div class="mb-1">
							<span class="badge <?= $statusClass ?>"><?= ucfirst($biz['status']) ?></span>
						</div>
						<div class="flex justify-between align-center mt-2 mb-2">
							<div>
								<div class="text-muted" style="font-size:0.95rem;">Total Investment</div>
								<div><strong>TZS <?= number_format($summary['total_investments'], 2) ?></strong></div>
							</div>
							<div>
								<div class="text-muted" style="font-size:0.95rem;">Total Sales</div>
								<div><strong>TZS <?= number_format($summary['total_sales'], 2) ?></strong></div>
							</div>
						</div>
						<div class="flex gap-1 mt-2">
							<a href="view.php?id=<?= $biz['id'] ?>" class="btn btn-primary">View</a>
							<a href="edit.php?id=<?= $biz['id'] ?>" class="btn btn-success">Edit</a>
							<form action="../../handlers/business_handler.php" method="POST" style="display:inline;">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?= $biz['id'] ?>">
								<button type="submit" class="btn btn-danger" data-confirm-delete>Delete</button>
							</form>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>