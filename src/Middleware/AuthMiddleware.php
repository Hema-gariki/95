<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

/**
 * AuthMiddleware — guards all /admin/* routes.
 *
 * If the visitor has no active session (not logged in),
 * they get redirected to the login page.
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['admin'])) {
            $_SESSION['_flash']['error'] = 'Please sign in to access the admin area.';
            return (new SlimResponse())
                ->withHeader('Location', '/admin/login')
                ->withStatus(302);
        }
        return $handler->handle($request);
    }
}
