<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Middleware\AuthenticationMiddleware;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\EmployeeModel;
use Pulunomoe\Attendance\Model\HolidayModel;
use Pulunomoe\Attendance\Model\StatusModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class DepartmentController extends Controller
{
	private DepartmentModel $departmentModel;
	private EmployeeModel $employeeModel;
	private HolidayModel $holidayModel;
	private StatusModel $statusModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
		$this->holidayModel = new HolidayModel($pdo);
		$this->statusModel = new StatusModel($pdo);
	}

	public function index(ServerRequest $request, Response $response): ResponseInterface
	{
		return $this->render($request, $response, 'departments/index.twig', [
			'departments' => $this->departmentModel->findAll(),
			'success' => $this->getFlash('success')
		]);
	}

	public function view(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];

		$department = $this->departmentModel->findOne($id);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		return $this->render($request, $response, 'departments/view.twig', [
			'department' => $department,
			'employees' => [],
			'statuses' => $this->statusModel->findAllByDepartment($id),
			'holidays' => $this->holidayModel->findAllByDepartment($id),
			'success' => $this->getFlash('success')
		]);
	}

	public function form(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'] ?? null;

		if (!empty($id)) {
			$department = $this->departmentModel->findOne($id);
			if (empty($department)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'departments/form.twig', [
			'department' => $department ?? null,
			'managers' => $this->employeeModel->findAllForSelect(),
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id');
		$managerId = $request->getParam('manager_id');
		$name = $request->getParam('name');
		$description = $request->getParam('description');

		$errors = $this->departmentModel->validate($managerId, $name);
		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			$url = empty($id) ? '/departments/form' : '/departments/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$id = $this->departmentModel->create($managerId, $name, $description);
		} else {
			$this->departmentModel->update($id, $managerId, $name, $description);
		}

		$this->setFlash('success', 'Department has been successfully saved');
		return $response->withRedirect('/departments/view/'.$id);
	}

	public function delete(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];

		$department = $this->departmentModel->findOne($id);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'departments/delete.twig', [
			'department' => $department
		]);
	}

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id', -1);

		$department = $this->departmentModel->findOne($id);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		$this->departmentModel->delete($id);

		$this->setFlash('success', 'Department has been successfully deleted');
		return $response->withRedirect('/departments');
	}
}
