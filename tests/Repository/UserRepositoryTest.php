<?php
    namespace Hans\Belajar\PHP\MVC\Repository;

    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Domain\User;

    class UserRepositoryTest extends TestCase {
        private UserRepository $userRepository;
        
        public function setUp(): void {
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();
        }

        public function testSaveSuccess() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = '1234';

            $this->userRepository->save($user);

            $result = $this->userRepository->findById($user->id);

            $this->assertEquals($user->id, $result->id);
            $this->assertEquals($user->name, $result->name);
            $this->assertEquals($user->password, $result->password);
        }

        public function testFindByIdNotFound() {
            $user = $this->userRepository->findById('notfound');
            $this->assertNull($user);
        }

        public function testUpdate() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = 'hans1234';
            $this->userRepository->save($user);

            $user->name = 'budi';
            $this->userRepository->update($user);

            $result = $this->userRepository->findById($user->id);

            $this->assertEquals($user->id, $result->id);
            $this->assertEquals($user->name, $result->name);
            $this->assertEquals($user->password, $result->password);
        }
    }
?>