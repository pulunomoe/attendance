<?php

namespace Pulunomoe\Attendance\Controller;

use Cassandra\Date;
use DateTime;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Middleware\AuthenticationMiddleware;
use Pulunomoe\Attendance\Model\AttendanceModel;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\AssignmentModel;
use Pulunomoe\Attendance\Model\EmployeeModel;
use Pulunomoe\Attendance\Model\StatusModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AssignmentController extends Controller
{
	private DepartmentModel $departmentModel;
	private EmployeeModel $employeeModel;
	private AssignmentModel $assignmentModel;
	private AttendanceModel $attendanceModel;
	private StatusModel $statusModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
		$this->assignmentModel = new AssignmentModel($pdo);
		$this->attendanceModel = new AttendanceModel($pdo);
		$this->statusModel = new StatusModel($pdo);
	}

	////////////////////////////////////////////////////////////////////////////

	public function indexFromDepartment(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		return $this->index($request, $response, 'department', $args['departmentId']);
	}

	public function indexFromEmployee(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		return $this->index($request, $response, 'employee', $args['employeeId']);
	}

	private function index(ServerRequest $request, Response $response, string $parent, int $parentId): ResponseInterface
	{
		$parentModel = $parent == 'department' ? 'departmentModel' : 'employeeModel';

		$$parent = $this->$parentModel->findOne($parentId);
		if (empty($$parent)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $$parent);

		if ($parent == 'department') {
			$assignment = $this->assignmentModel->findAllByDepartment($parentId);
		} else {
			$assignment = $this->assignmentModel->findAllByEmployee($parentId);
		}

		return $this->render($request, $response, 'assignments/index.twig', [
			'parent' => $parent,
			$parent => $$parent,
			'assignments' => $assignment,
			'success' => $this->getFlash('success')
		]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function view(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];
		$id = $args['id'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		$assignment = $this->assignmentModel->findOne($id);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'assignments/view.twig', [
			'department' => $department,
			'assignment' => $assignment,
			'attendances' => $this->attendanceModel->findAllByAssignment($args['id']),
			'success' => $this->getFlash('success')
		]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function formFromDepartment(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'] ?? null;
		return $this->form($request, $response, 'department', $args['departmentId'], $id);
	}

	public function formFromEmployee(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'] ?? null;
		return $this->form($request, $response, 'employee', $args['employeeId'], $id);
	}

	private function form(ServerRequest $request, Response $response, string $parent, int $parentId, ?int $id): ResponseInterface
	{
		$parentModel = $parent == 'department' ? 'departmentModel' : 'employeeModel';

		$$parent = $this->$parentModel->findOne($parentId);
		if (empty($$parent)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $$parent);

		if (!empty($id)) {
			$assignment = $this->assignmentModel->findOne($id);
			if (empty($assignment)) {
				throw new HttpNotFoundException($request);
			}
		}

		if ($parent == 'department') {
			$option = 'employee';
			$options = $this->employeeModel->findAllForSelect();
		} else {
			$option = 'department';
			$options = $this->departmentModel->findAllForSelect();
		}

		return $this->render($request, $response, 'assignments/form.twig', [
			'csrf' => $this->generateCsrfToken(),
			'parent' => $parent,
			$parent => $$parent,
			'option' => $option,
			$option => $options,
			'assignment' => $assignment ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		$employeeId = $request->getParam('employee_id');
		$startDate = $request->getParam('start_date');
		$endDate = $request->getParam('end_date');
		$description = $request->getParam('description');

		$parent = $request->getParam('parent') == 'department' ? 'department' : 'employee';
		$parentIdName = $parent.'Id';
		$baseUrl = '/'.$parent.'s/view/'.$$parentIdName.'/assignments';

		if (empty($id)) {
			$errors = $this->assignmentModel->validateCreate($departmentId, $employeeId, $startDate, $endDate);
		} else {
			$errors = $this->assignmentModel->validateUpdate($startDate, $endDate);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			$url = empty($id) ? $baseUrl.'/form' : $baseUrl.'/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$this->assignmentModel->create($departmentId, $employeeId, $startDate, $endDate, $description);
		} else {
			$this->assignmentModel->update($id, $startDate, $endDate, $description);
		}

		$this->setFlash('success', 'Assignment has been successfully saved');
		return $response->withRedirect($baseUrl);
	}

	////////////////////////////////////////////////////////////////////////////

	public function deleteFromDepartment(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];
		return $this->delete($request, $response, 'department', $args['departmentId'], $id);
	}

	public function deleteFromEmployee(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];
		return $this->delete($request, $response, 'employee', $args['employeeId'], $id);
	}

	private function delete(ServerRequest $request, Response $response, string $parent, int $parentId, int $id): ResponseInterface
	{
		$parentModel = $parent == 'department' ? 'departmentModel' : 'employeeModel';

		$$parent = $this->$parentModel->findOne($parentId);
		if (empty($$parent)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $$parent);

		if (!empty($id)) {
			$assignment = $this->assignmentModel->findOne($id);
			if (empty($assignment)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'assignments/delete.twig', [
			'csrf' => $this->generateCsrfToken(),
			'parent' => $parent,
			$parent => $$parent,
			'assignment' => $assignment
		]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id', -1);

		$parent = $request->getParam('parent') == 'department' ? 'department' : 'employee';
		$parentId = $request->getParam($parent.'_id', -1);

		$baseUrl = '/'.$parent.'s/view/'.$parentId.'/assignments';

		$parentModel = $parent == 'department' ? 'departmentModel' : 'employeeModel';

		$$parent = $this->$parentModel->findOne($parentId);
		if (empty($$parent)) {
			throw new HttpNotFoundException($request);
		}

		$assignment = $this->assignmentModel->findOne($id);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		$this->assignmentModel->delete($id);

		$this->setFlash('success', 'Assignment has been successfully deleted');
		return $response->withRedirect($baseUrl);
	}

	////////////////////////////////////////////////////////////////////////////

	public function report(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		return $this->render($request, $response, 'assignments/report.twig', [
			'csrf' => $this->generateCsrfToken(),
			'department' => $department,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function reportPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$departmentId = $request->getParam('department_id');
		$startDate = $request->getParam('start_date');
		$endDate = $request->getParam('end_date');

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		$errors = $this->assignmentModel->validateReport($departmentId, $startDate, $endDate);
		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			return $response->withRedirect('/departments/view/'.$departmentId.'/assignments/report');
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		$report = $this->assignmentModel->generateReportByDepartment($departmentId, $startDate, $endDate);
		return $response->withFileDownload(fopen('data://text/plain,'.$report,'r'), 'report.csv', 'text/csv');
	}

	////////////////////////////////////////////////////////////////////////////

	public function viewMine(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];

		$assignment = $this->assignmentModel->findOne($id);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::employeeOnly($request, $assignment);

		$assignment = $this->assignmentModel->findOne($id);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		$checkIn = false;
		$today = new DateTime();
		if ($assignment->active && (new DateTime($assignment->start_date) <= $today) && (new DateTime($assignment->end_date) >= $today)) {
			$checkIn = true;
		}
		if (!empty($this->attendanceModel->findOneByAssignmentAndDate($id, (new DateTime())->format('Y-m-d')))) {
			$checkIn = false;
		}

		return $this->render($request, $response, 'assignments/mine.twig', [
			'assignment' => $assignment,
			'attendances' => $this->attendanceModel->findAllByAssignment($args['id']),
			'checkIn' => $checkIn,
			'success' => $this->getFlash('success')
		]);
	}

	public function checkIn(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];

		$assignment = $this->assignmentModel->findOne($id);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::employeeOnly($request, $assignment);

		return $this->render($request, $response, 'assignments/checkin.twig', [
			'csrf' => $this->generateCsrfToken(),
			'date' => (new DateTime())->format('l, d F Y'),
			'assignment' => $assignment,
			'statuses' => $this->statusModel->findAllByDepartmentForSelect($assignment->department_id),
			'errors' => $this->getFlash('errors')
		]);
	}

	public function checkInPost(ServerRequest $request, Response $response): ResponseInterface
{
	$this->verifyCsrfToken($request);

	$assignmentId = $request->getParam('assignment_id');
	$statusId = $request->getParam('status_id');
	$date = (new DateTime())->format('Y-m-d');
	$description = $request->getParam('description');

	$baseUrl = '/assignments/view/'.$assignmentId;

	$errors = $this->attendanceModel->validateCreate($assignmentId, $statusId, $date);
	if (!empty($errors)) {
		$this->setFlash('errors', $errors);
		$url = $baseUrl.'/check_in';
		return $response->withRedirect($url);
	}

	$this->attendanceModel->create($assignmentId, $statusId, $date, $description);

	$this->setFlash('success', 'Check-in has been successfully saved');
	return $response->withRedirect($baseUrl);
}
}
