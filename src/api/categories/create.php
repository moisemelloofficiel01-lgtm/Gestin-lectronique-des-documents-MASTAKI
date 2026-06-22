<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

// Define custom logger for debugging if needed (similar to suppliers)
function log_debug($msg) {
    file_put_contents(__DIR__ . '/debug_create.log', date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
}

// Log raw input to debug file
$raw_input = file_get_contents("php://input");
log_debug("Raw Input: " . $raw_input);

require_once __DIR__ . '/../../config/db.php';

$data = json_decode($raw_input, true);

// Fallback to standard POST if JSON is empty (for form submissions without JSON stringify)
if (empty($data) && !empty($_POST)) {
    $data = $_POST;
    log_debug("Using POST data: " . json_encode($_POST));
}

if (!$data || !isset($data['code']) || !isset($data['name']) || empty($data['code']) || empty($data['name'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'error', 
        'message' => 'Données manquantes : Veuillez remplir tous les champs (Nom et Code requis).',
        'debug' => [
            'json_data' => $data,
            'post_data' => $_POST,
            'raw_input_start' => substr($raw_input, 0, 100)
        ]
    ]);
    exit;
}

try {
    $code = strtoupper(trim($data['code']));
    $name = trim($data['name']);
    $icon = isset($data['icon']) && !empty($data['icon']) ? $data['icon'] : 'ti-file';
    $color = isset($data['color']) && !empty($data['color']) ? $data['color'] : 'primary';

    // Check if category code already exists
    $checkStmt = mysqli_prepare($conn, "SELECT category_id FROM document_categories WHERE code = ?");
    mysqli_stmt_bind_param($checkStmt, "s", $code);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        throw new Exception("Une catégorie avec ce code existe déjà.");
    }
    mysqli_stmt_close($checkStmt);

    $stmt = mysqli_prepare($conn, "INSERT INTO document_categories (code, name, icon, color) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Erreur de préparation SQL: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ssss", $code, $name, $icon, $color);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Catégorie créée avec succès.']);
    } else {
        throw new Exception("Erreur lors de l'insertion: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    log_debug("Error: " . $e->getMessage());
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
