<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID missing']);
    exit;
}

$id = (int)$_GET['id'];

$sql = "SELECT a.*, d.nom_fichier_original, d.type_document as doc_type_document, 
               d.date_facture, d.montant_ttc, d.devise, f.nom_fournisseur
        FROM archive_documents a
        LEFT JOIN documents d ON a.document_id = d.document_id
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id
        WHERE a.archive_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Decode JSON fields
    foreach (['contributeurs', 'identifiants', 'politiques_acces'] as $jsonField) {
        if (isset($row[$jsonField]) && $row[$jsonField]) {
            $row[$jsonField] = json_decode($row[$jsonField], true);
        }
    }
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Archive not found']);
}

mysqli_close($conn);
?>
