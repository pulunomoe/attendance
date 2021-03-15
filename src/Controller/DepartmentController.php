<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Views\PhpRenderer;

class DepartmentController extends Controller
{
	private DepartmentModel $departmentModel;

	public function __construct(PDO $pdo, PhpRenderer $view)
	{
		parent::__construct($pdo, $view);
		$this->departmentModel = new DepartmentModel($pdo);
	}

	public function index(ServerRequest $request, Response $response): ResponseInterface
	{
		return $this->view->render($response, 'departments/index.php', [
			'departments' => $this->departmentModel->findAll(),
			'success' => $this->getFlash('success')
		]);
	}

	public function view(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$department = $this->departmentModel->findOne($args['id']);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		return $this->view->render($response, 'departments/view.php', [
			'department' => $department,
			'employees' => [],
			'success' => $this->getFlash('success')
		]);
	}

	public function form(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		if (!empty($args['id'])) {
			$department = $this->departmentModel->findOne($args['id']);
			if (empty($department)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->view->render($response, 'departments/form.php', [
			'department' => $department ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id');
		$name = $request->getParam('name');
		$description = $request->getParam('description');

		$errors = $this->departmentModel->validate($name);
		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			$url = empty($id) ? '/departments/form' : '/departments/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$id = $this->departmentModel->create($name, $description);
		} else {
			$this->departmentModel->update($id, $name, $description);
		}

		$this->setFlash('success', 'Department has been successfully saved');
		return $response->withRedirect('/departments/view/'.$id);
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