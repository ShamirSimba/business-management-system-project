<?php
require_once '../config/database.php';
require_once '../classes/Business.php';
require_once '../api/helpers/validator.php';
session_start();
$businessModel = new Business($conn);
$validator = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$errors = $validator->required(['name', 'type', 'status'], $_POST);
		if (!empty($errors)) {
			$_SESSION['error'] = implode('<br>', $errors);
			header('Location: ../modules/businesses/create.php');
			exit;
		}
		$data = [
			'user_id' => $_SESSION['user_id'],
			'name' => $_POST['name'],
			'type' => $_POST['type'],
			'description' => $_POST['description'] ?? '',
			'status' => $_POST['status']
		];
		if ($businessModel->create($data)) {
			$_SESSION['success'] = 'Business created successfully.';
			header('Location: ../modules/businesses/index.php');
			exit;
		} else {
			$_SESSION['error'] = 'Failed to create business.';
			header('Location: ../modules/businesses/create.php');
			exit;
		}
	} elseif ($action === 'update') {
		$errors = $validator->required(['id', 'name', 'type', 'status'], $_POST);
		if (!empty($errors)) {
			$_SESSION['error'] = implode('<br>', $errors);
			header('Location: ../modules/businesses/edit.php?id=' . $_POST['id']);
			exit;
		}
		$data = [
			'name' => $_POST['name'],
			'type' => $_POST['type'],
			'description' => $_POST['description'] ?? '',
			'status' => $_POST['status']
		];
		if ($businessModel->update($_POST['id'], $data)) {
			$_SESSION['success'] = 'Business updated successfully.';
			header('Location: ../modules/businesses/index.php');
			exit;
		} else {
			$_SESSION['error'] = 'Failed to update business.';
			header('Location: ../modules/businesses/edit.php?id=' . $_POST['id']);
			exit;
		}
	} elseif ($action === 'delete') {
		if ($businessModel->delete($_POST['id'])) {
			$_SESSION['success'] = 'Business deleted.';
		} else {
			$_SESSION['error'] = 'Failed to delete business.';
		}
		header('Location: ../modules/businesses/index.php');
		exit;
	}
}
header('Location: ../modules/businesses/index.php');
exit;