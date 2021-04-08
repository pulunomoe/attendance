<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Model\EmployeeModel;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class AuthenticationController extends Controller
{
	private EmployeeModel $employeeModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
	}

	public function login(ServerRequest $request, Response $response): ResponseInterface
	{
		return $this->render($request, $response, 'login.twig', [
			'csrf' => $this->generateCsrfToken(),
			'error' => $this->getFlash('error')
		]);
	}

	public function loginPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$email = $request->getParam('email');
		$password = $request->getParam('password');

		$employee = $this->employeeModel->login($email, $password);
		if (empty($employee)) {
			$this->setFlash('error', 'Invalid email and/or password');
		}

		$_SESSION['employee'] = $employee;

		return $response->withRedirect('/');
	}

	public function logout(ServerRequest $request, Response $response): ResponseInterface
	{
		session_destroy();

		return $response->withRedirect('/login');
	}
}
