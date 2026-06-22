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
        echo json_encode(["status" => "error", "message" => "ID du document manquant."]);
        exit;
    }

    $id = (int)$_GET['id'];
    $stmt = $db->prepare("
        SELECT d.*, f.nom_fournisseur, f.ville as fournisseur_ville, f.pays as fournisseur_pays,
               f.telephone_principal as fournisseur_tel, f.email_general as fournisseur_email
        FROM documents d
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id
        WHERE d.document_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Document non trouve."]);
        exit;
    }

    echo json_encode(["status" => "success", "data" => $doc]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
