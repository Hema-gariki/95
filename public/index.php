<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

session_name($_ENV['SESSION_NAME'] ?? 'surveyflow_session');
session_set_cookie_params([
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require __DIR__ . '/../src/bootstrap/database.php';

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../src/bootstrap/container.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$twig = $container->get(Twig::class);
$app->add(TwigMiddleware::create($app, $twig));
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

require __DIR__ . '/../src/routes.php';

$app->run();