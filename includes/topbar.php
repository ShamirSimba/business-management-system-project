<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}
?>
<div class="topbar">
    <!-- <button id="sidebar-toggle" class="btn btn-primary topbar-hamburger" type="button">☰</button> -->
    <div class="topbar-title"><?= htmlspecialchars($page_title ?? APP_NAME) ?></div>
    <div class="topbar-center">
        <?php if (!empty($businesses) && is_array($businesses)): ?>
            <?php
                $current_path = strtok($_SERVER['REQUEST_URI'], '?');
                $query_params = $_GET;
            ?>
            <select class="business-select" onchange="if(this.value) window.location.href=this.value;">
                <?php foreach ($businesses as $business):
                    $query_params['business_id'] = $business['id'];
                    $url = $current_path . '?' . http_build_query($query_params);
                ?>
                    <option value="<?= htmlspecialchars($url) ?>" <?= isset($current_business_id) && $current_business_id == $business['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($business['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
    <div class="topbar-actions">
        <div class="topbar-user">
            <span class="topbar-user-name"><?= htmlspecialchars($current_user['name'] ?? 'User') ?></span>
            <!-- <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-secondary">Logout</a> -->
        </div>
    </div>
</div>
