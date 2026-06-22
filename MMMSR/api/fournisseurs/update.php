<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schema_helper.php';

// Ensure table schema is correct
ensureFournisseursTableSchema($conn);

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

try {
    $id = $_POST['fournisseur_id'];
    if (!$id) {
        throw new Exception("ID manquant");
    }

    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo = handleFileUpload($_FILES['logo']);
         if (!$logo) {
            throw new Exception("Erreur lors du téléchargement de l'image");
        }
    } else {
        $stmt = mysqli_prepare($conn, "SELECT logo FROM fournisseurs WHERE fournisseur_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $logo = $row['logo'];
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
    $statut = $_POST['statut'] ?? 'ACTIF';

    $sql = "UPDATE fournisseurs SET 
            nom_fournisseur = ?,
            logo = ?, 
            adresse = ?, 
            complement_adresse = ?, 
            code_postal = ?, 
            ville = ?, 
            pays = ?, 
            contacts = ?, 
            telephone_principal = ?, 
            email_general = ?, 
            categorie_fournisseur = ?, 
            secteur_activite = ?, 
            commentaires_evaluation = ?,
            statut = ?,
            date_modification = CURRENT_TIMESTAMP
            WHERE fournisseur_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssssssssssi", $nom_fournisseur, $logo, $adresse, $complement_adresse, $code_postal, $ville, $pays, $contacts, $telephone_principal, $email_general, $categorie_fournisseur, $secteur_activite, $commentaires_evaluation, $statut, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Fournisseur mis à jour avec succès']);
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
