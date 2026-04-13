<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$driver  = $_ENV['DB_DRIVER'] ?? 'sqlite';

if ($driver === 'sqlite') {
    $dbPath = __DIR__ . '/../../' . ($_ENV['DB_DATABASE'] ?? 'database/surveyflow.sqlite');
    // Auto-create the SQLite file if it doesn't exist
    if (!file_exists(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }
    if (!file_exists($dbPath)) {
        touch($dbPath);
    }
    $capsule->addConnection(['driver' => 'sqlite', 'database' => $dbPath, 'prefix' => '']);
} else {
    $capsule->addConnection([
        'driver'    => $driver,
        'host'      => $_ENV['DB_HOST']     ?? '127.0.0.1',
        'port'      => $_ENV['DB_PORT']     ?? '3306',
        'database'  => $_ENV['DB_DATABASE'] ?? 'surveyflow',
        'username'  => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ]);
}

$capsule->setAsGlobal();
$capsule->bootEloquent();
