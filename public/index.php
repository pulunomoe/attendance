<?php

session_start();

use Pulunomoe\Attendance\Controller\DepartmentController;
use Pulunomoe\Attendance\Controller\EmployeeController;
use Pulunomoe\Attendance\Controller\StatusController;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

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

$twig = Twig::create(__DIR__ . '/../templates/');
$app->add(TwigMiddleware::create($app, $twig));

$departmentController = new DepartmentController($pdo);
$employeeController = new EmployeeController($pdo);
$statusController = new StatusController($pdo);

$app->get('/departments', [$departmentController, 'index']);
$app->get('/departments/view/{id}', [$departmentController, 'view']);
$app->get('/departments/form[/{id}]', [$departmentController, 'form']);
$app->post('/departments/form', [$departmentController, 'formPost']);
$app->get('/departments/delete/{id}', [$departmentController, 'delete']);
$app->post('/departments/delete', [$departmentController, 'deletePost']);

$app->get('/employees', [$employeeController, 'index']);
$app->get('/employees/view/{id}', [$employeeController, 'view']);
$app->get('/employees/form[/{id}]', [$employeeController, 'form']);
$app->post('/employees/form', [$employeeController, 'formPost']);
$app->get('/employees/delete/{id}', [$employeeController, 'delete']);
$app->post('/employees/delete', [$employeeController, 'deletePost']);

$app->get('/departments/view/{departmentId}/statuses', [$statusController, 'index']);
$app->get('/departments/view/{departmentId}/statuses/form[/{id}]', [$statusController, 'form']);
$app->post('/statuses/form', [$statusController, 'formPost']);
$app->get('/departments/view/{departmentId}/statuses/delete/{id}', [$statusController, 'delete']);
$app->post('/statuses/delete', [$statusController, 'deletePost']);

$app->run();
