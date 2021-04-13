<?php

namespace Pulunomoe\Attendance\Model;

use DateInterval;
use DateTime;
use Exception;
use PDO;

class AssignmentModel extends Model
{
	private DepartmentModel $departmentModel;
	private EmployeeModel $employeeModel;
	private StatusModel $statusModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
		$this->statusModel = new StatusModel($pdo);
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

	public function findAllMine(): array
	{
		$assignments = $this->findAllByEmployee($_SESSION['employee']->id);

		foreach ($assignments as &$assignment) {
			$assignment = $this->enrichFields($assignment);
		}

		return $assignments;
	}

	public function findOne(int $id): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM assignments_view WHERE id = ?');
		$stmt->execute([$id]);

		$assignment = $stmt->fetch();

		return $this->enrichFields($assignment);
	}

	public function create(int $departmentId, int $employeeId, string $startDate, string $endDate, string $description): ?int
	{
		$startDate = $this->parseDate($startDate);
		$endDate = $this->parseDate($endDate);

		$stmt = $this->pdo->prepare('INSERT INTO assignments (department_id, employee_id, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)');
		$stmt->execute([$departmentId, $employeeId, $startDate, $endDate, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, string $startDate, string $endDate, string $description): void
	{
		$startDate = $this->parseDate($startDate);
		$endDate = $this->parseDate($endDate);

		$stmt = $this->pdo->prepare('UPDATE assignments SET start_date = ?, end_date = ?, description = ? WHERE id = ?');
		$stmt->execute([$startDate, $endDate, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM assignments WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(int $departmentId, int $employeeId, string $startDate, string $endDate): array
	{
		$errors = $this->validateUpdate($startDate, $endDate);

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

	public function validateUpdate(string $startDate, string $endDate): array
	{
		$errors = [];

		if (empty($startDate)) {
			$errors[] = 'start date is required';
		}

		try {
			new DateTime($startDate);
		} catch (Exception $e) {
			$errors[] = 'start date is invalid';
		}

		if (!empty($endDate)) {
			try {
				new DateTime($endDate);
			} catch (Exception $e) {
				$errors[] = 'end date is invalid';
			}
		}

		return $errors;
	}

	////////////////////////////////////////////////////////////////////////////

	public function generateReportByDepartment(int $departmentId, string $startDate, string $endDate): string
	{
		$startDate = $this->parseDate($startDate);
		$endDate = $this->parseDate($endDate);

		$stmt = $this->pdo->prepare('SELECT * FROM reports_view WHERE department_id = ? AND date >= ? AND date <= ?');
		$stmt->execute([$departmentId, $startDate, $endDate]);

		$rows = $stmt->fetchAll();

		$data = [];
		$employees = [];
		foreach ($rows as $row) {
			$data[$row->date][$row->employee_name] = $row->status;
			if (!array_key_exists($row->employee_name, $employees)) {
				$employees[$row->employee_name] = [];
			}
		}

		$csv[0][0] = 'employee';

		for ($i = 0; $i < sizeof($employees); $i++) {
			$csv[$i+1][0] = array_keys($employees)[$i];
		}

		$startDate = new DateTime($startDate);
		$endDate = new DateTime($endDate);
		$days = $startDate->diff($endDate)->days;

		for ($i = 1; $i <= $days; $i++) {

			$date = new DateTime($startDate->format('Y-m-d').' +'.($i-1).' days');
			$csv[0][$i] = $date->format('Y-m-d');

			for ($j = 0; $j < sizeof($employees); $j++) {
				if (array_key_exists($csv[0][$i], $data)) {
					$employee = array_keys($employees)[$j];
					if (array_key_exists($employee, $data[$csv[0][$i]])) {
						$status = $data[$csv[0][$i]][$employee];
						$csv[$j+1][$i] = $status;
						if (empty($employees[$employee][$status])) {
							$employees[$employee][$status] = 1;
						} else {
							$employees[$employee][$status]++;
						}
					}
				}
				if (empty($csv[$j+1][$i])) {
					$csv[$j+1][$i] = '';
				}
			}

		}

		$statuses = $this->statusModel->findAllByDepartment($departmentId);
		foreach ($statuses as $status) {
			$csv[0][] = $status->name;
			for ($i = 0; $i < sizeof($employees); $i++) {
				$employee = array_keys($employees)[$i];
				$csv[$i+1][] = empty($employees[$employee][$status->name]) ? 0 : $employees[$employee][$status->name];
			}
		}

		$output = '';
		foreach ($csv as $row) {
			foreach ($row as $col) {
				$output .= '"'.$col."\",";
			}
			$output .= '$$$';
			$output = str_replace(',$$$', "\n", $output);
		}

		return $output;
	}

	public function validateReport(int $departmentId, string $startDate, string $endDate): array
	{
		$errors = [];

		if (empty($departmentId)) {
			$errors[] = 'department is required';
		}

		if (empty($this->departmentModel->findOne($departmentId))) {
			$errors[] = 'department is invalid';
		}

		if (empty($startDate)) {
			$errors[] = 'start date is required';
		}

		try {
			new DateTime($startDate);
		} catch (Exception $e) {
			$errors = 'start date is invalid';
		}

		if (empty($endDate)) {
			$errors[] = 'end date is required';
		}

		try {
			new DateTime($endDate);
		} catch (Exception $e) {
			$errors[] = 'end date is invalid';
		}

		return $errors;
	}

	////////////////////////////////////////////////////////////////////////////

	private function enrichFields(object $assignment): object
	{
		$department = $this->departmentModel->findOne($assignment->department_id);
		$assignment->manager_name = $department->manager_name;
		$assignment->manager_email = $department->manager_email;

		$today = new DateTime();
		$assignment->active = ((new DateTime($assignment->start_date)) <= $today && (new DateTime($assignment->end_date)) >= $today);

		return $assignment;
	}
}
