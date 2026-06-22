<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['document_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit;
}

$id = (int)$_POST['document_id'];

// Get file path first
$sql = "SELECT chemin_stockage FROM documents WHERE document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $filePath = __DIR__ . '/../../uploads/documents/' . basename($row['chemin_stockage']);
    
    // Delete record
    $deleteSql = "DELETE FROM documents WHERE document_id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    
    if (mysqli_stmt_execute($deleteStmt)) {
        // Delete file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        echo json_encode(['status' => 'success', 'message' => 'Document supprimé avec succès']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Document non trouvé']);
}

mysqli_close($conn);
?>