<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$password = isset($data->password) ? $data->password : '';

if (empty($password)) {
    echo json_encode(["status" => "error", "message" => "Mot de passe requis"]);
    exit;
}

$userId = $_SESSION['user_id'];
$query = "SELECT mot_de_passe FROM utilisateurs WHERE utilisateur_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['mot_de_passe'])) {
    echo json_encode(["status" => "success", "message" => "Mot de passe vérifié"]);
} else {
    echo json_encode(["status" => "error", "message" => "Mot de passe incorrect"]);
}

mysqli_close($conn);
?>
