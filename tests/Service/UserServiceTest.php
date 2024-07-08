<?php
    namespace Hans\Belajar\PHP\MVC\Service;
    
    use PHPUnit\Framework\TestCase;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Model\UserLoginRequest;
    use Hans\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
    use Hans\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
    use Hans\Belajar\PHP\MVC\Exception\ValidationException;
    use Hans\Belajar\PHP\MVC\Domain\User;
    use Hans\Belajar\PHP\MVC\Domain\Session;

    class UserServiceTest extends TestCase {
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;
        private UserService $userService;

        protected function setUp(): void {
            $connection = Database::getConnection();
            $this->userRepository = new UserRepository($connection);
            $this->sessionRepository = new SessionRepository($connection);
            $this->userService = new UserService($this->userRepository);

            $this->sessionRepository->deleteAll();
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

        public function testLoginNotFound() {
            $this->expectException(ValidationException::class);

            $request = new UserLoginRequest();
            $request->id = 'hans';
            $request->password = 'hans123';

            $this->userService->login($request);
        }

        public function testLoginWrongPassword() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            
            $this->userRepository->save($user);
            
            $this->expectException(ValidationException::class);

            $request = new UserLoginRequest();
            $request->id = 'hans';
            $request->password = 'has123';

            $this->userService->login($request);
        }

        public function testLoginSuccess() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            
            $this->userRepository->save($user);
            
            $request = new UserLoginRequest();
            $request->id = 'hans';
            $request->password = 'hans123';

            $response = $this->userService->login($request);

            $this->assertEquals($request->id, $user->id);
            $this->assertTrue(password_verify($request->password, $response->user->password));
        }

        public function testUpdateSuccess() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $request = new UserProfileUpdateRequest();
            $request->id = $user->id;
            $request->name = 'budi';
            $this->userService->updateProfile($request);

            $result = $this->userRepository->findById($user->id);

            $this->assertEquals($result->name, $request->name);
        }

        public function testUpdateValidationError() {
            $this->expectException(ValidationException::class);
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $request = new UserProfileUpdateRequest();
            $request->id = $user->id;
            $request->name = '';
            $this->userService->updateProfile($request);
        }

        public function testUpdateNotFound() {
            $this->expectException(ValidationException::class);

            $request = new UserProfileUpdateRequest();
            $request->id = 'kaka';
            $request->name = 'akmal';
            $this->userService->updateProfile($request);
        }

        public function testUpdatePasswordSuccess() {
            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $request = new UserPasswordUpdateRequest();
            $request->id = $user->id;
            $request->oldPassword = 'hans123';
            $request->newPassword = '123';

            $this->userService->updatePassword($request);

            $result = $this->userRepository->findById($user->id);

            $this->assertTrue(password_verify($request->newPassword, $result->password));
        }

        public function testUpdatePasswordValidationError() {
            $this->expectException(ValidationException::class);

            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $request = new UserPasswordUpdateRequest();
            $request->id = $user->id;
            $request->oldPassword = '';
            $request->newPassword = '';

            $this->userService->updatePassword($request);
        }

        public function testUpdatePasswordWrongOldPassword() {
            $this->expectException(ValidationException::class);

            $user = new User();
            $user->id = 'hans';
            $user->name = 'hans';
            $user->password = password_hash('hans123', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $request = new UserPasswordUpdateRequest();
            $request->id = $user->id;
            $request->oldPassword = '123';
            $request->newPassword = 'hanso';

            $this->userService->updatePassword($request);
        }

        public function testUpdatePasswordNotFound() {
            $this->expectException(ValidationException::class);

            $request = new UserPasswordUpdateRequest();
            $request->id ='hanso';
            $request->oldPassword = '123';
            $request->newPassword = 'hanso';

            $this->userService->updatePassword($request);
        }
    }
?>