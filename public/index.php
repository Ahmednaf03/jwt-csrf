<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

//Load env first to avoid undefined variables
require_once __DIR__ . '/../config/config.php';
loadEnv();

// DB connection with static function
require_once __DIR__ . '/../app/core/Database.php';
$pdo = Database::connect();

// Global middleware for processing JSON
require_once __DIR__ . '/../app/middleware/jsonMiddleware.php';
jsonMiddleware();

// Dispatch router
require_once __DIR__ . '/../app/core/Router.php';

$handled = dispatch($pdo);

if (!$handled) {
    errorResponse('Endpoint not found', 404);
}
