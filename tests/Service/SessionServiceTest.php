<?php
    namespace Hans\Belajar\PHP\MVC\Service;

    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Domain\User;
    use Hans\Belajar\PHP\MVC\Domain\Session;

    function setcookie(string $name, string $value) {
        echo "$name: $value";
    }

    class SessionServiceTest extends TestCase {
        private SessionService $sessionService;
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void {
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();

            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = 'rahasia';
            $this->userRepository->save($user);
        }

        public function testCreate() {
            $session = $this->sessionService->create('hans');

            $this->expectOutputRegex("[X-HNS-SESSION: $session->id]");
            $result = $this->sessionRepository->findById($session->id);
            $this->assertEquals('hans', $result->userId);
        }

        public function testDestroy() {
            $session = new Session();
            $session->id = uniqid();
            $session->userId = 'hans';

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->sessionService->destroy();

            $this->expectOutputRegex("[X-HNS-SESSION: ]");

            $result = $this->sessionRepository->findById($session->id);
            $this->assertNull($result);
        }

        public function testCurrent() {
            $session = new Session();
            $session->id = uniqid();
            $session->userId = 'hans';

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $result = $this->sessionService->current();

            $this->assertEquals($session->userId, $result->id);
        }
    }
?>