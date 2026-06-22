<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD');
if ($password === false) {
    $password = '';
}
$dbname = getenv('MYSQL_DATABASE') ?: 'my_database_gedo';

$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($conn, "utf8mb4");

$safeDbName = str_replace('`', '``', $dbname);
if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $safeDbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database initialization failed: ' . mysqli_error($conn)]);
    exit;
}

if (!mysqli_select_db($conn, $dbname)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database selection failed: ' . mysqli_error($conn)]);
    exit;
}

// Initialize tables if they don't exist
require_once __DIR__ . '/init_db.php';
initializeDatabaseSchema($conn);
?>
