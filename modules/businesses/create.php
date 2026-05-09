<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Add New Business';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
		<div class="container">
			<h2>Add New Business</h2>
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
			<?php endif; ?>
			<form action="../../handlers/business_handler.php" method="POST" class="card" style="max-width:500px; margin:auto;">
				<input type="hidden" name="action" value="create">
				<div class="form-group">
					<label for="name">Business Name</label>
					<input type="text" id="name" name="name" class="form-control" required>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type" class="form-control" required>
						<option value="retail">Retail</option>
						<option value="wholesale">Wholesale</option>
						<option value="service">Service</option>
						<option value="other">Other</option>
					</select>
				</div>
				<div class="form-group">
					<label for="description">Description</label>
					<textarea id="description" name="description" class="form-control"></textarea>
				</div>
				<div class="form-group">
					<label for="status">Status</label>
					<select id="status" name="status" class="form-control" required>
						<option value="active">Active</option>
						<option value="inactive">Inactive</option>
					</select>
				</div>
				<button type="submit" class="btn btn-primary">Create Business</button>
			</form>
		</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>