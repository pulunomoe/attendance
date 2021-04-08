<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Pulunomoe\Attendance\Middleware\AuthenticationMiddleware;
use Pulunomoe\Attendance\Model\DepartmentModel;
use Pulunomoe\Attendance\Model\HolidayModel;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class HolidayController extends Controller
{
	private DepartmentModel $departmentModel;
	private HolidayModel $holidayModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->holidayModel = new HolidayModel($pdo);
	}

	public function index(ServerRequest $request, Response $response, array $args): ResponseInterface
	{
		$departmentId = $args['departmentId'];

		$department = $this->departmentModel->findOne($departmentId);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		AuthenticationMiddleware::managerOnly($request, $department);

		return $this->render($request, $response, 'holidays/index.twig', [
			'department' => $department,
			'holidays' => $this->holidayModel->findAllByDepartment($args['departmentId']),
			'success' => $this->getFlash('success')
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

		AuthenticationMiddleware::managerOnly($request, $department);

		if (!empty($id)) {
			$holiday = $this->holidayModel->findOne($id);
			if (empty($holiday)) {
				throw new HttpNotFoundException($request);
			}
		}

		return $this->render($request, $response, 'holidays/form.twig', [
			'csrf' => $this->generateCsrfToken(),
			'department' => $department,
			'holiday' => $holiday ?? null,
			'errors' => $this->getFlash('errors')
		]);
	}

	public function formPost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		$name = $request->getParam('name');
		$date = $request->getParam('date');
		$description = $request->getParam('description');

		$baseUrl = '/departments/view/'.$departmentId.'/holidays';

		if (empty($id)) {
			$errors = $this->holidayModel->validateCreate($departmentId, $name, $date);
		} else {
			$errors = $this->holidayModel->validateUpdate($name, $date);
		}

		if (!empty($errors)) {
			$this->setFlash('errors', $errors);;
			$url = empty($id) ? $baseUrl.'/form' : $baseUrl.'/form/'.$id;
			return $response->withRedirect($url);
		}

		if (empty($id)) {
			$this->holidayModel->create($departmentId, $name, $date, $description);
		} else {
			$this->holidayModel->update($id, $name, $date, $description);
		}

		$this->setFlash('success', 'Holiday has been successfully saved');
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

		AuthenticationMiddleware::managerOnly($request, $department);

		$holiday = $this->holidayModel->findOne($id);
		if (empty($holiday)) {
			throw new HttpNotFoundException($request);
		}

		return $this->render($request, $response, 'holidays/delete.twig', [
			'csrf' => $this->generateCsrfToken(),
			'department' => $department,
			'holiday' => $holiday
		]);
	}

	public function deletePost(ServerRequest $request, Response $response): ResponseInterface
	{
		$this->verifyCsrfToken($request);

		$id = $request->getParam('id', -1);
		$departmentId = $request->getParam('id', -1);

		$department = $this->departmentModel->findOne($id);
		if (empty($department)) {
			throw new HttpNotFoundException($request);
		}

		$holiday = $this->holidayModel->findOne($id);
		if (empty($holiday)) {
			throw new HttpNotFoundException($request);
		}

		$this->holidayModel->delete($id);

		$this->setFlash('success', 'Holiday has been successfully deleted');
		return $response->withRedirect('/departments/view/'.$departmentId.'/holidays');
	}
}
