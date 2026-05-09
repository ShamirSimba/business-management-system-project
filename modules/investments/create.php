<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Add Investment/Expense';
$businessModel = new Business($conn);
$businesses = $businessModel->getAll($current_user['id']);
$type = $_GET['type'] ?? 'capital';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
		<div class="container">
			<h2>Add Investment/Expense</h2>
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
			<?php endif; ?>
			<form action="../../handlers/investment_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
				<input type="hidden" name="action" value="create">
				<div class="form-group">
					<label for="business_id">Business</label>
					<select id="business_id" name="business_id" class="form-control" required>
						<?php foreach ($businesses as $biz): ?>
							<option value="<?= $biz['id'] ?>"><?= htmlspecialchars($biz['name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type" class="form-control" required>
						<option value="capital" <?= $type=='capital'?'selected':'' ?>>Capital</option>
						<option value="expense" <?= $type=='expense'?'selected':'' ?>>Expense</option>
					</select>
				</div>
				<div class="form-group">
					<label for="amount">Amount</label>
					<input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
				</div>
				<div class="form-group">
					<label for="note">Note</label>
					<input type="text" id="note" name="note" class="form-control">
				</div>
				<div class="form-group">
					<label for="date">Date</label>
					<input type="date" id="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
				</div>
				<button type="submit" class="btn btn-primary">Add</button>
			</form>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>