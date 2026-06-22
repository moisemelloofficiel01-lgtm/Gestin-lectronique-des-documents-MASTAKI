<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schema_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

ensureNotificationsTableSchema($conn);

$userId = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Fetch unread notifications first, then read ones
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $userId, $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
$unreadCount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
    if ($row['is_read'] == 0) {
        $unreadCount++;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $notifications,
    'unread_count' => $unreadCount
]);

mysqli_close($conn);
?>