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

$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? 'Admin123!';
$fonction = $data['fonction'] ?? '';
$telephone = $data['telephone'] ?? '';
$role = $data['role'] ?? 'USER';

if (empty($nom) || empty($prenom) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Nom, prénom et email sont requis"]);
    exit;
}

$check = mysqli_query($conn, "SELECT email FROM utilisateurs WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
    exit;
}

$matricule = 'USR-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$userRoles = json_encode([$role]);
$createdBy = $_SESSION['user_id'];

$sql = "INSERT INTO utilisateurs (matricule, nom, prenom, email, mot_de_passe, fonction, telephone, roles, cree_par) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssssssi", $matricule, $nom, $prenom, $email, $hashedPassword, $fonction, $telephone, $userRoles, $createdBy);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "message" => "Utilisateur créé avec succès", "id" => mysqli_insert_id($conn)]);
} else {
    echo json_encode(["status" => "error", "message" => "Erreur: " . mysqli_error($conn)]);
}
