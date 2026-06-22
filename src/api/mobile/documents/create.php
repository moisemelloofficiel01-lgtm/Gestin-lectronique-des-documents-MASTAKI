<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Methode non autorisee.");
    }

    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Aucun fichier telecharge ou erreur (code: " . ($_FILES['document']['error'] ?? 'unknown') . ").");
    }

    $file = $_FILES['document'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

    $newFileName = $uuid . '.' . $fileExt;
    $uploadDir = __DIR__ . '/../../uploads/documents/';
    if (!file_exists($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $uploadPath = $uploadDir . $newFileName;

    $checksum = hash_file('sha256', $fileTmpName);

    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
        throw new Exception("Echec du déplacement du fichier.");
    }

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
    $fournisseur_id = !empty($_POST['fournisseur_id']) ? (int)$_POST['fournisseur_id'] : null;
    $service_demandeur = !empty($_POST['service_demandeur']) ? $_POST['service_demandeur'] : null;
    $centre_cout = !empty($_POST['centre_cout']) ? $_POST['centre_cout'] : null;
    $duree_conservation = !empty($_POST['duree_conservation']) ? (int)$_POST['duree_conservation'] : null;
    $statut = $_POST['statut'] ?? 'NOUVEAU';

    $sql = "INSERT INTO documents (
        uuid_document, type_document, sous_type, nom_fichier_original, extension_fichier,
        chemin_stockage, taille_fichier, checksum, numero_facture, numero_commande,
        numero_bon_livraison, date_facture, date_echeance, montant_ht, montant_tva,
        montant_ttc, devise, fournisseur_id, service_demandeur, centre_cout, duree_conservation, statut
    ) VALUES (
        :uuid, :type_document, :sous_type, :nom_fichier, :extension,
        :chemin, :taille, :checksum, :num_facture, :num_commande,
        :num_bl, :date_facture, :date_echeance, :montant_ht, :montant_tva,
        :montant_ttc, :devise, :fournisseur_id, :service_demandeur, :centre_cout, :duree, :statut
    )";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':uuid' => $uuid,
        ':type_document' => $type_document,
        ':sous_type' => $sous_type,
        ':nom_fichier' => $fileName,
        ':extension' => $fileExt,
        ':chemin' => $newFileName,
        ':taille' => $fileSize,
        ':checksum' => $checksum,
        ':num_facture' => $numero_facture,
        ':num_commande' => $numero_commande,
        ':num_bl' => $numero_bon_livraison,
        ':date_facture' => $date_facture,
        ':date_echeance' => $date_echeance,
        ':montant_ht' => $montant_ht,
        ':montant_tva' => $montant_tva,
        ':montant_ttc' => $montant_ttc,
        ':devise' => $devise,
        ':fournisseur_id' => $fournisseur_id,
        ':service_demandeur' => $service_demandeur,
        ':centre_cout' => $centre_cout,
        ':duree' => $duree_conservation,
        ':statut' => $statut,
    ]);

    echo json_encode(["status" => "success", "message" => "Document ajoute avec succes.", "document_id" => (int)$db->lastInsertId()]);

} catch (Exception $e) {
    if (isset($uploadPath) && file_exists($uploadPath)) { @unlink($uploadPath); }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
