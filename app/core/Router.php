<?php

require_once __DIR__ . '/../middleware/authMiddleware.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PatientController.php';

// routing function
function dispatch(PDO $pdo): bool{
    //capture method and body
    $method = $_SERVER['REQUEST_METHOD'];
    $body   = $_REQUEST['body'] ?? null;
    // controller objects
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
    if ($method === 'POST' && $url === '/api/refresh') {
    $authController->refresh();
    return true;
    }



    // --------- Patients ----------

    // GET all patients
    if ($method === 'GET' && $url === '/api/patients') {
         $user = authMiddleware(); 
    
        if($user['role'] === 'user') {
            $authController->unAuthorized();
            return true;
        }
        $patientController->getPatients();
        return true;
    }

    if ($method === 'GET' && preg_match('#^/api/patients/(\d+)$#', $url, $matches)) {
       $user = authMiddleware();
    
        if($user['role'] === 'user') {
             $patientController->getPatientById($user['id']);
             return true;
        } else{
        $id = (int)$matches[1];
        $patientController->getPatientById($id);
        return true;
        }
    }

    if ($method === 'POST' && $url === '/api/patients') {
        $user = authMiddleware(); 
    
        if($user['role'] === 'user') {
            $authController->unAuthorized();
            return true;
        }

        $patientController->createPatient(
            trim($body['name'] ?? ''),
            (int) ($body['age'] ?? 0),
            trim($body['gender'] ?? ''),
            trim($body['phone'] ?? ''),
            trim($body['address'] ?? '')
        );

        return true;
    }
    if ($method === 'PUT' && preg_match('#^/api/patients/(\d+)$#', $url, $matches)) {
         $user = authMiddleware(); 
    
        if($user['role'] === 'user') {
            $authController->unAuthorized();
            return true;
        }
        $id = (int)$matches[1];
        $patientController->updatePatient($id, $body);
         exit;
        }

     if ($method === 'DELETE' && preg_match('#^/api/patients/(\d+)$#', $url, $matches)) {
        $user = authMiddleware(); 
    
        if($user['role'] === 'user') {
            $authController->unAuthorized();
            return true;
        }
        $id = (int)$matches[1];
        $patientController->deletePatient($id);
         exit;
        }

    return false; // not matched
}
