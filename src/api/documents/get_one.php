<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

// Include schema helper to ensure database structure is correct
require_once __DIR__ . '/../fournisseurs/schema_helper.php';

// Ensure the table and columns exist before querying
ensureFournisseursTableSchema($conn);

// Ensure required columns exist in fournisseurs table
$columnsToCheck = ['nom_fournisseur', 'ville', 'pays'];
foreach ($columnsToCheck as $col) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM fournisseurs LIKE '$col'");
    if (mysqli_num_rows($result) == 0) {
        $default = ($col == 'nom_fournisseur') ? " NOT NULL DEFAULT 'Fournisseur Inconnu'" : "";
        $alterSql = "ALTER TABLE fournisseurs ADD COLUMN $col VARCHAR(100)$default";
        mysqli_query($conn, $alterSql);
    }
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit;
}

$id = (int)$_GET['id'];

$sql = "SELECT d.*, f.nom_fournisseur, f.ville as fournisseur_ville, f.pays as fournisseur_pays 
        FROM documents d 
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id 
        WHERE d.document_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Document non trouvé']);
}

mysqli_close($conn);
?>