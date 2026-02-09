<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

//Load env
require_once __DIR__ . '/../config/config.php';
loadEnv();

// DB connection
require_once __DIR__ . '/../app/core/Database.php';
$pdo = Database::connect();

// Global middleware
require_once __DIR__ . '/../app/middleware/jsonMiddleware.php';
jsonMiddleware();

// Dispatch router
require_once __DIR__ . '/../app/core/Router.php';

$handled = dispatch($pdo);

if (!$handled) {
    errorResponse('Endpoint not found', 404);
}
