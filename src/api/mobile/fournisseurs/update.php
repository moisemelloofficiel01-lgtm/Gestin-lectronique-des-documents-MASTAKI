<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $fournisseur_id = (int)($_POST['fournisseur_id'] ?? 0);
    if (!$fournisseur_id) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID du fournisseur manquant."]);
        exit;
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

    $stmt = $db->prepare("SELECT logo FROM fournisseurs WHERE fournisseur_id = :id LIMIT 1");
    $stmt->execute([':id' => $fournisseur_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    $logoPath = $existing ? $existing['logo'] : null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoDir = __DIR__ . '/../../uploads/fournisseurs/';
        if (!file_exists($logoDir)) { mkdir($logoDir, 0777, true); }
        $logoName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']);
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoDir . $logoName)) {
            if ($logoPath && file_exists($logoDir . $logoPath)) { @unlink($logoDir . $logoPath); }
            $logoPath = $logoName;
        }
    }

    $sql = "UPDATE fournisseurs SET
        nom_fournisseur = :nom, logo = :logo, adresse = :adresse,
        complement_adresse = :complement, code_postal = :cp, ville = :ville,
        pays = :pays, contacts = :contacts, telephone_principal = :tel,
        email_general = :email, categorie_fournisseur = :categorie,
        secteur_activite = :secteur, commentaires_evaluation = :commentaires,
        statut = :statut, date_modification = CURRENT_TIMESTAMP
        WHERE fournisseur_id = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':nom' => $nom_fournisseur,
        ':logo' => $logoPath,
        ':adresse' => $adresse,
        ':complement' => $complement_adresse,
        ':cp' => $code_postal,
        ':ville' => $ville,
        ':pays' => $pays,
        ':contacts' => $contacts,
        ':tel' => $telephone_principal,
        ':email' => $email_general,
        ':categorie' => $categorie_fournisseur,
        ':secteur' => $secteur_activite,
        ':commentaires' => $commentaires_evaluation,
        ':statut' => $statut,
        ':id' => $fournisseur_id,
    ]);

    echo json_encode(["status" => "success", "message" => "Fournisseur mis a jour avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
