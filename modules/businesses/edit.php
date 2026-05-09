<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Edit Business';
$businessModel = new Business($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$business = $businessModel->getById($id);
if (!$business) {
	header('Location: index.php');
	exit;
}
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
		<div class="container">
			<h2>Edit Business</h2>
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
			<?php endif; ?>
			<form action="../../handlers/business_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
				<input type="hidden" name="action" value="update">
				<input type="hidden" name="id" value="<?= $business['id'] ?>">
				<div class="form-group">
					<label for="name">Business Name</label> 
					<input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($business['name']) ?>" required>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type" class="form-control" required>
						<option value="retail" <?= $business['type']=='retail'?'selected':'' ?>>Retail</option>
						<option value="wholesale" <?= $business['type']=='wholesale'?'selected':'' ?>>Wholesale</option>
						<option value="service" <?= $business['type']=='service'?'selected':'' ?>>Service</option>
						<option value="other" <?= $business['type']=='other'?'selected':'' ?>>Other</option>
					</select>
				</div>
				<div class="form-group">
					<label for="description">Description</label>
					<textarea id="description" name="description" class="form-control"><?= htmlspecialchars($business['description']) ?></textarea>
				</div>
				<div class="form-group">
					<label for="status">Status</label>
					<select id="status" name="status" class="form-control" required>
						<option value="active" <?= $business['status']=='active'?'selected':'' ?>>Active</option>
						<option value="inactive" <?= $business['status']=='inactive'?'selected':'' ?>>Inactive</option>
					</select>
				</div>
				<button type="submit" class="btn btn-success">Update Business</button>
			</form>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>