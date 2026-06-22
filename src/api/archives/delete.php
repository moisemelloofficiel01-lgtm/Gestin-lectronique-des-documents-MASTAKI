<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['archive_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'archive_id is required']);
    exit;
}

$archive_id = (int)$data['archive_id'];

// Check if it exists
$check = mysqli_query($conn, "SELECT document_id FROM archive_documents WHERE archive_id = $archive_id");
if (mysqli_num_rows($check) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Archive not found']);
    exit;
}

$sql = "DELETE FROM archive_documents WHERE archive_id = ?";

try {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $archive_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Archive deleted successfully']);
    } else {
        throw new Exception(mysqli_error($conn));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
