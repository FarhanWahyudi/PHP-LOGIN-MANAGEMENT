<?php
    namespace Hans\Belajar\PHP\MVC\Controller {

        require_once __DIR__ . '/../Helper/helper.php';

        use PHPUnit\Framework\TestCase;
        use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
        use Hans\Belajar\PHP\MVC\Repository\UserRepository;
        use Hans\Belajar\PHP\MVC\Service\SessionService;
        use Hans\Belajar\PHP\MVC\Config\Database;
        use Hans\Belajar\PHP\MVC\Domain\User;
        use Hans\Belajar\PHP\MVC\Domain\Session;

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
                $this->expectOutputRegex('[X-HNS-SESSION: ]');
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

            public function testLogout() {
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

                $this->userController->logout();

                $this->expectOutputRegex('[Location: /]');
                $this->expectOutputRegex('[X-HNS-SESSION: ]');
            }

            public function testUpdateProfile() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

                $this->userController->updateProfile();

                $this->expectOutputRegex('[Profile]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[name]');
                $this->expectOutputRegex('[hans]');
            }

            public function testUpdateProfileSuccess() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

                $_POST['name'] = 'budi';
                $this->userController->postUpdateProfile();

                $this->expectOutputRegex('[Location: /]');

                $result = $this->userRepository->findById($user->id);
                $this->assertEquals('budi', $result->name);
            }

            public function testUpdateProfileValidationError() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

                $_POST['name'] = '';
                $this->userController->postUpdateProfile();

                $this->expectOutputRegex('[Profile]');
                $this->expectOutputRegex('[id]');
                $this->expectOutputRegex('[name]');
                $this->expectOutputRegex('[hans]');
                $this->expectOutputRegex('[name can not blank]');
            }

            public function testUpdatePassword() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
                
                $this->userController->updatePassword();

                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[Old Password]');
                $this->expectOutputRegex('[New Password]');
                $this->expectOutputRegex('[hans]');
            }

            public function testPostUpdatePasswordSuccess() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
                
                $_POST['oldPassword'] = 'rahasia';
                $_POST['newPassword'] = '123';

                $this->userController->postUpdatePassword();

                $this->expectOutputRegex('[Location: /]');

                $result = $this->userRepository->findById($user->id);
                $this->assertTrue(password_verify('123', $result->password));
            }

            public function testPostUpdatePasswordValidationError() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
                
                $_POST['oldPassword'] = '';
                $_POST['newPassword'] = '';

                $this->userController->postUpdatePassword();

                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[hans]');
                $this->expectOutputRegex('[id, old password, and new password can not blank]');
            }
            
            public function testPostUpdatePasswordWrongOldPassword() {
                $user = new User();
                $user->id = 'hans';
                $user->name = 'hans';
                $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
                $this->userRepository->save($user);

                $session = new Session();
                $session->id = uniqid();
                $session->userId = $user->id;
                $this->sessionRepository->save($session);

                $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
                
                $_POST['oldPassword'] = 'admin#123';
                $_POST['newPassword'] = '123';

                $this->userController->postUpdatePassword();

                $this->expectOutputRegex('[Password]');
                $this->expectOutputRegex('[Id]');
                $this->expectOutputRegex('[hans]');
                $this->expectOutputRegex('[password is wrong]');
            }
        }
    }
?>