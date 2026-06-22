<?php
// Disable error display to avoid polluting JSON output
ini_set('display_errors', 0);
header("Content-Type: application/json");

try {
    require_once __DIR__ . '/../../config/db.php';
    
    // Check connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    require_once __DIR__ . '/schema_helper.php';

    // Ensure table exists
    if (!ensureDocumentsTableSchema($conn)) {
        throw new Exception("Failed to ensure table schema");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Check for file upload
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Aucun fichier téléchargé ou erreur de téléchargement (Code: " . ($_FILES['document']['error'] ?? 'unknown') . ")");
    }

    $file = $_FILES['document'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Generate UUID
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $newFileName = $uuid . '.' . $fileExt;
    $uploadDir = __DIR__ . '/../../uploads/documents/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    $uploadPath = $uploadDir . $newFileName;

    // Calculate checksum
    $checksum = hash_file('sha256', $fileTmpName);

    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Extract other fields
        $type_document = $_POST['type_document'] ?? 'AUTRE';
        $sous_type = $_POST['sous_type'] ?? '';
        $numero_facture = !empty($_POST['numero_facture']) ? $_POST['numero_facture'] : null;
        $numero_commande = !empty($_POST['numero_commande']) ? $_POST['numero_commande'] : null;
        $numero_bon_livraison = !empty($_POST['numero_bon_livraison']) ? $_POST['numero_bon_livraison'] : null;
        $date_facture = !empty($_POST['date_facture']) ? $_POST['date_facture'] : null;
        $date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
        $montant_ht = !empty($_POST['montant_ht']) ? $_POST['montant_ht'] : null;
        $montant_tva = !empty($_POST['montant_tva']) ? $_POST['montant_tva'] : null;
        $montant_ttc = !empty($_POST['montant_ttc']) ? $_POST['montant_ttc'] : null;
        $devise = $_POST['devise'] ?? 'USD';
        $fournisseur_id = !empty($_POST['fournisseur_id']) && $_POST['fournisseur_id'] !== 'undefined' ? $_POST['fournisseur_id'] : null;
        $service_demandeur = !empty($_POST['service_demandeur']) ? $_POST['service_demandeur'] : null;
        $centre_cout = !empty($_POST['centre_cout']) ? $_POST['centre_cout'] : null;
        $duree_conservation = !empty($_POST['duree_conservation']) ? $_POST['duree_conservation'] : null;
        $created_by = $_SESSION['user_id'] ?? 1; // Default to 1 if not set

        $sql = "INSERT INTO documents (
            uuid_document, type_document, sous_type, nom_fichier_original, extension_fichier, 
            chemin_stockage, taille_fichier, checksum, numero_facture, numero_commande, 
            numero_bon_livraison, date_facture, date_echeance, montant_ht, montant_tva, 
            montant_ttc, devise, fournisseur_id, service_demandeur, centre_cout, duree_conservation, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssssssissssssdddssssii", 
            $uuid, $type_document, $sous_type, $fileName, $fileExt, 
            $newFileName, $fileSize, $checksum, $numero_facture, $numero_commande, 
            $numero_bon_livraison, $date_facture, $date_echeance, $montant_ht, $montant_tva, 
            $montant_ttc, $devise, $fournisseur_id, $service_demandeur, $centre_cout, $duree_conservation, $created_by
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Document ajouté avec succès']);
        } else {
            // Remove file if DB insert fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            throw new Exception("Database insert failed: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Failed to move uploaded file");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unexpected error: ' . $e->getMessage()]);
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>