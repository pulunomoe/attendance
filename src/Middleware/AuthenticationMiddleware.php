<?php

namespace Pulunomoe\Attendance\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;
use Slim\Psr7\Response;

class AuthenticationMiddleware
{
	public const RULE_EVERYONE = 0;
	public const RULE_ADMIN = 1;

	private int $rule;

	public function __construct(int $rule)
	{
		$this->rule = $rule;
	}

	public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
	{
		if (empty($_SESSION['employee'])) {
			return (new Response())
				->withHeader('Location', '/login')
				->withStatus(302);
		}

		if ($_SESSION['employee']->admin < $this->rule) {
			session_destroy();
			return (new Response())
				->withHeader('Location', '/login')
				->withStatus(302);
		}

		return $handler->handle($request);
	}

	public static function managerOnly(Request $request, object $department): void
	{
		if (!$_SESSION['employee']->admin && $department->manager_id != $_SESSION['employee']->id) {
			session_destroy();
			throw new HttpForbiddenException($request);
		}
	}
}
