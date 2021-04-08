<?php

namespace Pulunomoe\Attendance\Model;

use PDO;

class DepartmentModel extends Model
{
	private EmployeeModel $employeeModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->employeeModel = new EmployeeModel($pdo);
	}

	public function findAll(): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM departments_view');
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function findAllForSelect(): array
	{
		$departments = $this->findAll();

		$options = [];
		foreach ($departments as $department) {
			$options[$department->id] = $department->name;
		}

		return $options;
	}

	public function findAllMine(): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM departments_view WHERE manager_id = ?');
		$stmt->execute([$_SESSION['employee']->id]);

		return $stmt->fetchAll();
	}

	public function findOne(int $id): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM departments_view WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(int $managerId, string $name, string $description): ?int
	{
		$stmt = $this->pdo->prepare('INSERT INTO departments (manager_id, name, description) VALUES (?, ?, ?)');
		$stmt->execute([$managerId, $name, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, int $managerId, string $name, string $description): void
	{
		$stmt = $this->pdo->prepare('UPDATE departments SET manager_id = ?, name = ?, description = ? WHERE id = ?');
		$stmt->execute([$managerId, $name, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM departments WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validate(int $managerId, string $name): array
	{
		$errors = [];

		if (empty($managerId)) {
			$errors[] = 'manager is required';
		}

		if (empty($this->employeeModel->findOne($managerId))) {
			$errors[] = 'manager is invalid';
		}

		if (empty($name)) {
			$errors[] = 'name is required';
		}

		return $errors;
	}
}
