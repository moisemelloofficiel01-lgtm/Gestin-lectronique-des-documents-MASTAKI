<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schema_helper.php';

// Ensure table schema is correct
ensureFournisseursTableSchema($conn);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search === 'undefined' || $search === 'null') {
    $search = '';
}
$offset = ($page - 1) * $limit;
if ($offset < 0) $offset = 0;

$sql = "SELECT * FROM fournisseurs WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (nom_fournisseur LIKE ? OR logo LIKE ? OR adresse LIKE ? OR ville LIKE ? OR pays LIKE ? OR email_general LIKE ? OR telephone_principal LIKE ? OR categorie_fournisseur LIKE ? OR secteur_activite LIKE ?)";
}

// Count total records
$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = mysqli_prepare($conn, $countSql);

if (!empty($search)) {
    $searchTerm = "%$search%";
    mysqli_stmt_bind_param($stmt, "sssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
$totalRecords = $row[0];
$totalPages = ceil($totalRecords / $limit);

// Fetch records
$sql .= " ORDER BY date_creation DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);

if (!empty($search)) {
    $searchTerm = "%$search%";
    mysqli_stmt_bind_param($stmt, "sssssssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$fournisseurs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fournisseurs[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $fournisseurs,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords
    ],
    'debug_info' => [
        'search_param' => $search,
        'sql_query' => $sql,
        'limit' => $limit,
        'offset' => $offset
    ]
]);

mysqli_close($conn);
?>
