<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$notifId = isset($data['id']) ? (int)$data['id'] : 0;

if ($notifId > 0) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    $userId = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, "ii", $notifId, $userId);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    // Mark all as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    $userId = $_SESSION['user_id'];
    mysqli_stmt_execute($stmt);
    echo json_encode(['status' => 'success']);
}

mysqli_close($conn);
?>