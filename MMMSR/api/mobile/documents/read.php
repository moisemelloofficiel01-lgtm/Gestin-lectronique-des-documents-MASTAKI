<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: " . $origin);
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(d.nom_fichier_original LIKE :search OR d.numero_facture LIKE :search2 OR f.nom_fournisseur LIKE :search3)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }
    if (!empty($type_filter)) {
        $where[] = "d.type_document = :type";
        $params[':type'] = $type_filter;
    }
    if (!empty($status_filter)) {
        if ($status_filter === 'ACTIF') {
            $where[] = "(d.statut NOT IN ('ARCHIVE','ARCHIVAL'))";
        } else {
            $where[] = "d.statut = :status";
            $params[':status'] = $status_filter;
        }
    }

    $whereSQL = count($where) ? " WHERE " . implode(" AND ", $where) : "";

    $countSQL = "SELECT COUNT(*) FROM documents d LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id" . $whereSQL;
    $countStmt = $db->prepare($countSQL);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalRecords / $limit));

    $dataSQL = "SELECT d.*, f.nom_fournisseur, f.ville as fournisseur_ville 
                FROM documents d 
                LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id" .
                $whereSQL . " ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset";
    $dataStmt = $db->prepare($dataSQL);
    $dataStmt->execute($params);
    $documents = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $documents,
        "pagination" => [
            "page" => $page,
            "limit" => $limit,
            "total_pages" => $totalPages,
            "total_records" => $totalRecords,
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
