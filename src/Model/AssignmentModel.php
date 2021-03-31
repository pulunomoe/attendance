<?php

namespace Pulunomoe\Attendance\Model;

use DateTime;
use Exception;
use PDO;

class AssignmentModel extends Model
{
	private DepartmentModel $departmentModel;
	private EmployeeModel $employeeModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
	}

	public function findAllByDepartment(int $departmentId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM assignments_view WHERE department_id = ?');
		$stmt->execute([$departmentId]);

		return $stmt->fetchAll();
	}

	public function findAllByEmployee(int $employeeId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM assignments_view WHERE employee_id = ?');
		$stmt->execute([$employeeId]);

		return $stmt->fetchAll();
	}

	public function findOne(int $id): ?object
	{
		$stmt = $this->pdo->prepare('SELECT * FROM assignments_view WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(int $departmentId, int $employeeId, int $isManager, string $startDate, string $endDate, string $description): ?int
	{
		$startDate = $this->parseDate($startDate);
		$endDate = $this->parseDate($endDate);

		$stmt = $this->pdo->prepare('INSERT INTO assignments (department_id, employee_id, is_manager, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)');
		$stmt->execute([$departmentId, $employeeId, $isManager, $startDate, $endDate, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, int $isManager, string $startDate, string $endDate, string $description): void
	{
		$startDate = $this->parseDate($startDate);
		$endDate = $this->parseDate($endDate);

		$stmt = $this->pdo->prepare('UPDATE assignments SET is_manager = ?, start_date = ?, end_date = ?, description = ? WHERE id = ?');
		$stmt->execute([$isManager, $startDate, $endDate, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM assignments WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(int $departmentId, int $employeeId, int $isManager, string $startDate, string $endDate): array
	{
		$errors = $this->validateUpdate($isManager, $startDate, $endDate);

		if (empty($departmentId)) {
			$errors[] = 'department is required';
		}

		if (empty($this->departmentModel->findOne($departmentId))) {
			$errors[] = 'department is invalid';
		}

		if (empty($employeeId)) {
			$errors[] = 'employee is required';
		}

		if (empty($this->employeeModel->findOne($employeeId))) {
			$errors[] = 'employee is invalid';
		}

		return $errors;
	}

	public function validateUpdate(int $isManager, string $startDate, string $endDate): array
	{
		$errors = [];

		if (!in_array($isManager, [0, 1])) {
			$errors[] = 'manager status is invalid';
		}

		if (empty($startDate)) {
			$errors[] = 'start date is required';
		}

		try {
			new DateTime($startDate);
		} catch (Exception $e) {
			$errors = 'start date is invalid';
		}

		if (!empty($endDate)) {
			try {
				new DateTime($endDate);
			} catch (Exception $e) {
				$errors = 'end date is invalid';
			}
		}

		return $errors;
	}
}
