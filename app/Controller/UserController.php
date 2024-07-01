<?php
    namespace Hans\Belajar\PHP\MVC\Controller;

    use Hans\Belajar\PHP\MVC\App\View;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Service\UserService;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Exception\ValidationException;

    class UserController {
        private UserService $userService;

        public function __construct() {
            $connection = Database::getConnection();
            $userRepository = new UserRepository($connection);
            $this->userService = new UserService($userRepository);
        }

        public function register() {
            View::render('User/Register', [
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
                View::render('User/Register', [
                    'title' => 'Register new User',
                    'error' => $exception->getMessage()
                ]);
            }
        }
    }
?>