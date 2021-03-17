<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\StatusModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class StatusController extends Controller
{
	private DepartmentModel $departmentModel;
	private StatusModel $statusModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->statusModel = new StatusModel($pdo);
	}

	public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'statuses/index.twig', [
			'department' => $department,
			'statuses' => $this->statusModel->findAllByDepartment($args['departmentId'])
		]);
	}

	public function form(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];
		$id = $args['id'] ?? null;

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		if (!empty($id)) {
			$status = $this->statusModel->findOne($id);
			if (empty($status)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'statuses/form.php', [
			'department' => $department,
			'status' => $status ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		$name = $request->getParam('name');
		$email = $request->getParam('email');
		$password = $request->getParam('password');
		$confirm = $request->getParam('confirm');
		$description = $request->getParam('description');

		if (empty($id)) {
			$errors = $this->statusModel->validateCreate($departmentId, $name);
		} else {
			$errors = $this->statusModel->validateUpdate($id, $name);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			$url = empty($id) ? '/depart/form' : '/employees/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$id = $this->employeeModel->create($name, $email, $password, $description);
		} else {
			$this->employeeModel->update($id, $name, $email, $description);
		}

		$this->setFlash('success', 'Employee has been successfully saved');
		return $response->withRedirect('/employees/view/'.$id);
	}
}
