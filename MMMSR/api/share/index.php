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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        // Documents partagés avec moi
        $sharedWithMe = mysqli_query($conn, "SELECT dp.*, d.nom_fichier_original, d.uuid_document, d.extension_fichier, d.chemin_stockage, u.nom, u.prenom, u.email AS shared_by_email FROM documents_partages dp JOIN documents d ON dp.document_id = d.document_id JOIN utilisateurs u ON dp.shared_by = u.utilisateur_id WHERE dp.shared_with = $userId ORDER BY dp.created_at DESC");
        $withMe = [];
        while ($r = mysqli_fetch_assoc($sharedWithMe)) {
            $withMe[] = $r;
        }

        // Documents que j'ai partagés
        $sharedByMe = mysqli_query($conn, "SELECT dp.*, d.nom_fichier_original, d.uuid_document, d.extension_fichier, u.nom, u.prenom, u.email AS shared_with_email FROM documents_partages dp JOIN documents d ON dp.document_id = d.document_id JOIN utilisateurs u ON dp.shared_with = u.utilisateur_id WHERE dp.shared_by = $userId ORDER BY dp.created_at DESC");
        $byMe = [];
        while ($r = mysqli_fetch_assoc($sharedByMe)) {
            $byMe[] = $r;
        }

        echo json_encode(["status" => "success", "shared_with_me" => $withMe, "shared_by_me" => $byMe]);
        exit;
    }

    if ($action === 'users') {
        $users = mysqli_query($conn, "SELECT utilisateur_id, nom, prenom, email FROM utilisateurs WHERE actif = 1 AND utilisateur_id != $userId ORDER BY nom, prenom");
        $list = [];
        while ($r = mysqli_fetch_assoc($users)) $list[] = $r;
        echo json_encode(["status" => "success", "data" => $list]);
        exit;
    }

    if ($action === 'documents') {
        $docs = mysqli_query($conn, "SELECT d.document_id, d.nom_fichier_original, d.type_document FROM documents d WHERE d.created_by = $userId ORDER BY d.created_at DESC LIMIT 50");
        $list = [];
        while ($r = mysqli_fetch_assoc($docs)) $list[] = $r;
        echo json_encode(["status" => "success", "data" => $list]);
        exit;
    }

    if ($action === 'categories') {
        $cats = mysqli_query($conn, "SELECT category_id, code, name FROM document_categories ORDER BY name");
        $list = [];
        while ($r = mysqli_fetch_assoc($cats)) $list[] = $r;
        echo json_encode(["status" => "success", "data" => $list]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';

    if ($action === 'share_document') {
        $docId = (int)$data['document_id'];
        $sharedWith = (int)$data['shared_with'];
        $permission = $data['permission'] ?? 'view';

        $check = mysqli_query($conn, "SELECT * FROM documents_partages WHERE document_id = $docId AND shared_with = $sharedWith");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(["status" => "error", "message" => "Déjà partagé avec cet utilisateur"]);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO documents_partages (document_id, shared_by, shared_with, permission, type_partage) VALUES (?, $userId, ?, ?, 'document')");
        mysqli_stmt_bind_param($stmt, "iis", $docId, $sharedWith, $permission);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Document partagé"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
        exit;
    }

    if ($action === 'share_category') {
        $catId = (int)$data['category_id'];
        $sharedWith = (int)$data['shared_with'];
        $permission = $data['permission'] ?? 'view';

        $check = mysqli_query($conn, "SELECT * FROM partage_categories WHERE category_id = $catId AND shared_with = $sharedWith");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(["status" => "error", "message" => "Catégorie déjà partagée"]);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO partage_categories (category_id, shared_by, shared_with, permission) VALUES (?, $userId, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $catId, $sharedWith, $permission);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Catégorie partagée"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)$data['id'];
        mysqli_query($conn, "DELETE FROM documents_partages WHERE partage_id = $id AND (shared_by = $userId OR shared_with = $userId)");
        echo json_encode(["status" => "success", "message" => "Partage supprimé"]);
        exit;
    }

    if ($action === 'delete_category_share') {
        $id = (int)$data['id'];
        mysqli_query($conn, "DELETE FROM partage_categories WHERE partage_id = $id AND (shared_by = $userId OR shared_with = $userId)");
        echo json_encode(["status" => "success", "message" => "Partage supprimé"]);
        exit;
    }
}
