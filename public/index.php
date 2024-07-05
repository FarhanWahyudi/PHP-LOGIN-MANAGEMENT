<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use Hans\Belajar\PHP\MVC\App\Router;
    use Hans\Belajar\PHP\MVC\Controller\HomeController;
    use Hans\Belajar\PHP\MVC\Controller\UserController;
    use Hans\Belajar\PHP\MVC\Config\Database;

    Database::getConnection('prod');

    // home controller
    Router::add('GET', '/', HomeController::class, 'index', []);

    // user controller
    Router::add('GET', '/users/register', UserController::class, 'register', []);
    Router::add('POST', '/users/register', UserController::class, 'postRegister', []);
    Router::add('GET', '/users/login', UserController::class, 'login', []);
    Router::add('POST', '/users/login', UserController::class, 'postLogin', []);
    Router::add('GET', '/users/logout', UserController::class, 'logOut', []);

    Router::run();