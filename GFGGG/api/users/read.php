<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where = "WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR matricule LIKE ?";
    $s = "%$search%";
    $params = [$s, $s, $s, $s];
    $types = "ssss";
}

$countQuery = "SELECT COUNT(*) AS total FROM utilisateurs $where";
$countStmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$total = mysqli_fetch_assoc($countResult)['total'];

$query = "SELECT utilisateur_id, matricule, nom, prenom, email, photo, fonction, telephone, adresse, ville, roles, actif, date_creation FROM utilisateurs $where ORDER BY date_creation DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
$bindParams = array_merge($params, [$limit, $offset]);
$bindTypes = $types . "ii";
mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['roles'] = json_decode($row['roles'], true);
    $users[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $users,
    "total" => (int)$total,
    "page" => $page,
    "total_pages" => ceil($total / $limit)
]);
