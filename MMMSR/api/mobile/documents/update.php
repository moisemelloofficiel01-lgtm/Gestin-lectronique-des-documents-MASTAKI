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

    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput);
    if (!$data || !isset($data->document_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID du document manquant."]);
        exit;
    }

    $id = (int)$data->document_id;

    $sql = "UPDATE documents SET
        type_document = :type_document,
        sous_type = :sous_type,
        numero_facture = :numero_facture,
        numero_commande = :numero_commande,
        numero_bon_livraison = :numero_bon_livraison,
        date_facture = :date_facture,
        date_echeance = :date_echeance,
        montant_ht = :montant_ht,
        montant_tva = :montant_tva,
        montant_ttc = :montant_ttc,
        devise = :devise,
        fournisseur_id = :fournisseur_id,
        service_demandeur = :service_demandeur,
        centre_cout = :centre_cout,
        duree_conservation = :duree_conservation,
        statut = :statut
        WHERE document_id = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':type_document' => $data->type_document ?? 'AUTRE',
        ':sous_type' => $data->sous_type ?? null,
        ':numero_facture' => $data->numero_facture ?? null,
        ':numero_commande' => $data->numero_commande ?? null,
        ':numero_bon_livraison' => $data->numero_bon_livraison ?? null,
        ':date_facture' => $data->date_facture ?? null,
        ':date_echeance' => $data->date_echeance ?? null,
        ':montant_ht' => $data->montant_ht ?? null,
        ':montant_tva' => $data->montant_tva ?? null,
        ':montant_ttc' => $data->montant_ttc ?? null,
        ':devise' => $data->devise ?? 'USD',
        ':fournisseur_id' => !empty($data->fournisseur_id) ? (int)$data->fournisseur_id : null,
        ':service_demandeur' => $data->service_demandeur ?? null,
        ':centre_cout' => $data->centre_cout ?? null,
        ':duree_conservation' => $data->duree_conservation ?? null,
        ':statut' => $data->statut ?? 'NOUVEAU',
        ':id' => $id,
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Document non trouve ou aucune modification."]);
        exit;
    }

    echo json_encode(["status" => "success", "message" => "Document mis a jour avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
