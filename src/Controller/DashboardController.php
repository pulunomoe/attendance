<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Model\AssignmentModel;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\EmployeeModel;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class DashboardController extends Controller
{
	private DepartmentModel $departmentModel;
	private EmployeeModel $employeeModel;
	private AssignmentModel $assignmentModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
		$this->assignmentModel = new AssignmentModel($pdo);
	}

	public function dashboard(ServerRequest $request, Response $response): ResponseInterface
	{
		return $this->render($request, $response, 'dashboard/dashboard.twig', [
			'myAssignments' => $this->assignmentModel->findAllMine(),
			'myDepartments' => $this->departmentModel->findAllMine()
		]);
	}
}
