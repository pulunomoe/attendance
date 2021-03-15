<?php

session_start();

use Pulunomoe\Attendance\Controller\DepartmentController;
use Pulunomoe\Attendance\Controller\EmployeeController;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$pdo = new PDO(
	'mysql:host=localhost;dbname=attendance;charset=utf8mb4',
	'mysqldev',
	'mysqldev123',
	[
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
	]
);

$renderer = new PhpRenderer(__DIR__ . '/../templates/');
$renderer->setLayout('layout.php');

$departmentController = new DepartmentController($pdo, $renderer);

$app->get('/departments', [$departmentController, 'index']);
$app->get('/departments/view/{id}', [$departmentController, 'view']);
$app->get('/departments/form[/{id}]', [$departmentController, 'form']);
$app->post('/departments/form', [$departmentController, 'formPost']);
$app->post('/departments/delete', [$departmentController, 'deletePost']);

$employeeController = new EmployeeController($pdo, $renderer);

$app->get('/employees', [$employeeController, 'index']);
$app->get('/employees/view/{id}', [$employeeController, 'view']);
$app->get('/employees/form[/{id}]', [$employeeController, 'form']);
$app->post('/employees/form', [$employeeController, 'formPost']);
$app->post('/employees/delete', [$employeeController, 'deletePost']);

$app->run();
