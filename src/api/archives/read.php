<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../documents/archive_schema.php';

ensureArchiveTableSchema($conn);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;

$offset = ($page - 1) * $limit;

$whereClauses = ["1=1"];
$params = [];
$types = "";

if ($document_id) {
    $whereClauses[] = "document_id = ?";
    $params[] = $document_id;
    $types .= "i";
}

if (!empty($search)) {
    $whereClauses[] = "(titre LIKE ? OR description LIKE ? OR uuid_archive LIKE ? OR nom_fichier_original LIKE ?)"; // Assuming we might want to join documents to search by filename too
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    // For now, let's keep it simple on the archives table, but maybe join documents
    // Actually, let's join documents to get the filename if needed
}

// Build query
$sql = "SELECT a.*, d.nom_fichier_original, d.type_document as doc_type_document 
        FROM archive_documents a
        LEFT JOIN documents d ON a.document_id = d.document_id";

if (!empty($search)) {
    $whereClauses[] = "(a.titre LIKE ? OR a.description LIKE ? OR a.uuid_archive LIKE ? OR d.nom_fichier_original LIKE ?)";
    // Fix params for search
    // We already added params above, but let's reset to be clean
    $params = [];
    $types = "";
    
    if ($document_id) {
        $params[] = $document_id;
        $types .= "i";
    }
    
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

$whereSQL = " WHERE " . implode(" AND ", $whereClauses);

// Count total
$countSql = "SELECT COUNT(*) FROM archive_documents a LEFT JOIN documents d ON a.document_id = d.document_id" . $whereSQL;

try {
    $stmt = mysqli_prepare($conn, $countSql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totalRecords = mysqli_fetch_array($result)[0];
    $totalPages = ceil($totalRecords / $limit);

    // Fetch records
    $sql .= $whereSQL . " ORDER BY a.date_archivage DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $archives = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Decode JSON fields
        foreach (['contributeurs', 'identifiants', 'politiques_acces'] as $jsonField) {
            if (isset($row[$jsonField]) && $row[$jsonField]) {
                $row[$jsonField] = json_decode($row[$jsonField], true);
            }
        }
        $archives[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $archives,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>
