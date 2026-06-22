<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Test headers reçus et configuration serveur
$headers = [];
foreach ($_SERVER as $k => $v) {
    if (str_starts_with($k, 'HTTP_')) {
        $headers[$k] = $v;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "CORS test OK - PHP s'execute correctement",
    "server" => [
        "php_version" => phpversion(),
        "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        "server_name" => $_SERVER['SERVER_NAME'] ?? 'N/A',
        "document_root" => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        "script_filename" => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
        "request_uri" => $_SERVER['REQUEST_URI'] ?? 'N/A',
        "request_method" => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        "mod_rewrite" => function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()),
        "mod_headers" => function_exists('apache_get_modules') && in_array('mod_headers', apache_get_modules()),
    ],
    "cors_headers" => [
        "Access-Control-Allow-Origin" => $origin,
        "Access-Control-Allow-Credentials" => "true",
        "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS",
        "Access-Control-Allow-Headers" => "Content-Type, Authorization, X-Requested-With, Accept, Origin",
    ],
    "request_headers" => $headers,
], JSON_PRETTY_PRINT);
