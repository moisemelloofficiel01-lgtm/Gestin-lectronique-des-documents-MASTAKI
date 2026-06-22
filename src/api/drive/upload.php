<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$userId = $_SESSION['user_id'];
$dossierId = isset($_POST['dossier_id']) && $_POST['dossier_id'] !== '' ? (int)$_POST['dossier_id'] : null;

if (!isset($_FILES['file'])) {
    echo json_encode(["status" => "error", "message" => "Aucun fichier reçu"]);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$uuid = uniqid('', true) . '-' . bin2hex(random_bytes(8));
$originalName = $file['name'];
$storagePath = 'uploads/drive/' . $uuid . '.' . $ext;
$fullPath = __DIR__ . '/../../' . $storagePath;

if (move_uploaded_file($file['tmp_name'], $fullPath)) {
    $typeDoc = 'AUTRE';
    if (in_array($ext, ['pdf'])) $typeDoc = 'PDF';
    elseif (in_array($ext, ['jpg','jpeg','png','gif','webp'])) $typeDoc = 'IMAGE';
    elseif (in_array($ext, ['doc','docx'])) $typeDoc = 'DOC';
    elseif (in_array($ext, ['xls','xlsx'])) $typeDoc = 'XLS';
    elseif (in_array($ext, ['txt'])) $typeDoc = 'TXT';

    if ($dossierId) {
        $sql = "INSERT INTO documents_personnels (uuid, dossier_id, nom_fichier, fichier_original, extension, taille, chemin_stockage, type_document, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssisisi", $uuid, $dossierId, $originalName, $originalName, $ext, $file['size'], $storagePath, $typeDoc, $userId);
    } else {
        $sql = "INSERT INTO documents_personnels (uuid, dossier_id, nom_fichier, fichier_original, extension, taille, chemin_stockage, type_document, created_by) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssisi", $uuid, $originalName, $originalName, $ext, $file['size'], $storagePath, $typeDoc, $userId);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Fichier uploadé", "id" => mysqli_insert_id($conn)]);
    } else {
        @unlink($fullPath);
        echo json_encode(["status" => "error", "message" => "Erreur BD: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Échec de l'upload"]);
}
