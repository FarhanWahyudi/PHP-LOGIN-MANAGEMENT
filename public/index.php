<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use Hans\Belajar\PHP\MVC\App\Router;
    use Hans\Belajar\PHP\MVC\Controller\HomeController;

    Router::add('GET', '/', HomeController::class, 'index', []);

    Router::run();