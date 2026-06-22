<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

$id = $_SESSION['user_id'];
$prenom = $_POST['prenom'] ?? '';
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($prenom) || empty($nom) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Tous les champs sont obligatoires']);
    exit;
}

// Check email uniqueness
$checkSql = "SELECT utilisateur_id FROM utilisateurs WHERE email = ? AND utilisateur_id != ?";
$stmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmt, "si", $email, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cet email est déjà utilisé']);
    exit;
}

$updateSql = "UPDATE utilisateurs SET prenom = ?, nom = ?, email = ? WHERE utilisateur_id = ?";
$updateStmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($updateStmt, "sssi", $prenom, $nom, $email, $id);

if (mysqli_stmt_execute($updateStmt)) {
    // Update session
    $_SESSION['username'] = $prenom . ' ' . $nom;
    echo json_encode(['status' => 'success', 'message' => 'Profil mis à jour avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
