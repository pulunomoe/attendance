<?php

namespace Pulunomoe\Attendance\Model;

use DateTime;
use PDO;

abstract class Model
{
	protected PDO $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	////////////////////////////////////////////////////////////////////////////

	protected function parseDate(string $date): ?string
	{
		return empty($date) ? null : (new DateTime($date))->format('Y-m-d');
	}
}
