<?php

namespace Pulunomoe\Attendance\Model;

use DateTime;
use Exception;
use PDO;

class AttendanceModel extends Model
{
	private StatusModel $statusModel;
	private AssignmentModel $assignmentModel;

	public function __construct(PDO $pdo)
	{
		parent::__construct($pdo);
		$this->statusModel = new StatusModel($pdo);
		$this->assignmentModel = new AssignmentModel($pdo);
	}

	public function findAllByAssignment(int $assignmentId): array
	{
		$stmt = $this->pdo->prepare('SELECT * FROM attendances_view WHERE assignment_id = ?');
		$stmt->execute([$assignmentId]);

		return $stmt->fetchAll();
	}

	public function findOne(int $id): object|bool
	{
		$stmt = $this->pdo->prepare('SELECT * FROM attendances_view WHERE id = ?');
		$stmt->execute([$id]);

		return $stmt->fetch();
	}

	public function findOneByAssignmentAndDate(int $assignmentId, string $date): object|bool
	{
		$date = $this->parseDate($date);

		$stmt = $this->pdo->prepare('SELECT * FROM attendances WHERE assignment_id = ? AND date = ?');
		$stmt->execute([$assignmentId, $date]);

		return $stmt->fetch();
	}

	public function create(int $assignmentId, int $statusId, string $date, string $description): ?int
	{
		$date = $this->parseDate($date);

		$stmt = $this->pdo->prepare('INSERT INTO attendances (assignment_id, status_id, date, description) VALUES (?, ?, ?, ?)');
		$stmt->execute([$assignmentId, $statusId, $date, $description]);

		return $this->pdo->lastInsertId();
	}

	public function update(int $id, int $statusId, string $description): void
	{
		$stmt = $this->pdo->prepare('UPDATE attendances SET status_id = ?, description = ? WHERE id = ?');
		$stmt->execute([$statusId, $description, $id]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->pdo->prepare('DELETE FROM attendances WHERE id = ?');
		$stmt->execute([$id]);
	}

	////////////////////////////////////////////////////////////////////////////

	public function validateCreate(int $assignmentId, int $statusId, string $date): array
	{
		$errors = $this->validateUpdate($assignmentId, $statusId);

		if (empty($assignmentId)) {
			$errors[] = 'assignment is required';
		}

		$assignment = $this->assignmentModel->findOne($assignmentId);
		if (empty($assignment)) {
			$errors[] = 'assignment is invalid';
		}

		if (empty($date)) {
			$errors[] = 'date is required';
		}

		try {
			$date = new DateTime($date);
		} catch (Exception $e) {
			$errors[] = 'date is invalid';
			return $errors;
		}

		$startDate = new DateTime($assignment->start_date);
		if ($date < $startDate) {
			$errors[] = 'date is before start date';
		}

		if (!empty($assignment->end_date)) {
			$endDate = new DateTime($assignment->end_date);
			if ($date > $endDate) {
				$errors[] = 'date is after end date';
			}
		}

		if (!empty($this->findOneByAssignmentAndDate($assignmentId, $date->format('Y-m-d')))) {
			$errors[] = 'attendance already exists';
		}

		return $errors;
	}

	public function validateUpdate(int $assignmentId, int $statusId): array
	{
		$errors = [];

		$assignment = $this->assignmentModel->findOne($assignmentId);

		if (empty($statusId)) {
			$errors[] = 'status is required';
		}

		if (empty($this->statusModel->findOne($statusId))) {
			$errors[] = 'status is invalid';
		}

		if (!$this->statusModel->isInDepartment($statusId, $assignment->department_id)) {
			$errors[] = 'status is invalid';
		}

		return $errors;
	}
}
