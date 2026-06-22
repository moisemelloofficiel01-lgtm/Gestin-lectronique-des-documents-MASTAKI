<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schema_helper.php';

// Ensure table schema is correct
ensureFournisseursTableSchema($conn);

$id = $_GET['id'] ?? 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM fournisseurs WHERE fournisseur_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Fournisseur non trouvé']);
}

mysqli_close($conn);
?>
