<?php
include 'pages/header.php';
require_once 'config/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'FACTURE';
$types_map = [
    'FACTURE' => 'Factures',
    'BON_COMMANDE' => 'Bons de Commande',
    'BON_LIVRAISON' => 'Bons de Livraison',
    'DEVIS' => 'Devis',
    'CONTRAT' => 'Contrats',
    'AUTRE' => 'Autres'
];
$display_name = isset($types_map[$type]) ? $types_map[$type] : $type;
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="classement.php">Classement</a></li>
                        <li class="breadcrumb-item active"><?php echo $display_name; ?></li>
                    </ol>
                </nav>
                <h4 class="card-title mb-1">Documents : <?php echo $display_name; ?></h4>
                <p class="text-muted mb-0" id="catCount">Chargement...</p>
            </div>
            <div class="col-auto">
                <a href="classement.php" class="btn btn-white border shadow-sm">
                    <i class="ti ti-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="searchCat" class="form-control border-start-0" placeholder="Rechercher dans cette catégorie...">
                </div>
            </div>
        </div>

        <div class="row" id="catDocumentsGrid">
            <!-- Data loaded via AJAX -->
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

<?php include 'pages/document_modals.php'; ?>
<?php include 'pages/footer.php'; ?>

<script>
$(document).ready(function() {
    loadCatDocuments();

    $('#searchCat').on('keyup', function() {
        loadCatDocuments(1);
    });
});

function loadCatDocuments(page = 1) {
    const search = $('#searchCat').val();
    const type = '<?php echo $type; ?>';
    
    $.ajax({
        url: 'api/documents/read.php',
        type: 'GET',
        cache: false,
        data: { 
            page: page, 
            search: search, 
            type_filter: type, // We need to update read.php to handle this filter
            view_mode: 'all', 
            limit: 12 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#catCount').text(response.pagination.total_records + ' documents trouvés');
                let cards = '';
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        let badgeClass = 'bg-primary-soft';
                        if (item.statut === 'VALIDE') badgeClass = 'bg-success-soft';
                        if (item.statut === 'ARCHIVE') badgeClass = 'bg-secondary-soft';
                        
                        cards += `
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm document-card hover-lift">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="avatar avatar-md rounded bg-light">
                                                <i class="ti ti-file-text fs-20 text-primary"></i>
                                            </div>
                                            <span class="badge ${badgeClass} fs-10">${item.statut}</span>
                                        </div>
                                        <h6 class="text-truncate fw-bold mb-1" title="${item.nom_fichier_original}">${item.nom_fichier_original}</h6>
                                        <p class="text-muted fs-12 mb-2"><i class="ti ti-building me-1"></i>${item.nom_fournisseur || 'DIVERS'}</p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                            <small class="text-muted fs-11">${item.date_facture || '--'}</small>
                                            <a href="document.php?id=${item.document_id}" class="btn btn-xs btn-outline-primary">Détails</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    cards = '<div class="col-12 text-center py-5"><p class="text-muted">Aucun document dans cette catégorie</p></div>';
                }
                $('#catDocumentsGrid').html(cards);
                renderPagination(response.pagination);
            }
        }
    });
}

function renderPagination(pagination) {
    let html = '';
    const totalPages = pagination.total_pages;
    const currentPage = pagination.current_page;
    if (totalPages > 1) {
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadCatDocuments(${currentPage - 1})">Précédent</a></li>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadCatDocuments(${i})">${i}</a></li>`;
        }
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadCatDocuments(${currentPage + 1})">Suivant</a></li>`;
    }
    $('#paginationControls').html(html);
}
</script>

<style>
.bg-primary-soft { background-color: #e8f0fe; color: #1a73e8; }
.bg-success-soft { background-color: #e6f4ea; color: #1e7e34; }
.bg-secondary-soft { background-color: #f1f3f4; color: #5f6368; }
</style>
