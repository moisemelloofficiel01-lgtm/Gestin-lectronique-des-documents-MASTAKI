<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';
    $parentId = isset($data['parent_id']) && $data['parent_id'] ? (int)$data['parent_id'] : null;

    if ($action === 'create_folder') {
        $nom = trim($data['nom'] ?? '');
        if (empty($nom)) {
            echo json_encode(["status" => "error", "message" => "Nom du dossier requis"]);
            exit;
        }
        if ($parentId) {
            $stmt = mysqli_prepare($conn, "INSERT INTO dossiers (nom, parent_id, created_by) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sii", $nom, $parentId, $userId);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO dossiers (nom, created_by) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "si", $nom, $userId);
        }
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Dossier créé", "id" => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
        exit;
    }

    if ($action === 'rename') {
        $id = (int)$data['id'];
        $nom = trim($data['nom'] ?? '');
        if (empty($nom)) { echo json_encode(["status" => "error", "message" => "Nom requis"]); exit; }
        mysqli_query($conn, "UPDATE dossiers SET nom = '" . mysqli_real_escape_string($conn, $nom) . "' WHERE dossier_id = $id AND created_by = $userId");
        echo json_encode(["status" => "success", "message" => "Renommé"]);
        exit;
    }

    if ($action === 'delete_folder') {
        $id = (int)$data['id'];
        mysqli_query($conn, "DELETE FROM dossiers WHERE dossier_id = $id AND created_by = $userId");
        echo json_encode(["status" => "success", "message" => "Dossier supprimé"]);
        exit;
    }

    if ($action === 'delete_file') {
        $id = (int)$data['id'];
        $f = mysqli_fetch_assoc(mysqli_query($conn, "SELECT chemin_stockage FROM documents_personnels WHERE document_id = $id AND created_by = $userId"));
        if ($f) {
            @unlink(__DIR__ . '/../../' . $f['chemin_stockage']);
            mysqli_query($conn, "DELETE FROM documents_personnels WHERE document_id = $id");
        }
        echo json_encode(["status" => "success", "message" => "Fichier supprimé"]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Action inconnue"]);
    exit;
}

$action = $_GET['action'] ?? 'list';
$parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;

if ($action === 'list') {
    $where = "d.created_by = $userId AND d.parent_id " . ($parentId ? "= $parentId" : "IS NULL");
    $dossiers = mysqli_query($conn, "SELECT d.*, (SELECT COUNT(*) FROM dossiers WHERE parent_id = d.dossier_id) AS nb_sous_dossiers, (SELECT COUNT(*) FROM documents_personnels WHERE dossier_id = d.dossier_id) AS nb_fichiers FROM dossiers d WHERE $where ORDER BY d.nom ASC");
    $folders = [];
    while ($r = mysqli_fetch_assoc($dossiers)) { $folders[] = $r; }
    $filesWhere = "p.created_by = $userId AND p.dossier_id " . ($parentId ? "= $parentId" : "IS NULL");
    $files = mysqli_query($conn, "SELECT p.*, u.nom, u.prenom FROM documents_personnels p LEFT JOIN utilisateurs u ON p.created_by = u.utilisateur_id WHERE $filesWhere ORDER BY p.created_at DESC");
    $fileList = [];
    while ($r = mysqli_fetch_assoc($files)) { $fileList[] = $r; }
    $breadcrumb = [];
    if ($parentId) {
        $pid = $parentId;
        while ($pid) {
            $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dossier_id, nom, parent_id FROM dossiers WHERE dossier_id = $pid"));
            if ($p) { array_unshift($breadcrumb, $p); $pid = $p['parent_id']; } else break;
        }
    }
    echo json_encode(["status" => "success", "folders" => $folders, "files" => $fileList, "breadcrumb" => $breadcrumb, "current_folder" => $parentId]);
    exit;
}

if ($action === 'tree') {
    $all = mysqli_query($conn, "SELECT dossier_id, nom, parent_id FROM dossiers WHERE created_by = $userId ORDER BY nom");
    $tree = [];
    while ($r = mysqli_fetch_assoc($all)) $tree[] = $r;
    echo json_encode(["status" => "success", "data" => $tree]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Action inconnue"]);
