<?php

require_once __DIR__ . '/../helpers/JWT.php'; // where validateJWT() lives
require_once __DIR__ . '/../helpers/Helper.php';
require_once __DIR__ . '/../core/Database.php';


function authMiddleware(){
    header('Content-Type: application/json; charset=UTF-8');
    $helper = new Helper(Database::connect());
    // Read Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    // cehck for header presence
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }

    // Extract Bearer token
    if (!preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid Authorization format']);
        exit;
    }

    $token = $matches[1];

    // Validate JWT 
    $payload = validateToken($token);

    if ($payload === false) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }

    if ($payload === "invalid credentials") {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
        exit;
    }

    // 4. Attach user data to request
    $_REQUEST['user'] = $payload;
    $user = $helper->getRoleAndId($payload['user_id']);

    return $user;
}
?>