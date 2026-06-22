<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../notifications/schema_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}

$document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;

if ($document_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID document invalide']);
    exit;
}

// Check if document exists
$sql = "SELECT nom_fichier_original FROM documents WHERE document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $document_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    echo json_encode(['status' => 'error', 'message' => 'Document non trouvé']);
    exit;
}

// Update status to VALIDE (or original status if tracked, but VALIDE is safe)
// Also remove from archive_documents? Or keep history? Usually we keep history but mark as unarchived.
// For simplicity, we just change the status in documents table.
$updateSql = "UPDATE documents SET statut = 'VALIDE', date_archivage = NULL WHERE document_id = ?";
$updateStmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($updateStmt, "i", $document_id);

if (mysqli_stmt_execute($updateStmt)) {
    // Notify
    ensureNotificationsTableSchema($conn);
    $userId = $_SESSION['user_id'];
    $notifTitle = "Document Désarchivé";
    $notifMessage = "Le document '" . $doc['nom_fichier_original'] . "' a été désarchivé.";
    $notifType = "info";
    
    $notifSql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $notifStmt = mysqli_prepare($conn, $notifSql);
    mysqli_stmt_bind_param($notifStmt, "isss", $userId, $notifTitle, $notifMessage, $notifType);
    mysqli_stmt_execute($notifStmt);

    echo json_encode(['status' => 'success', 'message' => 'Document désarchivé avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
