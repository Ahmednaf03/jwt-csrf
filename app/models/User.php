<?php 
require_once '../app/core/Database.php';

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }


    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($email, $hashedPassword,$role) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password,role) VALUES (:email, :password,:role)");
        $stmt->execute([':email' => $email, ':password' => $hashedPassword,':role'=>$role]);
        return $this->getByEmail($email);
    }

    // get user based on id for RBAC
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userRole;
    }

    
    public function getUserByRefreshToken($token) {
        $stmt = $this->pdo->prepare("SELECT id, email, role, refresh_token_expires_at  FROM users WHERE refresh_token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateRefreshToken($id, $token, $expiresAt) {
        $stmt = $this->pdo->prepare("UPDATE users SET refresh_token = :token, refresh_token_expires_at = :expiresAt WHERE id = :id");
        $stmt->execute([':token' => $token, ':expiresAt' => $expiresAt, ':id' => $id]);
    }

    public function refreshRotation($id, $token, $expiresAt) {
        $stmt = $this->pdo->prepare("UPDATE users SET refresh_token = :token, refresh_token_expires_at = :expiresAt WHERE id = :id");
        $stmt->execute([':token' => $token, ':expiresAt' => $expiresAt, ':id' => $id]);
    }
}

?>