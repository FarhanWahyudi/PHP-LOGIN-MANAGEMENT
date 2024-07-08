<?php
    namespace Hans\Belajar\PHP\MVC\Service;

    use Hans\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use Hans\Belajar\PHP\MVC\Model\UserRegisterResponse;
    use Hans\Belajar\PHP\MVC\Model\UserLoginRequest;
    use Hans\Belajar\PHP\MVC\Model\UserLoginResponse;
    use Hans\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
    use Hans\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
    use Hans\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
    use Hans\Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
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

        public function login(UserLoginRequest $request): UserLoginResponse {
            $this->validateUserLoginRequest($request);

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException('id or password is wrong');
            }

            if (password_verify($request->password, $user->password)) {
                $response = new UserLoginResponse();
                $response->user = $user;
                return $response;
            } else {
                throw new ValidationException('id or password is wrong');
            }
        }

        private function validateUserLoginRequest(UserLoginRequest $request) {
            if ($request->id == null || $request->password == null || trim($request->id) == '' || trim($request->password) == '') {
                throw new ValidationException('id, password can not blank');
            }
        }

        public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse {
            $this->validateUserProfileUpdateRequest($request);

            try {
                Database::beginTransaction();

                $user = $this->userRepository->findById($request->id);
                if ($user == null) {
                    throw new ValidationException('User is not found');
                }

                $user->name = $request->name;
                $this->userRepository->update($user);

                Database::commitTransaction();

                $response = new UserProfileUpdateResponse();
                $response->user = $user;
                return $response;
            } catch (\Exception $exception) {
                Database::rollbackTransaction();
                throw $exception;
            }
        }

        private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request) {
            if ($request->id == null || $request->name == null || trim($request->id) == '' || trim($request->name) == '') {
                throw new ValidationException('name can not blank');
            }
        }

        public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse {
            $this->validateUpdatePasswordRequest($request);

            try {
                Database::beginTransaction();

                $user = $this->userRepository->findById($request->id);

                if (!$user) {
                    throw new ValidationException('user is not found');
                }

                if (!password_verify($request->oldPassword, $user->password)) {
                    throw new ValidationException('password is wrong');
                }

                $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
                $this->userRepository->update($user);

                Database::commitTransaction();

                $response = new UserPasswordUpdateResponse();
                $response->user = $user;
                return $response;
            } catch (\Exception $exception) {
                Database::rollbackTransaction();
                throw $exception;
            }
        }

        private function validateUpdatePasswordRequest(UserPasswordUpdateRequest $request) {
            if ($request->id == null || $request->oldPassword == null || $request->newPassword == null || trim($request->id) == '' || trim($request->oldPassword) == '' || trim($request->newPassword) == '') {
                throw new ValidationException('id, old password, and new password can not blank');
            }
        }
    }
?>