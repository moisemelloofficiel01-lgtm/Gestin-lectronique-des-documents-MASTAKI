<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['status' => 'debug'];

try {
    require_once __DIR__ . '/../../config/db.php';
    $response['db_connection'] = 'OK';
    
    $res = mysqli_query($conn, "SELECT DATABASE()");
    $row = mysqli_fetch_row($res);
    $response['database_name'] = $row[0];
    
    $res = mysqli_query($conn, "SHOW TABLES LIKE 'fournisseurs'");
    $response['table_exists'] = mysqli_num_rows($res) > 0;
    
    if ($response['table_exists']) {
        $res = mysqli_query($conn, "DESCRIBE fournisseurs");
        $cols = [];
        while($row = mysqli_fetch_assoc($res)) {
            $cols[] = $row['Field'];
        }
        $response['columns'] = $cols;
    }

} catch (Throwable $e) {
    $response['error'] = $e->getMessage();
    $response['file'] = $e->getFile();
    $response['line'] = $e->getLine();
}

echo json_encode($response);
?>
