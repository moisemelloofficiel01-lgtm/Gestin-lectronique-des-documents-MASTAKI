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

    if (!$data || !isset($data->user_id) || !isset($data->prenom) || !isset($data->nom) || !isset($data->email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Donnees incompletes."]);
        exit;
    }

    $userId = (int)$data->user_id;
    $prenom = trim($data->prenom);
    $nom = trim($data->nom);
    $email = trim($data->email);

    $check = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = :email AND utilisateur_id != :id");
    $check->execute([':email' => $email, ':id' => $userId]);
    if ($check->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Cet email est deja utilise."]);
        exit;
    }

    $stmt = $db->prepare("UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email WHERE utilisateur_id = :id");
    $stmt->execute([
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $email,
        ':id' => $userId,
    ]);

    echo json_encode(["status" => "success", "message" => "Profil mis a jour avec succes."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
