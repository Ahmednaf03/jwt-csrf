<?php 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/JWT.php';
class AuthController {

 private User $userModel;
    // db model initialisation through constructor 
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
        $refreshToken = bin2hex(random_bytes(64));
        $refreshTokenHash = hash('sha256', $refreshToken);

            $refreshExpiry = date(
            'Y-m-d H:i:s',
             time() + (60 * 60 * 24 * 7) // 7 days
                );

        $this->userModel->updateRefreshToken($user['id'], $refreshTokenHash, $refreshExpiry);
        setcookie(
    'refresh_token',
    $refreshToken,
    [
        'expires'  => time() + (60 * 60 * 24 * 7),
        'path'     => '/',
        'secure'   => true,      // HTTPS required
        'httponly' => true,      // JS cannot read
        'samesite' => 'Strict'   // or 'Lax'
    ]
);


    }


public function refresh(){
    if (!isset($_COOKIE['refresh_token'])) {
        errorResponse('Refresh token missing', 401);
    }

    $refreshToken = $_COOKIE['refresh_token'];
    $hashed = hash('sha256', $refreshToken);

    $stmt = $this->userModel->getUserByRefreshToken(
        $hashed
    );

    $user = $stmt;

    if (!$user) {
        errorResponse('Invalid refresh token', 401);
    }

    if (strtotime($user['refresh_token_expires_at']) < time()) {
        errorResponse('Refresh token expired', 401);
    }

    // Issue NEW access token
    $accessToken = generateToken($user["id"], $user["email"]);

    successResponse([
        'access_token' => $accessToken,
        'expires_in' => 900
    ], 'Refresh successful', 200);


$newRefreshToken = bin2hex(random_bytes(64));
$newHash = hash('sha256', $newRefreshToken);

$newExpiry = date(
    'Y-m-d H:i:s',
    time() + (60 * 60 * 24 * 7)
);

$stmt = $this->userModel->refreshRotation($user['id'], $newRefreshToken, $newExpiry);

setcookie(
    'refresh_token',
    $newRefreshToken,
    [
        'expires' => time() + (60 * 60 * 24 * 7),
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]
);

}



    public function register($data) {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $role = trim($data['role'] ?? '');

        if ($email === '' || $password === '') {
            errorResponse('Email and password are required', 400);
            return;
        }

        if ($this->userModel->getByEmail($email)) {
            errorResponse('Email already exists', 409);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->create($email, $hashedPassword,$role);
        successResponse(null, 'Registration successful', 201);
    }
    public function unAuthorized() {
        errorResponse('Unauthorized, admin access only', 401);
    }

}

?>