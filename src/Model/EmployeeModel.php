<?php

namespace Pulunomoe\Attendance\Model;

class EmployeeModel extends Model
{
	public function findAll(): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM employees WHERE admin = 0');
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function findAllForSelect(): array
	{
		$employees = $this->findAll();

		$options = [];
		foreach ($employees as $employee) {
			$options[$employee->id] = $employee->name . '<' . $employee->email . '>';
		}

		return $options;
	}

	public function findOne(int $id): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM employees WHERE id = ? AND admin = 0');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function create(string $name, string $email, string $password, string $description): ?int
	{
		$email = strtolower($email);
		$password = password_hash($password, PASSWORD_DEFAULT);

		$stmt = $this->pdo->prepare('INSERT INTO employees (name, email, password, description) VALUES (?, ?, ?, ?)');
		$stmt->execute([$name, $email, $password, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, string $name, string $email, string $description): void
	{
		$stmt = $this->pdo->prepare('UPDATE employees SET name = ?, email = ?, description = ? WHERE id = ?');
		$stmt->execute([$name, $email, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM employees WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function login(string $email, string $password): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM employees WHERE email = ?');
		$stmt->execute([$email]);
		$employee = $stmt->fetch();

		if (empty($employee)) {
			return null;
		}

		if (!password_verify($password, $employee->password)) {
			return null;
		}

		unset($employee->password);
		return $employee;
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(string $name, string $email, string $password, string $confirm): array
	{
		$errors = $this->validateCommon($name, $email);

		$stmt = $this->pdo->prepare('SELECT COUNT(id) FROM employees WHERE email = ?');
		$stmt->execute([strtolower($email)]);
		$id = $stmt->fetchColumn();
		if (!empty($id)) {
			$errors[] = 'email already exists';
		}

		if (empty($password)) {
			$errors[] = 'password is required';
		}

		if (!password_verify($confirm, password_hash($password, PASSWORD_DEFAULT))) {
			$errors[] = 'password confirmation does not match';
		}

		return $errors;
	}

	public function validateUpdate(int $id, string $name, string $email): array
	{
		$errors = $this->validateCommon($name, $email);

		$stmt = $this->pdo->prepare('SELECT COUNT(id) FROM employees WHERE email = ? AND id != ?');
		$stmt->execute([strtolower($email), $id]);
		$id = $stmt->fetchColumn();
		if (!empty($id)) {
			$errors[] = 'email already exists';
		}

		return $errors;
	}

	private function validateCommon(string $name, string $email): array
	{
		$errors = [];

		if (empty($name)) {
			$errors[] = 'name is required';
		}

		if (empty($email)) {
			$errors[] = 'email is required';
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'email is invalid';
		}

		return $errors;
	}
}
