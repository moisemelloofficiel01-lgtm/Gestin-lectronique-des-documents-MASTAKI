<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    die("ID du document manquant.");
}

$id = $_GET['id'];
$mode = isset($_GET['mode']) && $_GET['mode'] === 'view' ? 'inline' : 'attachment';

$sql = "SELECT nom_fichier_original, chemin_stockage, extension_fichier, type_document FROM documents WHERE document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $filePath = __DIR__ . '/../../uploads/documents/' . $row['chemin_stockage'];
    $originalName = $row['nom_fichier_original'];
    $extension = strtolower($row['extension_fichier']);

    if (file_exists($filePath)) {
        // Determine MIME type
        $mimeType = 'application/octet-stream';
        $knownMimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (array_key_exists($extension, $knownMimes)) {
            $mimeType = $knownMimes[$extension];
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $mode . '; filename="' . basename($originalName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Clear output buffer to avoid corrupting file
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo "Le fichier physique est introuvable sur le serveur.";
    }
} else {
    http_response_code(404);
    echo "Document non trouvé dans la base de données.";
}
mysqli_close($conn);
?>