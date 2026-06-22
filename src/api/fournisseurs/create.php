<?php
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Define custom logger
function log_debug($msg) {
    file_put_contents(__DIR__ . '/debug_create.log', date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
}

log_debug("Starting request...");

try {
    require_once __DIR__ . '/../../config/db.php';
    log_debug("DB Config included.");
    require_once __DIR__ . '/schema_helper.php';

    // Ensure table schema is correct
    ensureFournisseursTableSchema($conn);

    if (!function_exists('handleFileUpload')) {
        function handleFileUpload($file) {
            $targetDir = __DIR__ . "/../../uploads/fournisseurs/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array(strtolower($fileType), $allowTypes)) {
                if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                    return $fileName;
                }
            }
            return null;
        }
    }

    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo = handleFileUpload($_FILES['logo']);
        if (!$logo) {
            throw new Exception("Erreur lors du téléchargement de l'image (format non supporté ou erreur serveur)");
        }
    }

    $nom_fournisseur = $_POST['nom_fournisseur'] ?? 'Nouveau Fournisseur';
    $adresse = $_POST['adresse'] ?? '';
    $complement_adresse = $_POST['complement_adresse'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $pays = $_POST['pays'] ?? 'RDC';
    $contacts = $_POST['contacts'] ?? '[]';
    $telephone_principal = $_POST['telephone_principal'] ?? '';
    $email_general = $_POST['email_general'] ?? '';
    $categorie_fournisseur = $_POST['categorie_fournisseur'] ?? '';
    $secteur_activite = $_POST['secteur_activite'] ?? '';
    $commentaires_evaluation = $_POST['commentaires_evaluation'] ?? '';

    // Use backticks for column names to avoid reserved word conflicts
    $sql = "INSERT INTO fournisseurs (`nom_fournisseur`, `logo`, `adresse`, `complement_adresse`, `code_postal`, `ville`, `pays`, `contacts`, `telephone_principal`, `email_general`, `categorie_fournisseur`, `secteur_activite`, `commentaires_evaluation`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        throw new Exception("Erreur de préparation de la requête : " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "sssssssssssss", $nom_fournisseur, $logo, $adresse, $complement_adresse, $code_postal, $ville, $pays, $contacts, $telephone_principal, $email_general, $categorie_fournisseur, $secteur_activite, $commentaires_evaluation);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Fournisseur ajouté avec succès'
        ]);
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>