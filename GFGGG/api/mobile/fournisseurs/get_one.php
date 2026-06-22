<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID du fournisseur manquant."]);
        exit;
    }

    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM fournisseurs WHERE fournisseur_id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fournisseur) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Fournisseur non trouve."]);
        exit;
    }

    $docStmt = $db->prepare("SELECT document_id, type_document, nom_fichier_original, montant_ttc, devise, statut, created_at FROM documents WHERE fournisseur_id = :id ORDER BY created_at DESC LIMIT 10");
    $docStmt->execute([':id' => $id]);
    $fournisseur['documents'] = $docStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $fournisseur]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
