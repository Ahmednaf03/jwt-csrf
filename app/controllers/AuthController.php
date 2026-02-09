<?php 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/JWT.php';
class AuthController {

 private User $userModel;
    // db initialisation through constructor 
    public function __construct(PDO $pdo){
        $this->userModel = new User($pdo);
    }

    public function login($data) {
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($email === '' || $password === '') {
            errorResponse('Email and password are required', 400);
            return;
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            errorResponse('Invalid email or password', 401);
            return;
        }

        // Generate JWT
        $token = generateToken($user["id"], $user["email"]);
        successResponse(['token' => $token], 'Login successful', 200);
    }


    public function register($data) {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($email === '' || $password === '') {
            errorResponse('Email and password are required', 400);
            return;
        }

        if ($this->userModel->getByEmail($email)) {
            errorResponse('Email already exists', 409);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->create($email, $hashedPassword);
        successResponse(null, 'Registration successful', 201);
    }

}

?>