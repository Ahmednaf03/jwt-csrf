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

        // update refresh token right away after login 
        $refreshToken = bin2hex(random_bytes(64));
        $refreshTokenHash = password_hash($refreshToken, PASSWORD_DEFAULT);

            $refreshExpiry = date(
            'Y-m-d H:i:s',
             time() + (60 * 60 * 24 * 7) // 7 days
                );
        // insert into refresh_token table
        $this->userModel->insertRefreshToken($user['id'], $refreshTokenHash, $refreshExpiry);
        //set in cookie for generating new access tokens later
        setcookie(
    'refresh_token',
    $refreshTokenHash,
    [
        'expires'  => time() + (60 * 60 * 24 * 7), // 7 days validity 
        'path'     => '/',
        'secure'   => true,      // HTTPS required
        'httponly' => true,      // JS cannot read
        'samesite' => 'Strict'   // restricts other site from accessing this cookie
    ]
);


    }


public function refresh(){
    //check cookie for refresh token
    if (!isset($_COOKIE['refresh_token'])) {
        errorResponse('Refresh token missing', 401);
    }

    $refreshToken = $_COOKIE['refresh_token'];
    // $hashed = hash('sha256', $refreshToken);

    $stmt = $this->userModel->getUserByRefreshToken(
        $refreshToken
    );

    $user = $stmt;

    if (!$user) {
        errorResponse('Invalid refresh token', 401);
    }

    if (strtotime($user['expires_at']) < time()) {
        errorResponse('Refresh token expired', 401);
    }

    // Issue NEW access token
    $accessToken = generateToken($user["user_id"], $user["email"]);

    successResponse([
        'access_token' => $accessToken,
        'expires_in' => 900
    ], 'Refresh successful', 200);

/* refresh token rotation once new access token
    is issued, create new refresh token and invalidate old one
*/
$newRefreshToken = bin2hex(random_bytes(64));
$newHash = hash('sha256', $newRefreshToken);

$newExpiry = date(
    'Y-m-d H:i:s',
    time() + (60 * 60 * 24 * 7)
);

// update the refresh token
$stmt = $this->userModel->refreshRotation($user['refresh_id'], $newHash, $newExpiry);

setcookie(
    'refresh_token',
    $newHash,
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