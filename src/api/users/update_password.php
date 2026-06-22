<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

$id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Tous les champs sont obligatoires']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Les nouveaux mots de passe ne correspondent pas']);
    exit;
}

// Verify old password
$sql = "SELECT mot_de_passe FROM utilisateurs WHERE utilisateur_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($current_password, $user['mot_de_passe'])) {
    echo json_encode(['status' => 'error', 'message' => 'Mot de passe actuel incorrect']);
    exit;
}

// Update password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
$updateSql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE utilisateur_id = ?";
$updateStmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($updateStmt, "si", $new_hash, $id);

if (mysqli_stmt_execute($updateStmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Mot de passe modifié avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la modification: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
