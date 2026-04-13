<?php
declare(strict_types=1);

use Slim\Views\Twig;

return [
    Twig::class => function () {
        $twig = Twig::create(__DIR__ . '/../../templates', [
            'cache'       => false,
            'auto_reload' => true,
            'debug'       => true,
        ]);

        $env = $twig->getEnvironment();
        $env->addGlobal('flash',    $_SESSION['_flash'] ?? []);
        $env->addGlobal('app_name', $_ENV['APP_NAME']   ?? 'SurveyFlow');
        $_SESSION['_flash'] = [];

        return $twig;
    },
];