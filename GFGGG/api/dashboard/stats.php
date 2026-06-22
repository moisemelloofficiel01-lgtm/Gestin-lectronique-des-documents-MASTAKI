<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/db.php';

// Helper: safe query that returns false on error instead of crashing
function safeQuery($conn, $sql) {
    $res = @mysqli_query($conn, $sql);
    if (!$res) {
        error_log("Dashboard stats query error: " . mysqli_error($conn) . " | SQL: " . $sql);
    }
    return $res;
}

function safeCount($conn, $sql) {
    $res = safeQuery($conn, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return (int)($row['cnt'] ?? $row['count'] ?? 0);
    }
    return 0;
}

function safeSum($conn, $sql) {
    $res = safeQuery($conn, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return (float)($row['total'] ?? 0);
    }
    return 0;
}

try {
    // ── 1. Core KPI Stats ──────────────────────────────────────
    $total_docs     = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents");
    $archived_docs  = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE statut IN ('ARCHIVE','ARCHIVAL')");
    $active_docs    = $total_docs - $archived_docs;
    $pending_docs   = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE statut IN ('NOUVEAU', 'EN_COURS')");
    $validated_docs = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE statut = 'VALIDE'");
    $paid_docs      = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE statut = 'PAYE'");
    $total_suppliers = safeCount($conn, "SELECT COUNT(*) as cnt FROM fournisseurs");
    $total_value_usd = safeSum($conn, "SELECT COALESCE(SUM(montant_ttc),0) as total FROM documents WHERE devise = 'USD'");
    $total_value_cdf = safeSum($conn, "SELECT COALESCE(SUM(montant_ttc),0) as total FROM documents WHERE devise = 'CDF'");
    $total_categories = safeCount($conn, "SELECT COUNT(*) as cnt FROM document_categories");

    // ── 2. Document Status Distribution (Donut Chart) ──────────
    $status_labels = [];
    $status_counts = [];
    $status_colors = [];
    $colorMap = [
        'NOUVEAU'  => '#0d6efd',
        'EN_COURS' => '#0dcaf0',
        'VALIDE'   => '#198754',
        'PAYE'     => '#20c997',
        'ARCHIVE'  => '#6c757d',
        'ARCHIVAL' => '#6c757d',
        'REJETE'   => '#dc3545'
    ];
    $res = safeQuery($conn, "SELECT statut, COUNT(*) as cnt FROM documents GROUP BY statut ORDER BY cnt DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $status_labels[] = $row['statut'];
            $status_counts[] = (int)$row['cnt'];
            $status_colors[] = isset($colorMap[$row['statut']]) ? $colorMap[$row['statut']] : '#adb5bd';
        }
    }

    // ── 3. Categories Breakdown (Bar Chart) ────────────────────
    $cat_labels = [];
    $cat_counts = [];
    $res = safeQuery($conn, "
        SELECT dc.name, COUNT(d.document_id) as cnt 
        FROM document_categories dc 
        LEFT JOIN documents d ON d.type_document = dc.code 
        GROUP BY dc.category_id, dc.name
        ORDER BY cnt DESC
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cat_labels[] = $row['name'];
            $cat_counts[] = (int)$row['cnt'];
        }
    }

    // ── 4. Monthly Trend (Last 12 Months) ──────────────────────
    $months = [];
    $month_new = [];
    $month_archived = [];
    for ($m = 11; $m >= 0; $m--) {
        $date = date('Y-m', strtotime("-$m months"));
        $months[] = date('M Y', strtotime($date . '-01'));
        $month_new[]      = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE DATE_FORMAT(created_at, '%Y-%m') = '$date'");
        $month_archived[] = safeCount($conn, "SELECT COUNT(*) as cnt FROM documents WHERE DATE_FORMAT(date_archivage, '%Y-%m') = '$date'");
    }

    // ── 5. Top Suppliers by Document Count ─────────────────────
    $top_suppliers_labels = [];
    $top_suppliers_counts = [];
    $res = safeQuery($conn, "
        SELECT f.nom_fournisseur, COUNT(d.document_id) as cnt 
        FROM fournisseurs f 
        LEFT JOIN documents d ON d.fournisseur_id = f.fournisseur_id 
        GROUP BY f.fournisseur_id, f.nom_fournisseur 
        ORDER BY cnt DESC 
        LIMIT 5
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $top_suppliers_labels[] = $row['nom_fournisseur'];
            $top_suppliers_counts[] = (int)$row['cnt'];
        }
    }

    // ── 6. Recent Documents ────────────────────────────────────
    $recent_docs = [];
    $res = safeQuery($conn, "
        SELECT d.*, f.nom_fournisseur 
        FROM documents d 
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id 
        ORDER BY d.created_at DESC 
        LIMIT 6
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $recent_docs[] = $row;
        }
    }

    // ── 7. Recent Archived Documents ───────────────────────────
    $recent_archived = [];
    $res = safeQuery($conn, "
        SELECT d.*, f.nom_fournisseur 
        FROM documents d 
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id 
        WHERE d.statut IN ('ARCHIVE','ARCHIVAL') 
        ORDER BY d.date_archivage DESC 
        LIMIT 5
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $recent_archived[] = $row;
        }
    }

    // ── Output ─────────────────────────────────────────────────
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_documents'    => $total_docs,
            'active_documents'   => $active_docs,
            'archived_documents' => $archived_docs,
            'total_suppliers'    => $total_suppliers,
            'pending_validation' => $pending_docs,
            'validated'          => $validated_docs,
            'paid'               => $paid_docs,
            'total_value_usd'    => $total_value_usd,
            'total_value_cdf'    => $total_value_cdf,
            'total_categories'   => $total_categories
        ],
        'charts' => [
            'status_distribution' => [
                'labels' => $status_labels,
                'counts' => $status_counts,
                'colors' => $status_colors
            ],
            'categories' => [
                'labels' => $cat_labels,
                'counts' => $cat_counts
            ],
            'monthly_trend' => [
                'months'   => $months,
                'new_docs' => $month_new,
                'archived' => $month_archived
            ],
            'top_suppliers' => [
                'labels' => $top_suppliers_labels,
                'counts' => $top_suppliers_counts
            ]
        ],
        'recent_documents' => $recent_docs,
        'recent_archived'  => $recent_archived
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>
