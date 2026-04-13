<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(private Twig $view) {}

    /** Show the login form */
    public function showLogin(Request $request, Response $response): Response
    {
        if (!empty($_SESSION['admin'])) {
            return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
        }
        return $this->view->render($response, 'auth/login.twig');
    }

    /** Handle login form submission */
    public function login(Request $request, Response $response): Response
    {
        $body     = (array)$request->getParsedBody();
        $email    = trim($body['email']    ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['_flash']['error'] = 'Email and password are required.';
            return $response->withHeader('Location', '/admin/login')->withStatus(302);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !password_verify($password, $user->password)) {
            $_SESSION['_flash']['error'] = 'Incorrect email or password.';
            return $response->withHeader('Location', '/admin/login')->withStatus(302);
        }

        // Save user info in session — this is what AuthMiddleware checks
        $_SESSION['admin'] = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];

        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }

    /** Log out and destroy session */
    public function logout(Request $request, Response $response): Response
    {
        $_SESSION['admin'] = null;
        session_destroy();
        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    }
}
