<?php

require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PatientController.php';

function dispatch(PDO $pdo): bool
{
    $method = $_SERVER['REQUEST_METHOD'];
    $body   = $_REQUEST['body'] ?? null;

    $authController     = new AuthController($pdo);
    $patientController = new PatientController($pdo);

    // Normalize URL
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = rtrim($uri, '/');

    // Strip base path
    $basePath = '/jwt';
    if (str_starts_with($url, $basePath)) {
        $url = substr($url, strlen($basePath));
    }

    // ---------- ROUTES ----------

    if ($method === 'POST' && $url === '/api/login') {
        $authController->login($body);
        return true;
    }

    if ($method === 'POST' && $url === '/api/register') {
        $authController->register($body);
        return true;
    }

    if ($method === 'GET' && $url === '/api/patients') {
        authMiddleware();
        $patientController->getPatients();
        return true;
    }

    if ($method === 'POST' && $url === '/api/patients') {
        authMiddleware();

        $patientController->createPatient(
            trim($body['name'] ?? ''),
            (int) ($body['age'] ?? 0),
            trim($body['gender'] ?? ''),
            trim($body['phone'] ?? ''),
            trim($body['address'] ?? '')
        );

        return true;
    }

    return false; // not matched
}
