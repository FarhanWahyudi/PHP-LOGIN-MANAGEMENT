<?php
    namespace Hans\Belajar\PHP\MVC\App {
        function header(string $value) {
            echo $value;
        }
    }

    namespace Hans\Belajar\PHP\MVC\Controller {
        use PHPUnit\Framework\TestCase;
        use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
        use Hans\Belajar\PHP\MVC\Repository\UserRepository;
        use Hans\Belajar\PHP\MVC\Config\Database;
        use Hans\Belajar\PHP\MVC\Domain\User;

        class UserControllerTest extends TestCase {
            private UserController $userController;
            private UserRepository $userRepository;
            private SessionRepository $sessionRepository;

            protected function setUp(): void {
                $this->userController = new UserController();

                $this->sessionRepository = new SessionRepository(Database::getConnection());
                $this->userRepository = new UserRepository(Database::getConnection());
                $this->sessionRepository->deleteAll();
                $this->userRepository->deleteAll();
                
                putenv('mode=test');
            }

            public function testRegister() {
                $this->userController->register();

                $this->expectOutputRegex('[Register]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[Name]');
                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Register new User]');
            }

            public function testPostRegisterSuccess() {
                $_POST['id'] = 'hans';
                $_POST['name'] = 'hans';
                $_POST['password'] = 'hans123';

                $this->userController->postRegister();

                $this->expectOutputRegex('[Location: users/login]');
            }

            public function testPostRegisterValidationError() {
                $_POST['id'] = '';
                $_POST['name'] = 'hans';
                $_POST['password'] = 'hans123';

                $this->userController->postRegister();

                $this->expectOutputRegex('[Register]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[Name]');
                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Register new User]');
                $this->expectOutputRegex('[id, name, password can not blank]');
            }

            public function testPostRegisterDuplicate() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = 'hans123';

                $this->userRepository->save($user);

                $_POST['id'] = 'hans';
                $_POST['name'] = 'hans';
                $_POST['password'] = 'hans123';

                $this->userController->postRegister();

                $this->expectOutputRegex('[Register]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[Name]');
                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Register new User]');
                $this->expectOutputRegex('[email is already exists]');
            }

            public function testLogin() {
                $this->userController->login();

                $this->expectOutputRegex('[Login User]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[password]');
            }
            
            public function testLoginSuccess() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('hans123', PASSWORD_BCRYPT);

                $this->userRepository->save($user);

                $_POST['id'] = 'hans';
                $_POST['password'] = 'hans123';

                $this->userController->postLogin();

                $this->expectOutputRegex('[Location: /]');
            }

            public function testLoginValidationError() {
                $_POST['id'] = '';
                $_POST['password'] = '';

                $this->userController->postLogin();

                $this->expectOutputRegex('[Login User]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[password]');
                $this->expectOutputRegex('[id, password can not blank]');
            }

            public function testLoginUserNotFound() {
                $_POST['id'] = 'hajar';
                $_POST['password'] = 'hajar';

                $this->userController->postLogin();

                $this->expectOutputRegex('[Login User]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[password]');
                $this->expectOutputRegex('[id or password is wrong]');
            }

            public function testLoginWrongPassword() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('hans123', PASSWORD_BCRYPT);

                $this->userRepository->save($user);

                $_POST['id'] = 'hans';
                $_POST['password'] = 'hajar';

                $this->userController->postLogin();

                $this->expectOutputRegex('[Login User]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[password]');
                $this->expectOutputRegex('[id or password is wrong]');
            }
        }
    }
?>