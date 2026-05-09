<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Investment.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Edit Investment/Expense';
$investmentModel = new Investment($conn);
$businessModel = new Business($conn);
$businesses = $businessModel->getAll($current_user['id']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$investment = $investmentModel->getById($id);
if (!$investment) {
	header('Location: index.php');
	exit;
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
	<main class="main-content">
		<?php include_once '../../includes/topbar.php'; ?>
		<div class="container">
			<h2>Edit Investment/Expense</h2>
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
			<?php endif; ?>
			<form action="../../handlers/investment_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
				<input type="hidden" name="action" value="update">
				<input type="hidden" name="id" value="<?= $investment['id'] ?>">
				<div class="form-group">
					<label for="business_id">Business</label>
					<select id="business_id" name="business_id" class="form-control" required>
						<?php foreach ($businesses as $biz): ?>
							<option value="<?= $biz['id'] ?>" <?= $investment['business_id']==$biz['id']?'selected':'' ?>><?= htmlspecialchars($biz['name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type" class="form-control" required>
						<option value="capital" <?= $investment['type']=='capital'?'selected':'' ?>>Capital</option>
						<option value="expense" <?= $investment['type']=='expense'?'selected':'' ?>>Expense</option>
					</select>
				</div>
				<div class="form-group">
					<label for="amount">Amount</label>
					<input type="number" step="0.01" id="amount" name="amount" class="form-control" value="<?= htmlspecialchars($investment['amount']) ?>" required>
				</div>
				<div class="form-group">
					<label for="note">Note</label>
					<input type="text" id="note" name="note" class="form-control" value="<?= htmlspecialchars($investment['note']) ?>">
				</div>
				<div class="form-group">
					<label for="date">Date</label>
					<input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($investment['date']) ?>" required>
				</div>
				<button type="submit" class="btn btn-success">Update</button>
			</form>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>