<?php

namespace Pulunomoe\Attendance\Controller;

use PDO;
use Slim\Exception\HttpBadRequestException;
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

	public function generateCsrfToken(): array
	{
		$key = password_hash(sha1(mt_rand()).sha1(microtime()), PASSWORD_DEFAULT);
		$value = password_hash(sha1(mt_rand()).sha1(microtime()), PASSWORD_DEFAULT);

		unset($_SESSION['csrf']);
		$_SESSION['csrf'][$key] = $value;

		return [
			'key' => $key,
			'value' => $value
		];
	}

	public function verifyCsrfToken(ServerRequest $request): void
	{
		$key = $request->getParam('csrf_key');
		$value = $request->getParam('csrf_value');

		if (empty($_SESSION['csrf'][$key]) || $_SESSION['csrf'][$key] != $value) {
			throw new HttpBadRequestException($request);
		}

		unset($_SESSION['csrf']);
	}
}
