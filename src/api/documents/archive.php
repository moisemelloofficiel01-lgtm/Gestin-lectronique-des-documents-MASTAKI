<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/archive_schema.php';
require_once __DIR__ . '/../notifications/schema_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

ensureArchiveTableSchema($conn);
ensureNotificationsTableSchema($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}

$document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
$duree_conservation = isset($_POST['duree_conservation']) ? (int)$_POST['duree_conservation'] : 5;
$type_archivage = isset($_POST['type_archivage']) ? $_POST['type_archivage'] : 'DEFINITIF';
$categorie_archivage = isset($_POST['categorie_archivage']) ? $_POST['categorie_archivage'] : '';
$sous_categorie = isset($_POST['sous_categorie']) ? $_POST['sous_categorie'] : '';
$niveau_confidentialite = isset($_POST['niveau_confidentialite']) ? $_POST['niveau_confidentialite'] : 'INTERNE';
$motif_archivage = isset($_POST['motif_archivage']) ? $_POST['motif_archivage'] : '';

if ($document_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID document invalide']);
    exit;
}

// Get document details
$sql = "SELECT * FROM documents WHERE document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $document_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    echo json_encode(['status' => 'error', 'message' => 'Document non trouvé']);
    exit;
}

// File details
$filePath = __DIR__ . '/../../uploads/documents/' . $doc['chemin_stockage'];
if (!file_exists($filePath)) {
    echo json_encode(['status' => 'error', 'message' => 'Fichier physique non trouvé']);
    exit;
}

$fileSize = filesize($filePath);
$hashSha256 = hash_file('sha256', $filePath);
$extension = strtoupper($doc['extension_fichier']);

// Calculate dates
$dateArchivage = date('Y-m-d H:i:s');
$dateFinConservation = date('Y-m-d', strtotime("+$duree_conservation years"));

// UUID generation
$uuidArchive = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// Insert into archive_documents
$insertSql = "INSERT INTO archive_documents (
    uuid_archive, document_id, type_archivage, categorie_archivage, sous_categorie,
    date_archivage, date_fin_conservation, duree_conservation,
    emplacement_physique, format_fichier, hash_sha256, taille_fichier,
    titre, description, archive_par, motif_archivage, niveau_confidentialite
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $insertSql);
$titre = $doc['nom_fichier_original'];
$description = "Archivé depuis le module documents";
$userId = $_SESSION['user_id'];

mysqli_stmt_bind_param($stmt, "sisssssisssississ", 
    $uuidArchive, $document_id, $type_archivage, $categorie_archivage, $sous_categorie,
    $dateArchivage, $dateFinConservation, $duree_conservation,
    $filePath, $extension, $hashSha256, $fileSize,
    $titre, $description, $userId, $motif_archivage, $niveau_confidentialite
);

if (mysqli_stmt_execute($stmt)) {
    // Update document status
    $updateSql = "UPDATE documents SET statut = 'ARCHIVE', date_archivage = ? WHERE document_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, "si", $dateArchivage, $document_id);
    mysqli_stmt_execute($updateStmt);

    // Create notification
    $notifTitle = "Document Archivé";
    $notifMessage = "Le document '" . $doc['nom_fichier_original'] . "' a été archivé avec succès pour une durée de $duree_conservation ans.";
    $notifType = "success";
    
    $notifSql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $notifStmt = mysqli_prepare($conn, $notifSql);
    mysqli_stmt_bind_param($notifStmt, "isss", $userId, $notifTitle, $notifMessage, $notifType);
    mysqli_stmt_execute($notifStmt);

    echo json_encode(['status' => 'success', 'message' => 'Document archivé avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'archivage: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>