<?php
    namespace Hans\Belajar\PHP\MVC\Service;
    
    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Exception\ValidationException;
    use Hans\Belajar\PHP\MVC\Domain\User;

    class UserServiceTest extends TestCase {
        private UserRepository $userRepository;
        private UserService $userService;

        protected function setUp(): void {
            $connection = Database::getConnection();
            $this->userRepository = new UserRepository($connection);
            $this->userService = new UserService($this->userRepository);

            $this->userRepository->deleteAll();
        }

        public function testRegisterSuccess() {
            $request = new UserRegisterRequest();
            $request->id = 'hans';
            $request->name = 'hans';
            $request->password = 'hans123';

            $response = $this->userService->register($request);

            self::assertEquals($request->id, $response->user->id);
            self::assertEquals($request->name, $response->user->name);
            self::assertNotEquals($request->password, $response->user->password);
            self::assertTrue(password_verify($request->password, $response->user->password));
        }

        public function testRegisterFailed() {
            $this->expectException(ValidationException::class);
            
            $request = new UserRegisterRequest();
            $request->id = '';
            $request->name = '';
            $request->password = '';
            
            $this->userService->register($request);
        }

        public function testRegisterDuplicate() {
            $this->expectException(ValidationException::class);

            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = 'hans123';

            $this->userRepository->save($user);

            $request = new UserRegisterRequest();
            $request->id = 'hans';
            $request->name = 'hans';
            $request->password = 'hans123';

            $this->userService->register($request);
        }
    }
?>