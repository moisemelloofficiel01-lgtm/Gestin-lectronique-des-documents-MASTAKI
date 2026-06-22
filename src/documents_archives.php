<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id'])) {
    header("Location: " . getBasePath() . "login.php");
    exit();
}
include 'pages/header.php';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="card-title mb-1">Archives des Documents</h4>
                <p class="text-muted mb-0">Historique des documents archivés</p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4 ms-auto">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="searchArchive" class="form-control border-start-0" placeholder="Rechercher dans les archives...">
                </div>
            </div>
        </div>
        <div class="row" id="archivesGrid"></div>
        <div class="row mt-4">
            <div class="col-md-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/document_modals.php'; ?>
<?php include 'pages/footer.php'; ?>

<script>
let currentViewMode = 'archived';

$(document).ready(function() {
    loadDocuments();
    $('#searchArchive').on('keyup', function() {
        loadDocuments(1);
    });
});

function loadDocuments(page = 1) {
    const search = $('#searchArchive').val();
    $.ajax({
        url: 'api/documents/read',
        type: 'GET',
        data: { page: page, search: search, view_mode: 'archived', limit: 12 },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') return;
            let cards = '';
            if (response.data.length > 0) {
                response.data.forEach(function(item) {
                    const fileExt = item.extension_fichier ? item.extension_fichier.toLowerCase() : '';
                    let iconClass = 'ti-archive text-secondary';
                    if (['pdf'].includes(fileExt)) iconClass = 'ti-file-type-pdf text-danger';
                    if (['jpg','jpeg','png'].includes(fileExt)) iconClass = 'ti-file-type-jpg text-primary';

                    cards += `
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="avatar avatar-md rounded bg-secondary-soft text-secondary">
                                            <i class="ti ${iconClass} fs-24"></i>
                                        </div>
                                        <span class="badge bg-secondary fs-10">ARCHIVE</span>
                                    </div>
                                    <h6 class="mb-1 text-truncate fw-bold">${item.nom_fichier_original}</h6>
                                    <p class="text-muted fs-12 mb-3">${item.nom_fournisseur || 'DIVERS'}</p>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mb-2">
                                        <small class="text-muted">${item.date_archivage || 'Date inconnue'}</small>
                                        <a href="document.php?id=${item.document_id}" class="btn btn-xs btn-outline-secondary">Détails</a>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-xs btn-success flex-fill" onclick="downloadDocument(${item.document_id})">
                                            <i class="ti ti-download me-1"></i>Télécharger
                                        </button>
                                        <button class="btn btn-xs btn-outline-primary flex-fill" onclick="unarchiveDocument(${item.document_id})">
                                            <i class="ti ti-archive-off me-1"></i>Désarchiver
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                cards = '<div class="col-12 text-center py-5"><i class="ti ti-archive-off fs-40 text-muted d-block mb-2"></i><h6>Aucun document archivé</h6></div>';
            }
            $('#archivesGrid').html(cards);
            renderPagination(response.pagination);
        }
    });
}

function downloadDocument(docId) {
    requestPassword(function(verified) {
        if(verified) { window.location.href = 'api/documents/download?id=' + docId; }
    });
}

function unarchiveDocument(id) {
    requestPassword(function(verified) {
        if(verified) {
            Swal.fire({
                title: 'Confirmer le désarchivage',
                text: "Le document sera remis en statut VALIDE.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, désarchiver',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if(result.isConfirmed) {
                    $.ajax({
                        url: 'api/documents/unarchive',
                        type: 'POST',
                        data: { document_id: id },
                        dataType: 'json',
                        success: function(response) {
                            if(response.status === 'success') {
                                Toast.fire({ icon: 'success', title: 'Document désarchivé' });
                                loadDocuments();
                            } else {
                                Swal.fire('Erreur', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }
    });
}

function renderPagination(pagination) {
    let html = '';
    const totalPages = pagination.total_pages;
    const currentPage = pagination.current_page;
    if (totalPages > 1) {
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadDocuments(${currentPage - 1})">Précédent</a></li>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadDocuments(${i})">${i}</a></li>`;
        }
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadDocuments(${currentPage + 1})">Suivant</a></li>`;
    }
    $('#paginationControls').html(html);
}
</script>

<style>
.bg-secondary-soft { background-color: rgba(108,117,125,.1) !important; }
</style>
