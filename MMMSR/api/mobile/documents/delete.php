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

    $stmt = $db->prepare("SELECT chemin_stockage FROM documents WHERE document_id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Document non trouve."]);
        exit;
    }

    $deleteStmt = $db->prepare("DELETE FROM documents WHERE document_id = :id");
    $deleteStmt->execute([':id' => $id]);

    $filePath = __DIR__ . '/../../uploads/documents/' . $doc['chemin_stockage'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }

    echo json_encode(["status" => "success", "message" => "Document supprime avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
