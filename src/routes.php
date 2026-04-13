<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\SurveyController;
use App\Middleware\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

// Home → redirect to admin login
$app->get('/', fn($req, $res) => $res->withHeader('Location', '/admin/login')->withStatus(302));

// ── Auth routes (no login required) ──────────────────────────────────────────
$app->get('/admin/login',   [AuthController::class, 'showLogin']);
$app->post('/admin/login',  [AuthController::class, 'login']);
$app->post('/admin/logout', [AuthController::class, 'logout']);

// ── Admin routes (login required — AuthMiddleware checks session) ─────────────
$app->group('/admin', function (RouteCollectorProxy $group) {
    $group->get('/dashboard',             [AdminController::class, 'dashboard']);
    $group->get('/upload',                [AdminController::class, 'showUpload']);
    $group->post('/upload',               [AdminController::class, 'processUpload']);
    $group->post('/toggle/{id}',          [AdminController::class, 'toggle']);
    $group->post('/delete/{id}',          [AdminController::class, 'delete']);
    $group->get('/results/{id}',          [AdminController::class, 'results']);
    $group->get('/results/{id}/download', [AdminController::class, 'downloadResults']);
})->add(AuthMiddleware::class);   // <-- protects every /admin route

// ── Public survey routes (no login needed for participants) ───────────────────
$app->get('/survey/{token}',          [SurveyController::class, 'show']);
$app->post('/survey/{token}',         [SurveyController::class, 'submit']);
$app->get('/survey/{token}/thankyou', [SurveyController::class, 'thankyou']);
