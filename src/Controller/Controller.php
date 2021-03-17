<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Views\Twig;

abstract class Controller
{
	protected PDO $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function render(ServerRequest $request, Response $response, string $template, array $data = [])
	{
		return Twig::fromRequest($request)->render($response, $template, $data);
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
