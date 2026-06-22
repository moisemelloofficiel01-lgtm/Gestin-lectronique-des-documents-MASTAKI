<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['document_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit;
}

$id = (int)$_POST['document_id'];
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
$fournisseur_id = !empty($_POST['fournisseur_id']) ? $_POST['fournisseur_id'] : null;
$service_demandeur = !empty($_POST['service_demandeur']) ? $_POST['service_demandeur'] : null;
$centre_cout = !empty($_POST['centre_cout']) ? $_POST['centre_cout'] : null;
$duree_conservation = !empty($_POST['duree_conservation']) ? $_POST['duree_conservation'] : null;
$statut = $_POST['statut'] ?? 'NOUVEAU';

$sql = "UPDATE documents SET 
    type_document = ?, sous_type = ?, numero_facture = ?, numero_commande = ?, 
    numero_bon_livraison = ?, date_facture = ?, date_echeance = ?, montant_ht = ?, 
    montant_tva = ?, montant_ttc = ?, devise = ?, fournisseur_id = ?, 
    service_demandeur = ?, centre_cout = ?, duree_conservation = ?, statut = ?
    WHERE document_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssdddssssisi", 
    $type_document, $sous_type, $numero_facture, $numero_commande, 
    $numero_bon_livraison, $date_facture, $date_echeance, $montant_ht, 
    $montant_tva, $montant_ttc, $devise, $fournisseur_id, 
    $service_demandeur, $centre_cout, $duree_conservation, $statut, $id
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Document mis à jour avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>