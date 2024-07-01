<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use Hans\Belajar\PHP\MVC\App\Router;
    use Hans\Belajar\PHP\MVC\Controller\HomeController;
    use Hans\Belajar\PHP\MVC\Controller\UserController;
    use Hans\Belajar\PHP\MVC\Config\Database;

    Database::getConnection('prod');

    // home
    Router::add('GET', '/', HomeController::class, 'index', []);

    // home
    Router::add('GET', '/users/register', UserController::class, 'register', []);
    Router::add('POST', '/users/register', UserController::class, 'postRegister', []);

    Router::run();