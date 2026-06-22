<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schema_helper.php';

// Ensure table schema is correct (just in case)
ensureFournisseursTableSchema($conn);

try {
    $id = $_POST['fournisseur_id'];
    if (!$id) {
        throw new Exception("ID manquant");
    }

    $sql = "DELETE FROM fournisseurs WHERE fournisseur_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Fournisseur supprimé avec succès']);
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
