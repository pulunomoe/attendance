<?php

namespace Pulunomoe\Attendance\Model;

use PDO;

class StatusModel extends Model
{
	private DepartmentModel $departmentModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->departmentModel = new DepartmentModel($pdo);
	}

	public function findAllByDepartment(int $departmentId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM statuses WHERE department_id = ?');
		$stmt->execute([$departmentId]);

		return $stmt->fetchAll();
	}

	public function findOne(int $id): ?object
	{
		$stmt = $this->pdo->prepare('SELECT * FROM statuses WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(int $departmentId, string $name, string $description): ?int
	{
		$stmt = $this->pdo->prepare('INSERT INTO statuses (department_id, name, description) VALUES (?, ?, ?)');
		$stmt->execute([$departmentId, $name, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, string $name, string $description): void
	{
		$stmt = $this->pdo->prepare('UPDATE statuses SET name = ?, description = ? WHERE id = ?');
		$stmt->execute([$name, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM statuses WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(int $departmentId, string $name): array
	{
		$errors = $this->validateUpdate($name);

		if (empty($departmentId)) {
			$errors[] = 'department is required';
		}

		if (empty($this->departmentModel->findOne($departmentId))) {
			$errors[] = 'department is invalid';
		}

		return $errors;
	}

	public function validateUpdate(string $name): array
	{
		$errors = [];

		if (empty($name)) {
			$errors[] = 'name is required';
		}

		return $errors;
	}
}
