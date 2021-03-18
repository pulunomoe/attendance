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

	protected function parseDate(string $date): string
	{
		return (new DateTime($date))->format('Y-m-d H:i:s');
	}
}
