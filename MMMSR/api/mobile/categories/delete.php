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
    if (!$data || !isset($data->category_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID de la categorie manquant."]);
        exit;
    }

    $id = (int)$data->category_id;

    $stmt = $db->prepare("DELETE FROM document_categories WHERE category_id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Categorie non trouvee."]);
        exit;
    }

    echo json_encode(["status" => "success", "message" => "Categorie supprimee avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
