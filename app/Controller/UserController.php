<?php
    namespace Hans\Belajar\PHP\MVC\Controller;

    use Hans\Belajar\PHP\MVC\App\View;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Repository\SessionRepository;
    use Hans\Belajar\PHP\MVC\Service\UserService;
    use Hans\Belajar\PHP\MVC\Service\SessionService;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Model\UserLoginRequest;
    use Hans\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
    use Hans\Belajar\PHP\MVC\Exception\ValidationException;

    class UserController {
        private UserService $userService;
        private SessionService $sessionService;

        public function __construct() {
            $connection = Database::getConnection();
            $userRepository = new UserRepository($connection);
            $this->userService = new UserService($userRepository);

            $sessionRepository = new SessionRepository($connection);
            $this->sessionService = new SessionService($sessionRepository, $userRepository);
        }

        public function register() {
            View::render('User/register', [
                'title' => 'Register new User',
            ]);
        }

        public function postRegister() {
            $request = new UserRegisterRequest();
            $request->id = $_POST['id'];
            $request->name = $_POST['name'];
            $request->password = $_POST['password'];

            try {
                $this->userService->register($request);
                View::redirect('users/login');
            } catch (ValidationException $exception) {
                View::render('User/register', [
                    'title' => 'Register new User',
                    'error' => $exception->getMessage()
                ]);
            }
        }

        public function login() {
            View::render('User/login', [
                'title' => 'Login User'
            ]);
        }

        public function postLogin() {
            $request = new UserLoginRequest();
            $request->id = $_POST['id'];
            $request->password = $_POST['password'];

            try {
                $response = $this->userService->login($request);
                $this->sessionService->create($response->user->id);
                View::redirect('/');
            } catch (ValidationException $exception) {
                View::render('User/login', [
                    'title' => 'Login User',
                    'error' => $exception->getMessage()
                ]);
            }
        }

        public function logout() {
            $this->sessionService->destroy();
            View::redirect('/');
        }

        public function updateProfile() {
            $user = $this->sessionService->current();
            View::render('User/update', [
                'title' => 'Update user profile',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name
                ]
            ]);
        }

        public function postUpdateProfile() {
            $user = $this->sessionService->current();

            $request = new UserProfileUpdateRequest();
            $request->id = $user->id;
            $request->name = $_POST['name'];

            try {
                $this->userService->updateProfile($request);
                View::redirect('/');
            } catch (ValidationException $exception) {
                View::render('User/update', [
                    'title' => 'Update user profile',
                    'error' => $exception->getMessage(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ]
                ]);
            }
        }
    }
?>