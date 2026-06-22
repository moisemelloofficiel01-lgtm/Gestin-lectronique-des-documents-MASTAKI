<?php
ini_set('display_errors', 0);
header("Content-Type: application/json");

try {
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../documents/archive_schema.php';

    // Ensure table exists
    ensureArchiveTableSchema($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Method Not Allowed");
    }

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // If JSON decode failed, check if $_POST has data (fallback for FormData)
    if (json_last_error() !== JSON_ERROR_NONE && !empty($_POST)) {
        $data = $_POST;
    }

    if (empty($data)) {
        throw new Exception("No data provided");
    }

    // Required fields
    $required = ['document_id', 'type_archivage', 'duree_conservation', 'format_fichier', 'hash_sha256', 'taille_fichier'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }

    // Generate UUID if not provided
    $uuid_archive = isset($data['uuid_archive']) ? $data['uuid_archive'] : sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    // Prepare fields
    $fields = [
        'uuid_archive', 'document_id', 'type_archivage', 'categorie_archivage', 'sous_categorie',
        'date_fin_vie_utile', 'date_fin_conservation', 'duree_conservation', 'emplacement_physique',
        'bucket_s3', 'chemin_objet', 'format_fichier', 'version_format', 'hash_sha256', 'hash_sha512',
        'taille_fichier', 'signature_electronique', 'certificat_signature', 'horodatage_certifie',
        'titre', 'description', 'sujet', 'auteur', 'createur', 'editeur', 'contributeurs',
        'date_creation', 'date_publication', 'type_mime', 'format_mime', 'identifiants', 'langue',
        'relation', 'couverture', 'droits', 'niveau_confidentialite', 'politiques_acces',
        'date_declassification', 'statut_archivage', 'motif_archivage', 'archive_par', 'autorise_par',
        'date_autorisation'
    ];

    $columns = [];
    $placeholders = [];
    $types = "";
    $values = [];

    // Force uuid
    $data['uuid_archive'] = $uuid_archive;

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $columns[] = $field;
            $placeholders[] = "?";
            $val = $data[$field];
            
            // Handle JSON fields
            if (in_array($field, ['contributeurs', 'identifiants', 'politiques_acces']) && is_array($val)) {
                $val = json_encode($val);
            }
            
            $values[] = $val;
            
            // Determine type
            if (is_int($val)) {
                $types .= "i";
            } elseif (is_float($val)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
        }
    }

    $sql = "INSERT INTO archive_documents (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Archive created successfully', 'id' => mysqli_insert_id($conn), 'uuid' => $uuid_archive]);
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unexpected error: ' . $e->getMessage()]);
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>