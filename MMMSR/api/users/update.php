<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$roles = json_decode($_SESSION['roles'] ?? '[]', true);
if (!in_array('SUPERADMIN', $roles)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Accès réservé à l'administrateur"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID utilisateur requis"]);
    exit;
}

$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$email = trim($data['email'] ?? '');
$fonction = $data['fonction'] ?? '';
$telephone = $data['telephone'] ?? '';
$actif = isset($data['actif']) ? (int)$data['actif'] : 1;
$role = $data['role'] ?? null;

if (empty($nom) || empty($prenom) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Nom, prénom et email sont requis"]);
    exit;
}

$check = mysqli_query($conn, "SELECT email FROM utilisateurs WHERE email = '$email' AND utilisateur_id != $id");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
    exit;
}

if ($role) {
    $userRoles = json_encode([$role]);
    $sql = "UPDATE utilisateurs SET nom=?, prenom=?, email=?, fonction=?, telephone=?, actif=?, roles=? WHERE utilisateur_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssissi", $nom, $prenom, $email, $fonction, $telephone, $actif, $userRoles, $id);
} else {
    $sql = "UPDATE utilisateurs SET nom=?, prenom=?, email=?, fonction=?, telephone=?, actif=? WHERE utilisateur_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssiii", $nom, $prenom, $email, $fonction, $telephone, $actif, $id);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "message" => "Utilisateur mis à jour"]);
} else {
    echo json_encode(["status" => "error", "message" => "Erreur: " . mysqli_error($conn)]);
}
