<?php
require_once '../config/database.php';
require_once '../classes/Sale.php';
require_once '../classes/Product.php';
require_once '../api/helpers/validator.php';
require_once '../auth/session.php';
$saleModel = new Sale($conn);
$productModel = new Product($conn);
$validator = new Validator();

// Get user's business_id
$stmt = $conn->prepare("SELECT id FROM businesses WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();
$business_id = $business['id'] ?? null;
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $cart_json = $_POST['cart_json'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $items = json_decode($cart_json, true);
        if (!is_array($items) || empty($items)) {
            $_SESSION['error'] = 'Cart is empty or invalid.';
            header('Location: ../modules/sales/create.php');
            exit;
        }
        $errors = $validator->required(['payment_method'], $_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ../modules/sales/create.php');
            exit;
        }
        // Validate items
        $valid = true;
        foreach ($items as $item) {
            $prod = $productModel->getById($item['product_id']);
            if (!$prod) {
                $valid = false;
                $_SESSION['error'] = 'Product not found: ' . htmlspecialchars($item['product_id']);
                break;
            }
            if ($prod['stock_qty'] < $item['qty']) {
                $valid = false;
                $_SESSION['error'] = 'Insufficient stock for: ' . htmlspecialchars($prod['name']);
                break;
            }
        }
        if (!$valid) {
            header('Location: ../modules/sales/create.php');
            exit;
        }
        // Prepare sale data
        $sale_data = [
            'business_id' => $business_id,
            'user_id' => $_SESSION['user_id'],
            'payment_method' => $payment_method
        ];
        $new_id = $saleModel->create($sale_data, $items);
        if ($new_id) {
            $_SESSION['success'] = 'Sale recorded.';
            header('Location: ../modules/sales/view.php?id=' . $new_id);
            exit;
        } else {
            $_SESSION['error'] = 'Failed to record sale.';
            header('Location: ../modules/sales/create.php');
            exit;
        }
    }
}
header('Location: ../modules/sales/index.php');
exit;// Sale handler for BMS