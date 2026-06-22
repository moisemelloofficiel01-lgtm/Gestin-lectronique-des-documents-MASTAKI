<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
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

if ($id == $_SESSION['user_id']) {
    echo json_encode(["status" => "error", "message" => "Vous ne pouvez pas supprimer votre propre compte"]);
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM utilisateurs WHERE utilisateur_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "message" => "Utilisateur supprimé"]);
} else {
    echo json_encode(["status" => "error", "message" => "Erreur: " . mysqli_error($conn)]);
}
