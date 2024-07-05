<?php
    namespace Hans\Belajar\PHP\MVC\Controller;

    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Domain\Session;
    use Hans\Belajar\PHP\MVC\Domain\User;
    use Hans\Belajar\PHP\MVC\Service\SessionService;

    class HomeControllerTest extends TestCase {

        private HomeController $homeController;
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void {
            $connection = Database::getConnection();
            $this->homeController = new HomeController();
            $this->sessionRepository = new SessionRepository($connection);
            $this->userRepository = new UserRepository($connection);

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
        }

        public function testGuest() {
            $this->homeController->index();   

            $this->expectOutputRegex('[Login Management]');
        }

        public function testUserLogin() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = 'hans123';
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->homeController->index();

            $this->expectOutputRegex('[Hello hans]');
        }

        // public function test() {

        // }
    }
?>