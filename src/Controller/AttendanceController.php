<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Middleware\AuthenticationMiddleware;
use Pulunomoe\Attendance\Model\AssignmentModel;
use Pulunomoe\Attendance\Model\AttendanceModel;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\HolidayModel;
use Pulunomoe\Attendance\Model\StatusModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AttendanceController extends Controller
{
	private DepartmentModel $departmentModel;
	private StatusModel $statusModel;
	private AssignmentModel $assignmentModel;
	private AttendanceModel $attendanceModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->statusModel = new StatusModel($pdo);
		$this->assignmentModel = new AssignmentModel($pdo);
		$this->attendanceModel = new AttendanceModel($pdo);
	}

	public function form(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];
		$assignmentId = $args['assignmentId'];
		$id = $args['id'] ?? null;

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		$assignment = $this->assignmentModel->findOne($assignmentId);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		if (!empty($id)) {
			$attendance = $this->attendanceModel->findOne($id);
			if (empty($attendance)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'attendances/form.twig', [
			'csrf' => $this->generateCsrfToken(),
			'department' => $department,
			'assignment' => $assignment,
			'statuses' => $this->statusModel->findAllByDepartmentForSelect($departmentId),
			'attendance' => $attendance ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		$assignmentId = $request->getParam('assignment_id');
		$statusId = $request->getParam('status_id');
		$date = $request->getParam('date');
		$description = $request->getParam('description');

		$baseUrl = '/departments/view/'.$departmentId.'/assignments/view/'.$assignmentId;

		if (empty($id)) {
			$errors = $this->attendanceModel->validateCreate($assignmentId, $statusId, $date);
		} else {
			$errors = $this->attendanceModel->validateUpdate($assignmentId, $statusId);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);;
			$url = empty($id) ? $baseUrl.'/attendances/form' : $baseUrl.'/form/attendances/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$this->attendanceModel->create($assignmentId, $statusId, $date, $description);
		} else {
			$this->attendanceModel->update($id, $statusId, $description);
		}

		$this->setFlash('success', 'Attendance has been successfully saved');
		return $response->withRedirect($baseUrl);
	}

	public function delete(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];
		$assignmentId = $args['assignmentId'];
		$id = $args['id'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		$assignment = $this->assignmentModel->findOne($assignmentId);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		$attendance = $this->attendanceModel->findOne($id);
		if (empty($attendance)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'attendances/delete.twig', [
			'csrf' => $this->generateCsrfToken(),
			'department' => $department,
			'assignment' => $assignment,
			'attendance' => $attendance
		]);
	}

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		$assignmentId = $request->getParam('assignment_id');

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}
		
		AuthenticationMiddleware::managerOnly($request, $department);

		$assignment = $this->assignmentModel->findOne($assignmentId);
		if (empty($assignment)) {
			throw new HttpNotFoundException($request);
		}

		$attendance = $this->attendanceModel->findOne($id);
		if (empty($attendance)) {
			throw new HttpNotFoundException($request);
		}

		$this->attendanceModel->delete($id);

		$this->setFlash('success', 'Attendance has been successfully deleted');
		return $response->withRedirect('/departments/view/'.$departmentId.'/assignments/view/'.$assignmentId);
	}
}
