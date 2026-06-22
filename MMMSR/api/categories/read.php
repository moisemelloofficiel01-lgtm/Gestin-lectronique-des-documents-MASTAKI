<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

try {
    $sql = "SELECT * FROM document_categories ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $categories]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
mysqli_close($conn);
?>
