<?php

namespace Pulunomoe\Attendance\Model;

use PDO;

abstract class Model
{
	protected PDO $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}
}