<?php

session_start();

use Pulunomoe\Attendance\Controller\AssignmentController;
use Pulunomoe\Attendance\Controller\AttendanceController;
use Pulunomoe\Attendance\Controller\AuthenticationController;
use Pulunomoe\Attendance\Controller\DashboardController;
use Pulunomoe\Attendance\Controller\DepartmentController;
use Pulunomoe\Attendance\Controller\EmployeeController;
use Pulunomoe\Attendance\Controller\HolidayController;
use Pulunomoe\Attendance\Controller\StatusController;
use Pulunomoe\Attendance\Middleware\AuthenticationMiddleware;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;

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

$twig = Twig::create(__DIR__ . '/../templates/', ['debug' => true]);
$twig->addExtension(new DebugExtension());
$twig->getEnvironment()->addGlobal('session', $_SESSION);
$app->add(TwigMiddleware::create($app, $twig));

$authenticationController = new AuthenticationController($pdo);
$dashboardController = new DashboardController($pdo);
$departmentController = new DepartmentController($pdo);
$employeeController = new EmployeeController($pdo);
$statusController = new StatusController($pdo);
$holidayController = new HolidayController($pdo);
$assignmentController = new AssignmentController($pdo);
$attendanceController = new AttendanceController($pdo);

$everyone = new AuthenticationMiddleware(AuthenticationMiddleware::RULE_EVERYONE);
$admin = new AuthenticationMiddleware(AuthenticationMiddleware::RULE_ADMIN);

$app->get('/login', [$authenticationController, 'login']);
$app->post('/login', [$authenticationController, 'loginPost']);
$app->get('/logout', [$authenticationController, 'logout']);

$app->get('/', [$dashboardController, 'dashboard'])->add($everyone);

$app->get('/departments', [$departmentController, 'index'])->add($admin);
$app->get('/departments/view/{id}', [$departmentController, 'view'])->add($everyone);
$app->get('/departments/form[/{id}]', [$departmentController, 'form'])->add($admin);
$app->post('/departments/form', [$departmentController, 'formPost'])->add($admin);
$app->get('/departments/delete/{id}', [$departmentController, 'delete'])->add($admin);
$app->post('/departments/delete', [$departmentController, 'deletePost'])->add($admin);

$app->get('/employees', [$employeeController, 'index'])->add($admin);
$app->get('/employees/view/{id}', [$employeeController, 'view'])->add($admin);
$app->get('/employees/form[/{id}]', [$employeeController, 'form'])->add($admin);
$app->post('/employees/form', [$employeeController, 'formPost'])->add($admin);
$app->get('/employees/delete/{id}', [$employeeController, 'delete'])->add($admin);
$app->post('/employees/delete', [$employeeController, 'deletePost'])->add($admin);

$app->get('/departments/view/{departmentId}/statuses', [$statusController, 'index'])->add($everyone);
$app->get('/departments/view/{departmentId}/statuses/form[/{id}]', [$statusController, 'form'])->add($everyone);
$app->post('/statuses/form', [$statusController, 'formPost'])->add($everyone);
$app->get('/departments/view/{departmentId}/statuses/delete/{id}', [$statusController, 'delete'])->add($everyone);
$app->post('/statuses/delete', [$statusController, 'deletePost'])->add($everyone);

$app->get('/departments/view/{departmentId}/holidays', [$holidayController, 'index'])->add($everyone);
$app->get('/departments/view/{departmentId}/holidays/form[/{id}]', [$holidayController, 'form'])->add($everyone);
$app->post('/holidays/form', [$holidayController, 'formPost'])->add($everyone);
$app->get('/departments/view/{departmentId}/holidays/delete/{id}', [$holidayController, 'delete'])->add($everyone);
$app->post('/holidays/delete', [$holidayController, 'deletePost'])->add($everyone);

$app->get('/departments/view/{departmentId}/assignments', [$assignmentController, 'indexFromDepartment'])->add($everyone);
$app->get('/employees/view/{employeeId}/assignments', [$assignmentController, 'indexFromEmployee'])->add($admin);
$app->get('/departments/view/{departmentId}/assignments/view/{id}', [$assignmentController, 'view'])->add($everyone);
$app->get('/departments/view/{departmentId}/assignments/form[/{id}]', [$assignmentController, 'formFromDepartment'])->add($admin);
$app->get('/employees/view/{employeeId}/assignments/form[/{id}]', [$assignmentController, 'formFromEmployee'])->add($admin);
$app->post('/assignments/form', [$assignmentController, 'formPost'])->add($admin);
$app->get('/departments/view/{departmentId}/assignments/delete/{id}', [$assignmentController, 'deleteFromDepartment'])->add($admin);
$app->get('/employees/view/{employeeId}/assignments/delete/{id}', [$assignmentController, 'deleteFromEmployee'])->add($admin);
$app->post('/assignments/delete', [$assignmentController, 'deletePost'])->add($admin);

$app->get('/departments/view/{departmentId}/assignments/report', [$assignmentController, 'report'])->add($everyone);
$app->post('/assignments/report', [$assignmentController, 'reportPost'])->add($everyone);

$app->get('/departments/view/{departmentId}/assignments/view/{assignmentId}/attendances/form[/{id}]', [$attendanceController, 'form'])->add($everyone);
$app->post('/attendances/form', [$attendanceController, 'formPost'])->add($everyone);
$app->get('/departments/view/{departmentId}/assignments/view/{assignmentId}/attendances/delete/{id}', [$attendanceController, 'delete'])->add($everyone);
$app->post('/attendances/delete', [$attendanceController, 'deletePost'])->add($everyone);

$app->run();
