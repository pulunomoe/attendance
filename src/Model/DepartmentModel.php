<?php

namespace Pulunomoe\Attendance\Model;

class DepartmentModel extends Model
{
	public function findAll(): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM departments');
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function findOne(int $id): ?object
	{
		$stmt = $this->pdo->prepare('SELECT * FROM departments WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(string $name, string $description): ?int
	{
		$stmt = $this->pdo->prepare('INSERT INTO departments (name, description) VALUES (?, ?)');
		$stmt->execute([$name, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, string $name, string $description): void
	{
		$stmt = $this->pdo->prepare('UPDATE departments SET name = ?, description = ? WHERE id = ?');
		$stmt->execute([$name, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM departments WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validate(string $name): array
	{
		$errors = [];

		if (empty($name)) {
			$errors[] = 'name is required';
		}

		return $errors;
	}
}