<?php
require_once 'config/db.php';
include 'pages/header.php';

$fournisseurId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($fournisseurId == 0) {
    header("Location: fournisseurs.php");
    exit;
}

$fournisseur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nom_fournisseur, categorie_fournisseur, ville, pays FROM fournisseurs WHERE fournisseur_id = $fournisseurId"));
if (!$fournisseur) {
    echo "<div class='page-wrapper'><div class='content'><div class='alert alert-danger'>Fournisseur non trouvé.</div></div></div>";
    include 'pages/footer.php';
    exit;
}
?>

<div class="page-wrapper">
    <div class="content pb-0">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" id="docsBreadcrumb">
                        <li class="breadcrumb-item"><a href="fournisseurs.php">Fournisseurs</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($fournisseur['nom_fournisseur']); ?></li>
                    </ol>
                </nav>
                <h4 class="mb-0">Documents du fournisseur</h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($fournisseur['nom_fournisseur']); ?> — <?php echo htmlspecialchars($fournisseur['categorie_fournisseur'] ?? ''); ?> — <?php echo htmlspecialchars($fournisseur['ville'] ?? ''); ?>, <?php echo htmlspecialchars($fournisseur['pays'] ?? ''); ?></p>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <a href="documents.php" class="btn btn-primary shadow-sm">
                    <i class="ti ti-file-text me-1"></i> Tous les Documents
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 ms-auto">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="searchDoc" class="form-control border-start-0" placeholder="Rechercher (nom, facture, type...)">
                </div>
            </div>
        </div>

        <div class="row" id="docsGrid">
            <!-- Cards will be loaded here via AJAX -->
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/footer.php'; ?>

<style>
.bg-primary-soft { background-color: #e8f0fe; color: #1a73e8; }
.bg-success-soft { background-color: #e6f4ea; color: #1e7e34; }
.bg-info-soft { background-color: #e2f2fd; color: #01579b; }
.bg-secondary-soft { background-color: #f1f3f4; color: #5f6368; }
</style>

<script>
let currentPage = 1;
const fournisseurId = <?php echo $fournisseurId; ?>;

$(document).ready(function() {
    loadDocs();

    let timeout = null;
    $('#searchDoc').on('keyup', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() { currentPage = 1; loadDocs(); }, 500);
    });
});

function loadDocs() {
    const search = $('#searchDoc').val();
    $.ajax({
        url: 'api/documents/read',
        type: 'GET',
        cache: false,
            data: { page: currentPage, limit: 12, search: search, fournisseur_id: fournisseurId, view_mode: 'all' },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') return;
            $('#totalDocs').text(response.pagination.total_records + ' documents');
            let cards = '';
            if (response.data.length > 0) {
                response.data.forEach(function(item) {
                    let badgeClass = 'bg-primary-soft';
                    if (item.statut === 'VALIDE') badgeClass = 'bg-success-soft';
                    if (item.statut === 'ARCHIVAL') badgeClass = 'bg-secondary-soft';
                    if (item.statut === 'PAYE') badgeClass = 'bg-info-soft';

                    const fileExt = item.extension_fichier ? item.extension_fichier.toLowerCase() : '';
                    let iconClass = 'ti-file-description';
                    if (['pdf'].includes(fileExt)) iconClass = 'ti-file-type-pdf text-danger';
                    if (['jpg','jpeg','png'].includes(fileExt)) iconClass = 'ti-file-type-jpg text-primary';
                    if (['xls','xlsx'].includes(fileExt)) iconClass = 'ti-file-type-xls text-success';

                    cards += `
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm document-card hover-lift">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="avatar avatar-md rounded bg-light">
                                            <i class="ti ${iconClass} fs-24"></i>
                                        </div>
                                        <span class="badge ${badgeClass} fs-10">${item.statut}</span>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="mb-1 text-truncate fw-bold" title="${item.nom_fichier_original}">${item.nom_fichier_original}</h6>
                                        <p class="text-muted fs-12 mb-0 d-flex align-items-center">
                                            <i class="ti ti-building me-1"></i>
                                            <span class="text-truncate">${item.nom_fournisseur || 'DIVERS'}</span>
                                        </p>
                                    </div>
                                    <div class="bg-light p-2 rounded mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Réf:</small>
                                            <small class="fw-bold">${item.numero_facture || '-'}</small>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Total:</small>
                                            <small class="fw-bold text-dark">${item.montant_ttc ? item.montant_ttc + ' ' + (item.devise || '') : '-'}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <small class="text-muted fs-11">${item.date_facture || '---'}</small>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-white border shadow-none" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="document.php?id=${item.document_id}"><i class="ti ti-eye me-2"></i>Détails</a>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="downloadDocument(${item.document_id}, '${item.statut}')"><i class="ti ti-download me-2"></i>Télécharger</a>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="shareDocument(${item.document_id})"><i class="ti ti-share me-2"></i>Partager</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                cards = '<div class="col-12 text-center py-5"><i class="ti ti-file-text text-muted fs-40 d-block mb-2"></i><h5>Aucun document trouvé</h5><a href="documents.php" class="btn btn-primary mt-3">Classer un document</a></div>';
            }
            $('#docsGrid').html(cards);
            renderPagination(response.pagination);
        }
    });
}

function renderPagination(pagination) {
    let html = '';
    const totalPages = pagination.total_pages;
    const current = pagination.current_page;
    if (totalPages > 1) {
        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${current - 1})">Précédent</a></li>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${i})">${i}</a></li>`;
        }
        html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${current + 1})">Suivant</a></li>`;
    }
    $('#paginationControls').html(html);
}

function changePage(page) { currentPage = page; loadDocs(); }

function downloadDocument(docId, statut) {
    var downloadUrl = 'api/documents/download?id=' + docId;
    if (statut === 'ARCHIVE' || statut === 'ARCHIVAL') {
        requestPassword(function(verified) { if(verified) window.location.href = downloadUrl; });
    } else {
        window.location.href = downloadUrl;
    }
}
</script>
