<?php

namespace Pulunomoe\Attendance\Model;

use DateTime;
use Exception;
use PDO;

class HolidayModel extends Model
{
	private DepartmentModel $departmentModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
	}

	public function findAllByDepartment(int $departmentId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM holidays WHERE department_id = ?');
		$stmt->execute([$departmentId]);

		return $stmt->fetchAll();
	}

	public function findOne(int $id): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM holidays WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(int $departmentId, string $name, string $date, string $description): ?int
	{
		$date = $this->parseDate($date);

		$stmt = $this->pdo->prepare('INSERT INTO holidays (department_id, name, date, description) VALUES (?, ?, ?, ?)');
		$stmt->execute([$departmentId, $name, $date, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, string $name, string $date, string $description): void
	{
		$date = $this->parseDate($date);

		$stmt = $this->pdo->prepare('UPDATE holidays SET name = ?, date = ?, description = ? WHERE id = ?');
		$stmt->execute([$name, $date, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM holidays WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(int $departmentId, string $name, string $date): array
	{
		$errors = $this->validateUpdate($name, $date);

		if (empty($departmentId)) {
			$errors[] = 'department is required';
		}

		if (empty($this->departmentModel->findOne($departmentId))) {
			$errors[] = 'department is invalid';
		}

		return $errors;
	}

	public function validateUpdate(string $name, string $date): array
	{
		$errors = [];

		if (empty($name)) {
			$errors[] = 'name is required';
		}

		if (empty($date)) {
			$errors[] = 'date is required';
		}

		try {
			new DateTime($date);
		} catch (Exception $e) {
			$errors = 'date is invalid';
		}

		return $errors;
	}
}
