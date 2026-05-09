<?php
require_once '../config/database.php';
require_once '../classes/Product.php';
require_once '../api/helpers/validator.php';
require_once '../auth/session.php';
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
        $errors = $validator->required(['name','category','cost_price','selling_price','stock_qty','low_stock_threshold'], $_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ../modules/inventory/create.php');
            exit;
        }
        $data = [
            'business_id' => $business_id,
            'name' => $_POST['name'],
            'category' => $_POST['category'],
            'cost_price' => $_POST['cost_price'],
            'selling_price' => $_POST['selling_price'],
            'stock_qty' => $_POST['stock_qty'],
            'low_stock_threshold' => $_POST['low_stock_threshold']
        ];
        if ($productModel->create($data)) {
            $_SESSION['success'] = 'Product added.';
            header('Location: ../modules/inventory/index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to add product.';
            header('Location: ../modules/inventory/create.php');
            exit;
        }
    } elseif ($action === 'update') {
        $errors = $validator->required(['id','name','category','cost_price','selling_price','low_stock_threshold'], $_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ../modules/inventory/edit.php?id=' . $_POST['id']);
            exit;
        }
        $data = [
            'name' => $_POST['name'],
            'category' => $_POST['category'],
            'cost_price' => $_POST['cost_price'],
            'selling_price' => $_POST['selling_price'],
            'low_stock_threshold' => $_POST['low_stock_threshold']
        ];
        if ($productModel->update($_POST['id'], $data)) {
            $_SESSION['success'] = 'Product updated.';
            header('Location: ../modules/inventory/index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update product.';
            header('Location: ../modules/inventory/edit.php?id=' . $_POST['id']);
            exit;
        }
    } elseif ($action === 'delete') {
        if ($productModel->delete($_POST['id'])) {
            $_SESSION['success'] = 'Product deleted.';
        } else {
            $_SESSION['error'] = 'Failed to delete product.';
        }
        header('Location: ../modules/inventory/index.php');
        exit;
    } elseif ($action === 'adjust_stock') {
        $errors = $validator->required(['id','qty','reason'], $_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ../modules/inventory/edit.php?id=' . $_POST['id']);
            exit;
        }
        if ($productModel->updateStock($_POST['id'], $_POST['qty'], $_POST['reason'])) {
            $_SESSION['success'] = 'Stock adjusted.';
        } else {
            $_SESSION['error'] = 'Failed to adjust stock.';
        }
        header('Location: ../modules/inventory/edit.php?id=' . $_POST['id']);
        exit;
    }
}
header('Location: ../modules/inventory/index.php');
exit;// Inventory handler for BMS