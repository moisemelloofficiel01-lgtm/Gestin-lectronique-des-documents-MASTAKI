<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
        throw new Exception("Methode non autorisee.");
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

    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoDir = __DIR__ . '/../../uploads/fournisseurs/';
        if (!file_exists($logoDir)) { mkdir($logoDir, 0777, true); }
        $logoExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $logoName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']);
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoDir . $logoName)) {
            $logoPath = $logoName;
        }
    }

    $sql = "INSERT INTO fournisseurs (nom_fournisseur, logo, adresse, complement_adresse, code_postal,
            ville, pays, contacts, telephone_principal, email_general,
            categorie_fournisseur, secteur_activite, commentaires_evaluation)
            VALUES (:nom, :logo, :adresse, :complement, :cp, :ville, :pays, :contacts,
            :tel, :email, :categorie, :secteur, :commentaires)";

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
    ]);

    echo json_encode(["status" => "success", "message" => "Fournisseur ajoute avec succes.", "fournisseur_id" => (int)$db->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
