<?php
    namespace Hans\Belajar\PHP\MVC\Middleware;

    use Hans\Belajar\PHP\MVC\Service\SessionService;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\App\View;
    use Hans\Belajar\PHP\MVC\Config\Database;

    class MustNotLoginMiddleware implements Middleware {
        private SessionService $sessionService;

        public function __construct() {
            $sessionRepository = new SessionRepository(Database::getConnection());
            $userRepository = new UserRepository(Database::getConnection());

            $this->sessionService = new SessionService($sessionRepository, $userRepository);
        }

        function before(): void {
            $user = $this->sessionService->current();

            if ($user) {
                View::redirect('/');
            }
        }
    }
?>