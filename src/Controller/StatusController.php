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

		return $this->render($request, $response, 'statuses/form.twig', [
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
		$description = $request->getParam('description');

		$baseUrl = '/departments/view/'.$departmentId.'/statuses';

		if (empty($id)) {
			$errors = $this->statusModel->validateCreate($departmentId, $name);
		} else {
			$errors = $this->statusModel->validateUpdate($name);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);;
			$url = empty($id) ? $baseUrl.'/form' : $baseUrl.'/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$this->statusModel->create($departmentId, $name, $description);
		} else {
			$this->statusModel->update($id, $name, $description);
		}

		$this->setFlash('success', 'Status has been successfully saved');
		return $response->withRedirect($baseUrl);
	}

	public function delete(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];
		$id = $args['id'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		$status = $this->statusModel->findOne($id);
		if (empty($status)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'statuses/delete.twig', [
			'department' => $department,
			'status' => $status
		]);
	}

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id', -1);
		$departmentId = $request->getParam('id', -1);

		$department = $this->departmentModel->findOne($id);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		$status = $this->statusModel->findOne($id);
		if (empty($status)) {
			throw new HttpNotFoundException($request);
		}

		$this->statusModel->delete($id);

		$this->setFlash('success', 'Status has been successfully deleted');
		return $response->withRedirect('/departments/view/'.$departmentId.'/statuses');
	}
}
