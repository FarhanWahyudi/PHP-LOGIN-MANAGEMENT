<?php
    namespace Hans\Belajar\PHP\MVC\Repository;

    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Domain\Session;
    use Hans\Belajar\PHP\MVC\Domain\User;

    class SessionRepositoryTest extends TestCAse {
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void {
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->userRepository = new UserRepository(Database::getConnection());

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();

            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = 'rahasia';
            $this->userRepository->save($user);
        }

        public function testSaveSuccess() {
            $session = new Session();
            $session->id = uniqid();
            $session->userId = 'hans';

            $this->sessionRepository->save($session);

            $result = $this->sessionRepository->findById($session->id);

            $this->assertEquals($session->id, $result->id);
            $this->assertEquals($session->userId, $result->userId);
        }

        public function testDeleteByIdSuccess() {
            $session = new Session();
            $session->id = uniqid();
            $session->userId = 'hans';

            $this->sessionRepository->save($session);
            $this->sessionRepository->deleteById($session->id);
            $result = $this->sessionRepository->findById($session->id);

            $this->assertNull($result);
        }

        public function testFindByIdNotFound() {
            $result = $this->sessionRepository->findById('not found');
            $this->assertNull($result);
        }
    }
?>