<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../documents/archive_schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['archive_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'archive_id is required']);
    exit;
}

$archive_id = (int)$data['archive_id'];
unset($data['archive_id']); // Remove ID from update data

// Allowed fields to update
$allowedFields = [
    'type_archivage', 'categorie_archivage', 'sous_categorie',
    'date_fin_vie_utile', 'date_fin_conservation', 'duree_conservation', 'emplacement_physique',
    'bucket_s3', 'chemin_objet', 'titre', 'description', 'sujet', 'contributeurs',
    'identifiants', 'langue', 'relation', 'couverture', 'droits', 'niveau_confidentialite',
    'politiques_acces', 'date_declassification', 'statut_archivage', 'motif_archivage',
    'date_dernier_acces', 'compteur_acces', 'dernier_acces_par'
];

$updates = [];
$params = [];
$types = "";

foreach ($allowedFields as $field) {
    if (isset($data[$field])) {
        $updates[] = "$field = ?";
        $val = $data[$field];
        
        // Handle JSON fields
        if (in_array($field, ['contributeurs', 'identifiants', 'politiques_acces']) && is_array($val)) {
            $val = json_encode($val);
        }
        
        $params[] = $val;
        
        if (is_int($val)) {
            $types .= "i";
        } elseif (is_float($val)) {
            $types .= "d";
        } else {
            $types .= "s";
        }
    }
}

if (empty($updates)) {
    echo json_encode(['status' => 'success', 'message' => 'No changes to update']);
    exit;
}

$sql = "UPDATE archive_documents SET " . implode(", ", $updates) . " WHERE archive_id = ?";
$params[] = $archive_id;
$types .= "i";

try {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Archive updated successfully']);
    } else {
        throw new Exception(mysqli_error($conn));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
