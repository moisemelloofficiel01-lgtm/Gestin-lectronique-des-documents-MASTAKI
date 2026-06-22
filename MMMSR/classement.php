<?php
include 'pages/header.php';
require_once 'config/db.php';

// Get dynamic categories from database
$categories = [];
$resCat = mysqli_query($conn, "SELECT code, name, icon, color FROM document_categories ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($resCat)) {
    $categories[$row['code']] = [
        'name' => $row['name'],
        'icon' => $row['icon'],
        'color' => $row['color']
    ];
}

$counts = [];
$sql = "SELECT type_document, COUNT(*) as count FROM documents GROUP BY type_document";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $counts[$row['type_document']] = $row['count'];
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="card-title mb-1">Classement par Catégorie</h4>
                <p class="text-muted mb-0">Visualisez vos documents organisés par type</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                    <i class="ti ti-plus me-1"></i> Nouveau Classement
                </a>
            </div>
        </div>

        <div class="row mt-4">
            <?php foreach ($categories as $key => $cat): ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg rounded bg-<?php echo $cat['color']; ?>-soft text-<?php echo $cat['color']; ?> me-3">
                                <i class="ti <?php echo $cat['icon']; ?> fs-24"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><?php echo $cat['name']; ?></h5>
                                <span class="text-muted fs-12 uppercase">Type: <?php echo $key; ?></span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo isset($counts[$key]) ? $counts[$key] : 0; ?></h3>
                                <p class="text-muted mb-0 fs-13">Documents classés</p>
                            </div>
                            <a href="detail_classement.php?type=<?php echo $key; ?>" class="btn btn-primary-soft btn-sm px-3">
                                Consulter <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light-soft border-0 py-2 text-center fs-11">
                        Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'pages/document_modals.php'; ?>
<?php include 'pages/footer.php'; ?>

<style>
.bg-primary-soft { background-color: #e8f0fe; color: #1a73e8; }
.bg-success-soft { background-color: #e6f4ea; color: #1e7e34; }
.bg-info-soft { background-color: #e2f2fd; color: #01579b; }
.bg-warning-soft { background-color: #fff4e5; color: #ffa000; }
.bg-danger-soft { background-color: #fce8e6; color: #d93025; }
.bg-secondary-soft { background-color: #f1f3f4; color: #5f6368; }
.btn-primary-soft { background-color: #e8f0fe; color: #1a73e8; border: none; }
.btn-primary-soft:hover { background-color: #1a73e8; color: #fff; }
</style>
