<?php
// debug purpose
$_REQUEST['body']['__debug'] = 'middleware ran';

function jsonMiddleware(){
    // Always respond with JSON
    header('Content-Type: application/json; charset=UTF-8');

    $method = $_SERVER['REQUEST_METHOD'];

    // Enforce JSON only for write methods
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') === false) {
            http_response_code(415);
            echo json_encode([
                'error' => 'Content-Type must be application/json'
            ]);
            exit;
        }

        // Read raw body
        $rawInput = file_get_contents('php://input');

        if ($rawInput === '') {
            $_REQUEST['body'] = [];
            return;
        }

        // Decode JSON safely
        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid JSON payload'
            ]);
            exit;
        }

        // Attach decoded body to request
        $_REQUEST['body'] = $data;
    }
}
