<?php
    namespace Hans\Belajar\PHP\MVC\Controller;
    
    use Hans\Belajar\PHP\MVC\App\View;

    class HomeController {
        function index() {
            View::render('Home/index', [
                'title' => 'PHP Login Management'
            ]);
        }
    }