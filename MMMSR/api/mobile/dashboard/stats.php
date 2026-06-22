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

    $total_docs = $db->query("SELECT COUNT(*) as cnt FROM documents")->fetch(PDO::FETCH_ASSOC)['cnt'];
    $archived_docs = $db->query("SELECT COUNT(*) as cnt FROM documents WHERE statut IN ('ARCHIVE','ARCHIVAL')")->fetch(PDO::FETCH_ASSOC)['cnt'];
    $active_docs = $total_docs - $archived_docs;
    $total_suppliers = $db->query("SELECT COUNT(*) as cnt FROM fournisseurs")->fetch(PDO::FETCH_ASSOC)['cnt'];
    $total_categories = $db->query("SELECT COUNT(*) as cnt FROM document_categories")->fetch(PDO::FETCH_ASSOC)['cnt'];

    $val_usd = $db->query("SELECT COALESCE(SUM(montant_ttc),0) as total FROM documents WHERE devise = 'USD'")->fetch(PDO::FETCH_ASSOC)['total'];
    $val_cdf = $db->query("SELECT COALESCE(SUM(montant_ttc),0) as total FROM documents WHERE devise = 'CDF'")->fetch(PDO::FETCH_ASSOC)['total'];

    $recent_docs = $db->query("
        SELECT d.*, f.nom_fournisseur 
        FROM documents d 
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id 
        ORDER BY d.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $status_dist = $db->query("SELECT statut, COUNT(*) as cnt FROM documents GROUP BY statut ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    $status_labels = array_column($status_dist, 'statut');
    $status_counts = array_map('intval', array_column($status_dist, 'cnt'));

    echo json_encode([
        "status" => "success",
        "stats" => [
            "total_documents" => (int)$total_docs,
            "active_documents" => (int)$active_docs,
            "archived_documents" => (int)$archived_docs,
            "total_suppliers" => (int)$total_suppliers,
            "total_categories" => (int)$total_categories,
            "total_value_usd" => (float)$val_usd,
            "total_value_cdf" => (float)$val_cdf,
        ],
        "status_distribution" => [
            "labels" => $status_labels,
            "counts" => $status_counts,
        ],
        "recent_documents" => $recent_docs,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
