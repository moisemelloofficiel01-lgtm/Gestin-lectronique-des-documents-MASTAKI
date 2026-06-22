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

    $name = trim($data->name ?? '');
    $code = strtoupper(trim($data->code ?? ''));
    $icon = trim($data->icon ?? 'ti-file');
    $color = trim($data->color ?? 'primary');

    if (empty($name) || empty($code)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Nom et code requis."]);
        exit;
    }

    $check = $db->prepare("SELECT COUNT(*) FROM document_categories WHERE code = :code");
    $check->execute([':code' => $code]);
    if ($check->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Ce code existe deja."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO document_categories (code, name, icon, color) VALUES (:code, :name, :icon, :color)");
    $stmt->execute([
        ':code' => $code,
        ':name' => $name,
        ':icon' => $icon,
        ':color' => $color,
    ]);

    echo json_encode(["status" => "success", "message" => "Categorie creee avec succes.", "category_id" => (int)$db->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
