<?php
    namespace Hans\Belajar\PHP\MVC\App {
        function header(string $value) {
            echo $value;
        }
    }

    namespace Hans\Belajar\PHP\MVC\Middleware {

        use PHPUnit\Framework\TestCase;
        use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
        use Hans\Belajar\PHP\MVC\Repository\UserRepository;
        use Hans\Belajar\PHP\MVC\Config\Database;
        use Hans\Belajar\PHP\MVC\Domain\Session;
        use Hans\Belajar\PHP\MVC\Domain\User;
        use Hans\Belajar\PHP\MVC\Service\SessionService;

        class MustNotLoginMiddlewareTest extends TestCase {
            private MustNotLoginMiddleware $middleware;
            private SessionRepository $sessionRepository;
            private UserRepository $userRepository;

            protected function setUp(): void {
                $this->middleware = new MustNotLoginMiddleware();
                putenv('mode=test');

                $this->sessionRepository = new SessionRepository(Database::getConnection());
                $this->userRepository = new UserRepository(Database::getConnection());

                $this->sessionRepository->deleteAll();
                $this->userRepository->deleteAll();
            }

            public function testBeforeGuest() {
                $this->middleware->before();
                $this->expectOutputString('');
            }

            public function testBeforeLoginUser() {
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

                $this->middleware->before();
                $this->expectOutputRegex('[Location: /]');
            }
        }
    }
?>