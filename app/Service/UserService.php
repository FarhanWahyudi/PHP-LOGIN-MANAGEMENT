<?php
    namespace Hans\Belajar\PHP\MVC\Service;

    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterResponse;
    use Hans\Belajar\PHP\MVC\Repository\UserRepository;
    use Hans\Belajar\PHP\MVC\Config\Database;
    use Hans\Belajar\PHP\MVC\Domain\User;
    use Hans\Belajar\PHP\MVC\Exception\ValidationException;

    class UserService {
        private UserRepository $userRepository;

        public function __construct(UserRepository $userRepository) {
            $this->userRepository = $userRepository;
        }

        public function register(UserRegisterRequest $request): UserRegisterResponse {
            $this->validateUserRegistrationRequest($request);

            try {
                Database::beginTransaction();

                $user = $this->userRepository->findById($request->id);
                if ($user) {
                    throw new ValidationException('email is already exists');
                }
    
                $user = new User();
                $user->id = $request->id;
                $user->name = $request->name;
                $user->password = password_hash($request->password, PASSWORD_BCRYPT);
    
                $this->userRepository->save($user);
    
                $response = new UserRegisterResponse();
                $response->user = $user;

                Database::commitTransaction();

                return $response;
            } catch(\Exception $exception) {
                Database::rollbackTransaction();
                throw $exception;
            }
        }

        private function validateUserRegistrationRequest(UserRegisterRequest $request) {
            if ($request->id == null || $request->name == null || $request->password == null || trim($request->id) == '' || trim($request->name) == '' || trim($request->password) == '') {
                throw new ValidationException('id, name, password can not blank');
            }
        }
    }
?>