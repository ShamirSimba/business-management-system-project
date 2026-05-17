<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}
$base_path = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '';

// Build navigation items with business_id parameter if available
$bid_param = (isset($current_business_id) && $current_business_id) ? '?business_id=' . $current_business_id : '';
$nav_items = [
    ['label' => 'Dashboard', 'href' => BASE_URL . '/modules/dashboard/index.php' . $bid_param, 'pattern' => '/modules/dashboard/', 'icon' => 'ri-dashboard-line'],
    ['label' => 'Businesses', 'href' => BASE_URL . '/modules/businesses/index.php', 'pattern' => '/modules/businesses/', 'icon' => 'ri-briefcase-line'],
    ['label' => 'Investments', 'href' => BASE_URL . '/modules/investments/index.php' . $bid_param, 'pattern' => '/modules/investments/', 'icon' => 'ri-line-chart-line'],
    ['label' => 'Inventory', 'href' => BASE_URL . '/modules/inventory/index.php' . $bid_param, 'pattern' => '/modules/inventory/', 'icon' => 'ri-box-3-line'],
    ['label' => 'Sales', 'href' => BASE_URL . '/modules/sales/index.php' . $bid_param, 'pattern' => '/modules/sales/', 'icon' => 'ri-shopping-cart-line'],
    ['label' => 'Profits', 'href' => BASE_URL . '/modules/profits/index.php' . $bid_param, 'pattern' => '/modules/profits/', 'icon' => 'ri-money-dollar-circle-line'],
    ['label' => 'Reports', 'href' => BASE_URL . '/modules/reports/index.php' . $bid_param, 'pattern' => '/modules/reports/', 'icon' => 'ri-file-chart-line'],
];
?>
<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="ri-bar-chart-2-line"></i>
            </div>
            <div class="sidebar-brand-text"><?= APP_NAME ?></div>
        </div>
<!-- top -->
<div class="topbar">
    <div class="" ><p>Business</p></div>
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
        </div>
    </div>
</div>

<!-- topend -->

        <nav class="sidebar-nav">
            <?php foreach ($nav_items as $item):
                $active = strpos($current_uri, $base_path . $item['pattern']) !== false ? 'active' : '';
            ?>
                <a href="<?= $item['href'] ?>" class="sidebar-nav-link <?= $active ?>">
                    <span class="sidebar-item-icon"><i class="<?= $item['icon'] ?>"></i></span>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="sidebar-footer">
         <div class="sidebar-user-email"><?= htmlspecialchars($current_user['email'] ?? 'Guest') ?></div> 
         <!-- <div class="sidebar-user-role"><?= htmlspecialchars($current_user['role'] ?? '') ?></div>  -->
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-secondary">Logout</a>
    </div>
</aside>
<div class="main-content">
