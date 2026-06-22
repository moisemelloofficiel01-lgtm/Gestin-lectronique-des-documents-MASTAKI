<?php
include 'pages/header.php';
require_once 'config/db.php';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="card-title mb-1">Configuration des Catégories</h4>
                <p class="text-muted mb-0">Gérez les types de documents acceptés dans le système</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="ti ti-plus me-1"></i> Nouvelle Catégorie
                </button>
            </div>
        </div>

        <div class="row" id="categoriesGrid">
            <!-- Categories loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ajouter une Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom de la catégorie</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Factures" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code (Unique)</label>
                        <input type="text" name="code" class="form-control" placeholder="Ex: FACTURE" required>
                        <small class="text-muted">Utilisé pour identifier la catégorie techniquement.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icône (Tabler)</label>
                            <input type="text" name="icon" class="form-control" placeholder="Ex: ti-file-invoice" value="ti-file">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Couleur</label>
                            <select name="color" class="form-select">
                                <option value="primary">Bleu</option>
                                <option value="success">Vert</option>
                                <option value="info">Cyan</option>
                                <option value="warning">Jaune</option>
                                <option value="danger">Rouge</option>
                                <option value="secondary">Gris</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'pages/footer.php'; ?>

<script>
$(document).ready(function() {
    loadCategories();

    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const data = {
            name: $(this).find('[name="name"]').val(),
            code: $(this).find('[name="code"]').val(),
            icon: $(this).find('[name="icon"]').val(),
            color: $(this).find('[name="color"]').val()
        };

        console.log("Données envoyées:", data);

        $.ajax({
            url: 'api/categories/create',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log("Réponse du serveur:", response);
                if(response.status === 'success') {
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                    loadCategories();
                    Swal.fire('Succès', 'Catégorie créée', 'success');
                } else {
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur AJAX:", status, error);
                console.log("Texte de réponse:", xhr.responseText);
                Swal.fire('Erreur Système', 'Impossible de contacter l\'API', 'error');
            }
        });
    });
});

function loadCategories() {
    $.ajax({
        url: 'api/categories/read',
        type: 'GET',
        success: function(response) {
            if(response.status === 'success') {
                let html = '';
                response.data.forEach(function(cat) {
                    html += `
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="avatar avatar-lg rounded bg-${cat.color}-soft text-${cat.color}">
                                            <i class="ti ${cat.icon} fs-24"></i>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon border-0" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteCategory(${cat.category_id})">
                                                    <i class="ti ti-trash me-2"></i> Supprimer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mb-1">${cat.name}</h5>
                                    <p class="text-muted fs-12 uppercase mb-0">Code: ${cat.code}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#categoriesGrid').html(html);
            }
        }
    });
}

function deleteCategory(id) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cela ne supprimera pas les documents associés, mais ils perdront leur étiquette.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer !'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/categories/delete',
                type: 'POST',
                data: JSON.stringify({ category_id: id }),
                contentType: 'application/json',
                success: function(response) {
                    if(response.status === 'success') {
                        loadCategories();
                        Swal.fire('Supprimé', response.message, 'success');
                    }
                }
            });
        }
    });
}
</script>

<style>
.bg-primary-soft { background-color: #e8f0fe; color: #1a73e8; }
.bg-success-soft { background-color: #e6f4ea; color: #1e7e34; }
.bg-info-soft { background-color: #e2f2fd; color: #01579b; }
.bg-warning-soft { background-color: #fff4e5; color: #ffa000; }
.bg-danger-soft { background-color: #fce8e6; color: #d93025; }
.bg-secondary-soft { background-color: #f1f3f4; color: #5f6368; }
</style>
