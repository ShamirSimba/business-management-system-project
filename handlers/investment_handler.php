<?php
require_once '../config/database.php';
require_once '../classes/Investment.php';
require_once '../api/helpers/validator.php';
session_start();
$investmentModel = new Investment($conn);
$validator = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$errors = $validator->required(['business_id', 'type', 'amount', 'date'], $_POST);
		if (!empty($errors)) {
			$_SESSION['error'] = implode('<br>', $errors);
			$bid = $_POST['business_id'] ?? '';
			header('Location: ../modules/investments/create.php?business_id=' . $bid);
			exit;
		}
		$business_id = intval($_POST['business_id']);
		$data = [
			'business_id' => $business_id,
			'amount' => $_POST['amount'],
			'type' => $_POST['type'],
			'note' => $_POST['note'] ?? '',
			'date' => $_POST['date']
		];
		if ($investmentModel->create($data)) {
			$_SESSION['success'] = 'Investment/Expense added.';
			header('Location: ../modules/investments/index.php?business_id=' . $business_id);
			exit;
		} else {
			$_SESSION['error'] = 'Failed to add investment/expense.';
			header('Location: ../modules/investments/create.php?business_id=' . $business_id);
			exit;
		}
	} elseif ($action === 'update') {
		$errors = $validator->required(['id', 'business_id', 'type', 'amount', 'date'], $_POST);
		if (!empty($errors)) {
			$_SESSION['error'] = implode('<br>', $errors);
			$bid = $_POST['business_id'] ?? '';
			header('Location: ../modules/investments/edit.php?id=' . $_POST['id'] . '&business_id=' . $bid);
			exit;
		}
		$business_id = intval($_POST['business_id']);
		$data = [
			'business_id' => $business_id,
			'amount' => $_POST['amount'],
			'type' => $_POST['type'],
			'note' => $_POST['note'] ?? '',
			'date' => $_POST['date']
		];
		if ($investmentModel->update($_POST['id'], $data)) {
			$_SESSION['success'] = 'Investment/Expense updated.';
			header('Location: ../modules/investments/index.php?business_id=' . $business_id);
			exit;
		} else {
			$_SESSION['error'] = 'Failed to update investment/expense.';
			header('Location: ../modules/investments/edit.php?id=' . $_POST['id'] . '&business_id=' . $business_id);
			exit;
		}
	} elseif ($action === 'delete') {
		$business_id = intval($_POST['business_id'] ?? 0);
		if ($investmentModel->delete($_POST['id'])) {
			$_SESSION['success'] = 'Investment/Expense deleted.';
		} else {
			$_SESSION['error'] = 'Failed to delete investment/expense.';
		}
		header('Location: ../modules/investments/index.php?business_id=' . $business_id);
		exit;
	}
}
header('Location: ../modules/investments/index.php');
exit;