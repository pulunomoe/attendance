<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Slim\Views\PhpRenderer;

abstract class Controller
{
	protected PDO $pdo;
	protected PhpRenderer $view;

	public function __construct(PDO $pdo, PhpRenderer $view)
	{
		$this->pdo = $pdo;
		$this->view = $view;
	}

	public function getFlash($key): string|array|null
	{
		if (!empty($_SESSION['flash'][$key])) {
			$flash = $_SESSION['flash'][$key];
			unset($_SESSION['flash'][$key]);
			return $flash;
		} else {
			return null;
		}
	}

	public function setFlash(string $key, string|array $value): void
	{
		$_SESSION['flash'][$key] = $value;
	}
}