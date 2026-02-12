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
        $stmt = $this->pdo->prepare("SELECT 
            rt.id AS refresh_id,
            rt.user_id,
            rt.expires_at,
            u.email,
            u.role
        FROM refresh_tokens rt
        JOIN users u ON rt.user_id = u.id
        WHERE rt.token_hash = :token
        ");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function insertRefreshToken($userId, $tokenHash,  $expiresAt) {
        $stmt = $this->pdo->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at)
                                    VALUES (:user_id, :token_hash, :expires_at)
                                    ON DUPLICATE KEY UPDATE
                                     token_hash = VALUES(token_hash),
                                    expires_at = VALUES(expires_at);");
        $stmt->execute([
        ':user_id'   => $userId,
        ':token_hash'=> $tokenHash,
        ':expires_at'=> $expiresAt
    ]);
    }

    public function refreshRotation($refreshId, $newHash, $newExpiry) {
        $stmt = $this->pdo->prepare("
        UPDATE refresh_tokens
        SET token_hash = :token,
            expires_at = :expiresAt
        WHERE id = :id
    ");
        $stmt->execute([
        ':token'     => $newHash,
        ':expiresAt' => $newExpiry,
        ':id'        => $refreshId
    ]);
    }
}

?>