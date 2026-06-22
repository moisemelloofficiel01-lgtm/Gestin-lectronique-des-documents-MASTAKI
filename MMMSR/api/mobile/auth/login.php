<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$rawInput = file_get_contents("php://input");
$data = null;

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput);
    if (json_last_error() === JSON_ERROR_NONE) {
        $data = $decoded;
    }
}

if (!$data && !empty($_POST)) {
    $data = (object) $_POST;
}

if (!$data && !empty($rawInput)) {
    parse_str($rawInput, $parsedBody);
    if (!empty($parsedBody)) {
        $data = (object) $parsedBody;
    }
}

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Email ou mot de passe manquant.",
    ]);
    exit;
}

$email = trim((string) $data->email);
$password = (string) $data->password;

$query = "SELECT utilisateur_id, matricule, nom, prenom, email, fonction, photo, roles, actif, mot_de_passe
          FROM utilisateurs
          WHERE email = :email
          LIMIT 1";

$stmt = $db->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Utilisateur introuvable.",
    ]);
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!(bool) $user['actif']) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "Compte inactif.",
    ]);
    exit;
}

if (!password_verify($password, $user['mot_de_passe'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Mot de passe invalide.",
    ]);
    exit;
}

$roles = json_decode($user['roles'], true);
if (!is_array($roles)) {
    $roles = [];
}

unset($user['mot_de_passe']);
$user['roles'] = $roles;

echo json_encode([
    "status" => "success",
    "message" => "Connexion mobile reussie.",
    "user" => [
        "id" => (int) $user['utilisateur_id'],
        "matricule" => $user['matricule'],
        "nom" => $user['nom'],
        "prenom" => $user['prenom'],
        "username" => trim($user['prenom'] . ' ' . $user['nom']),
        "email" => $user['email'],
        "fonction" => $user['fonction'],
        "photo" => $user['photo'],
        "roles" => $user['roles'],
    ],
]);
?>
