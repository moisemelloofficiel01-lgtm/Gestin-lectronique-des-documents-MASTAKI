<?php
// Disable error display to avoid polluting JSON output
ini_set('display_errors', 0);
header("Content-Type: application/json");

try {
    require_once __DIR__ . '/../../config/db.php';

    // Include schema helper to ensure database structure is correct
    // We check for suppliers schema because we join with it
    $suppliersSchema = __DIR__ . '/../fournisseurs/schema_helper.php';
    if (file_exists($suppliersSchema)) {
        require_once $suppliersSchema;
        // Ensure the table and columns exist before querying
        // This should add 'nom_fournisseur' if missing
        ensureFournisseursTableSchema($conn);
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $view_mode = isset($_GET['view_mode']) ? $_GET['view_mode'] : 'active'; // active, archive, all
    $type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
    $offset = ($page - 1) * $limit;

    $whereClauses = [];
    $params = [];
    $types = "";

    // Filter by view mode
    if ($view_mode === 'active') {
        $whereClauses[] = "d.statut != 'ARCHIVE'";
    } elseif ($view_mode === 'archive' || $view_mode === 'archived') {
        $whereClauses[] = "d.statut = 'ARCHIVE'";
    }

    // Filter by document type
    if (!empty($type_filter)) {
        $whereClauses[] = "d.type_document = ?";
        $params[] = $type_filter;
        $types .= "s";
    }

    // Search filter
    if (!empty($search)) {
        $whereClauses[] = "(d.nom_fichier_original LIKE ? OR d.numero_facture LIKE ? OR d.type_document LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    $whereSQL = "";
    if (count($whereClauses) > 0) {
        $whereSQL = " WHERE " . implode(" AND ", $whereClauses);
    }

    // Base SQL
    $sql = "SELECT d.*, f.nom_fournisseur, f.ville as fournisseur_ville, f.pays as fournisseur_pays 
            FROM documents d 
            LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id" . $whereSQL;

    // Count total
    $countSql = "SELECT COUNT(*) FROM documents d LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id" . $whereSQL;

    // Execute Count Query
    $stmt = mysqli_prepare($conn, $countSql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    $totalRecords = $row[0];
    $totalPages = ceil($totalRecords / $limit);

    // Fetch records
    $sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $documents = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $documents,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected error: ' . $e->getMessage()
    ]);
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>