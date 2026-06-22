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

    if (!$data || !isset($data->user_id) || !isset($data->current_password) || !isset($data->new_password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Donnees incompletes."]);
        exit;
    }

    $userId = (int)$data->user_id;
    $currentPassword = $data->current_password;
    $newPassword = $data->new_password;

    $stmt = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE utilisateur_id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Utilisateur non trouve."]);
        exit;
    }

    if (!password_verify($currentPassword, $user['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Mot de passe actuel incorrect."]);
        exit;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = :password WHERE utilisateur_id = :id");
    $updateStmt->execute([
        ':password' => $newHash,
        ':id' => $userId,
    ]);

    echo json_encode(["status" => "success", "message" => "Mot de passe modifie avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
