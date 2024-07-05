<?php
    namespace Hans\Belajar\PHP\MVC\Controller;
    
    use Hans\Belajar\PHP\MVC\App\View;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Service\SessionService;

    class HomeController {
        private SessionService $sessionService;

        public function __construct() {
            $connection = Database::getConnection();
            $userRepository = new UserRepository($connection);
            $sessionRepository = new SessionRepository($connection);

            $this->sessionService = new SessionService($sessionRepository, $userRepository);
        }

        function index() {
            $user = $this->sessionService->current();

            if ($user) {
                View::render('Home/dashboard', [
                    'title' => 'Dashboard',
                    'user' => [
                        'name' => $user->name
                        ]
                    ]);
                } else {
                View::render('Home/index', [
                    'title' => 'PHP Login Management'
                ]);
            }
        }
    }