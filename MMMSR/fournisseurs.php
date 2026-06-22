<?php
include 'pages/header.php';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="card-title mb-1">Gestion des Fournisseurs</h4>
                <p class="text-muted mb-0" id="totalFournisseurs">0 fournisseurs enregistrés</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">
                    <i class="ti ti-plus me-1"></i> Nouveau Fournisseur
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="searchFournisseur" class="form-control border-start-0" placeholder="Rechercher un fournisseur...">
                </div>
            </div>
        </div>

        <!-- Card Grid -->
        <div class="row" id="fournisseursGrid">
            <!-- Cards will be loaded here via AJAX -->
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-md-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal (Keep existing but style it better if needed) -->
<!-- ... (Existing Modals) ... -->
<?php include 'pages/fournisseur_modals.php'; ?>

<?php include 'pages/footer.php'; ?>

<script>
$(document).ready(function() {
    loadFournisseurs();

    let timeout = null;
    $('#searchFournisseur').on('keyup', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            loadFournisseurs(1);
        }, 500);
    });

    // Image Preview for Add Modal
    $('#add_logo_input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { $('#add_logo_preview').attr('src', e.target.result).show(); }
            reader.readAsDataURL(file);
        }
    });

    // Add Form Submit
    $('#addFournisseurForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'api/fournisseurs/create',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#addFournisseurModal').modal('hide');
                    $('#addFournisseurForm')[0].reset();
                    loadFournisseurs();
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: response.message });
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur create:", xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Erreur Serveur', text: 'Voir console pour détails (F12)' });
            }
        });
    });

    // Edit Form Submit
    $('#editFournisseurForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'api/fournisseurs/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#editFournisseurModal').modal('hide');
                    loadFournisseurs();
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: response.message });
                }
            }
        });
    });

    // Delete Confirmation
    $('#confirmDeleteBtn').on('click', function() {
        const id = $('#delete_fournisseur_id').val();
        $.ajax({
            url: 'api/fournisseurs/delete',
            type: 'POST',
            data: { fournisseur_id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteFournisseurModal').modal('hide');
                    loadFournisseurs();
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message });
                }
            }
        });
    });
});

function openViewModal(id) {
    $.ajax({
        url: 'api/fournisseurs/get_one',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;
                $('#view_logo').attr('src', data.logo ? 'uploads/fournisseurs/' + data.logo : 'assets/img/default-logo.png');
                $('#view_nom_fournisseur').text(data.nom_fournisseur);
                $('#view_statut').text(data.statut).removeClass().addClass('badge ' + (data.statut === 'ACTIF' ? 'bg-success-soft' : 'bg-danger-soft'));
                $('#view_categorie_fournisseur').text(data.categorie_fournisseur);
                $('#view_localisation').text(`${data.ville || ''}, ${data.pays || ''}`);
                $('#view_telephone_principal').text(data.telephone_principal || '-');
                $('#view_email_general').text(data.email_general || '-');
                $('#view_adresse').text(data.adresse || 'Aucune adresse spécifiée');
                $('#viewFournisseurModal').modal('show');
            }
        }
    });
}

function openEditModal(id) {
    $.ajax({
        url: 'api/fournisseurs/get_one',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;
                $('#edit_fournisseur_id').val(data.fournisseur_id);
                $('#edit_nom_fournisseur').val(data.nom_fournisseur);
                $('#edit_logo_preview_old').attr('src', data.logo ? 'uploads/fournisseurs/' + data.logo : 'assets/img/default-logo.png');
                $('#edit_categorie_fournisseur').val(data.categorie_fournisseur);
                $('#edit_adresse').val(data.adresse);
                $('#edit_ville').val(data.ville);
                $('#edit_pays').val(data.pays);
                $('#edit_statut').val(data.statut);
                $('#editFournisseurModal').modal('show');
            }
        }
    });
}

function loadFournisseurs(page = 1) {
    const search = $('#searchFournisseur').val();
    $.ajax({
        url: 'api/fournisseurs/read',
        type: 'GET',
        cache: false,
        data: { page: page, search: search, limit: 12 },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#totalFournisseurs').text(response.pagination.total_records + ' fournisseurs enregistrés');
                let cards = '';
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const logoSrc = item.logo ? `uploads/fournisseurs/${item.logo}` : 'assets/img/default-logo.png';
                        cards += `
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm supplier-card hover-lift">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar avatar-lg rounded border p-1 bg-white me-3">
                                                <img src="${logoSrc}" alt="Logo" class="img-fluid" style="object-fit: contain; width: 48px; height: 48px;">
                                            </div>
                                            <div class="overflow-hidden">
                                                <h6 class="mb-0 text-truncate fw-bold">${item.nom_fournisseur}</h6>
                                                <span class="badge ${item.statut === 'ACTIF' ? 'bg-success-soft' : 'bg-danger-soft'} fs-10">${item.statut}</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="ti ti-map-pin text-muted me-2 fs-14"></i>
                                                <span class="text-muted fs-13 text-truncate">${item.ville || '-'}, ${item.pays || '-'}</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="ti ti-mail text-muted me-2 fs-14"></i>
                                                <span class="text-muted fs-13 text-truncate">${item.email_general || '-'}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-phone text-muted me-2 fs-14"></i>
                                                <span class="text-muted fs-13">${item.telephone_principal || '-'}</span>
                                            </div>
                                        </div>
                                        <div class="pt-3 border-top mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-light text-dark fs-11">${item.categorie_fournisseur || 'DIVERS'}</span>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-icon btn-white border shadow-none" data-bs-toggle="dropdown">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="fournisseur_documents.php?id=${item.fournisseur_id}"><i class="ti ti-file-text me-2"></i>Documents</a>
                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="openViewModal(${item.fournisseur_id})"><i class="ti ti-eye me-2"></i>Détails</a>
                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="openEditModal(${item.fournisseur_id})"><i class="ti ti-pencil me-2"></i>Modifier</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="openDeleteModal(${item.fournisseur_id})"><i class="ti ti-trash me-2"></i>Supprimer</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    cards = `
                        <div class="col-12 text-center py-5">
                            <div class="mb-3"><i class="ti ti-users text-muted fs-40"></i></div>
                            <h5>Aucun fournisseur trouvé</h5>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">Ajouter un fournisseur</button>
                        </div>
                    `;
                }
                $('#fournisseursGrid').html(cards);
                renderPagination(response.pagination);
            }
        }
    });
}


function openDeleteModal(id) {
    $('#delete_fournisseur_id').val(id);
    $('#deleteFournisseurModal').modal('show');
}

function renderPagination(pagination) {
    let html = '';
    const totalPages = pagination.total_pages;
    const currentPage = pagination.current_page;
    if (totalPages > 1) {
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadFournisseurs(${currentPage - 1})">Précédent</a></li>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadFournisseurs(${i})">${i}</a></li>`;
        }
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadFournisseurs(${currentPage + 1})">Suivant</a></li>`;
    }
    $('#paginationControls').html(html);
}
</script>

<style>
.bg-success-soft { background-color: #e6f4ea; color: #1e7e34; }
.bg-danger-soft { background-color: #fdeaea; color: #d93025; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.supplier-card { border-radius: 12px; }
.btn-icon { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; }
</style>
