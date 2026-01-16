<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Features\Auth\AuthRoutes;
use App\Features\Post\PostRoutes;
use App\Helpers\Response;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

if ($_ENV['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

if ($_ENV['APP_ENV'] === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');

$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];
$segments = array_values(array_filter(explode('/', $path)));

try {
    AuthRoutes::handle($path, $method);
    PostRoutes::handle($path, $method, $segments);

    Response::json(['error' => 'Tidak ditemukan'], 404);
} catch (Throwable $e) {

    if ($_ENV['APP_ENV'] === 'development') {
        Response::json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }

    Response::json(['error' => 'Kesalahan tak terduga'], 500);
}
