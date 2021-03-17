<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Model\EmployeeModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class EmployeeController extends Controller
{
	private EmployeeModel $employeeModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
	}

	public function index(ServerRequest $request, Response $response): ResponseInterface
	{
		return $this->render($request, $response, 'employees/index.twig', [
			'employees' => $this->employeeModel->findAll(),
			'success' => $this->getFlash('success')
		]);
	}

	public function view(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$employee = $this->employeeModel->findOne($args['id']);
		if (empty($employee)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'employees/view.twig', [
			'employee' => $employee,
			'employees' => [],
			'success' => $this->getFlash('success')
		]);
	}

	public function form(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'] ?? null;

		if (!empty($id)) {
			$employee = $this->employeeModel->findOne($id);
			if (empty($employee)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'employees/form.twig', [
			'employee' => $employee ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id');
		$name = $request->getParam('name');
		$email = $request->getParam('email');
		$password = $request->getParam('password');
		$confirm = $request->getParam('confirm');
		$description = $request->getParam('description');

		if (empty($id)) {
			$errors = $this->employeeModel->validateCreate($name, $email, $password, $confirm);
		} else {
			$errors = $this->employeeModel->validateUpdate($id, $name, $email);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);
			$url = empty($id) ? '/employees/form' : '/employees/form/'.$id;
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

	public function delete(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$id = $args['id'];

		$employee = $this->employeeModel->findOne($id);
		if (empty($employee)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'employees/delete.twig', [
			'employee' => $employee
		]);
	}

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$id = $request->getParam('id', -1);

		$employee = $this->employeeModel->findOne($id);
		if (empty($employee)) {
			throw new HttpNotFoundException($request);
		}

		$this->employeeModel->delete($id);

		$this->setFlash('success', 'Employee has been successfully deleted');
		return $response->withRedirect('/employees');
	}
}
