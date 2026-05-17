<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Sale.php';
require_once __DIR__ . '/../../auth/session.php';
$saleModel = new Sale($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sale = $saleModel->getById($id);
if (!$sale) {
    die('Sale not found.');
}
$items = $sale['items'] ?? [];

// Get business name and user info
$stmt = $conn->prepare("SELECT b.name FROM businesses b JOIN sales s ON b.id = s.business_id WHERE s.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$business_result = $stmt->get_result()->fetch_assoc();
$business_name = $business_result['name'] ?? APP_NAME;
$stmt->close();

require_once __DIR__ . '/../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?= $sale['id'] ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { background: #fff; color: #222; }
        .receipt-box { max-width: 400px; margin: 30px auto; padding: 20px; border: 1px solid #eee; background: #fff; }
        .receipt-box h2 { text-align: center; margin-bottom: 10px; }
        .receipt-box table { width: 100%; margin-bottom: 10px; }
        .receipt-box th, .receipt-box td { padding: 4px; text-align: left; }
        .receipt-box .total { font-weight: bold; border-top: 1px solid #ccc; }
        .receipt-box .thankyou { text-align: center; margin-top: 20px; font-size: 1.1em; }
        .print-btn { display: block; margin: 10px auto; }
    </style>
</head>
<body>
<div class="receipt-box">
    <h2><?= htmlspecialchars($business_name) ?></h2>
    <div>Date: <?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></div>
    <div>Receipt #: <?= $sale['id'] ?></div>
    <table>
        <thead>
            <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td>TZS <?= number_format($item['unit_price'],2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">Total: TZS <?= number_format($sale['total_amount'],2) ?></div>
    <div>Payment: <?= ucfirst($sale['payment_method']) ?></div>
    <div class="thankyou">Thank you for your purchase!</div>
    <button class="btn btn-primary print-btn" onclick="window.print()">Print</button>
</div>
</body>
</html>// Sales receipt page for BMS