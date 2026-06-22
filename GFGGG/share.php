<?php
require_once 'config/db.php';
include 'pages/header.php';
$userId = $_SESSION['user_id'];
?>

<div class="page-wrapper">
    <div class="content pb-0">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Documents Partagés</h4>
                <p class="text-muted mb-0">Partagez des documents et catégories avec d'autres utilisateurs</p>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <button class="btn btn-primary shadow-sm" onclick="$('#shareDocumentModal').modal('show')">
                    <i class="ti ti-share me-1"></i> Partager un Document
                </button>
                <button class="btn btn-info shadow-sm text-white" onclick="$('#shareCategoryModal').modal('show')">
                    <i class="ti ti-category me-1"></i> Partager une Catégorie
                </button>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" id="shareTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="with-me-tab" data-bs-toggle="tab" data-bs-target="#withMe" type="button">Partagés avec moi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="by-me-tab" data-bs-toggle="tab" data-bs-target="#byMe" type="button">Mes partages</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="withMe">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0" id="sharedWithMeContent">
                        <div class="text-center py-5 text-muted"><i class="ti ti-loader fs-40 mb-2 d-block"></i>Chargement...</div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="byMe">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0" id="sharedByMeContent">
                        <div class="text-center py-5 text-muted"><i class="ti ti-loader fs-40 mb-2 d-block"></i>Chargement...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Document Modal -->
<div class="modal fade" id="shareDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Partager un Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="shareDocumentForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Document</label>
                        <select class="form-select" name="document_id" id="share_doc_select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Partager avec</label>
                        <select class="form-select" name="shared_with" id="share_user_select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Permission</label>
                        <select class="form-select" name="permission">
                            <option value="view">Consultation</option>
                            <option value="download">Téléchargement</option>
                            <option value="edit">Modification</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="shareDocumentForm" class="btn btn-primary px-4 fw-bold">Partager</button>
            </div>
        </div>
    </div>
</div>

<!-- Share Category Modal -->
<div class="modal fade" id="shareCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Partager une Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="shareCategoryForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catégorie</label>
                        <select class="form-select" name="category_id" id="share_cat_select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Partager avec</label>
                        <select class="form-select" name="shared_with" id="share_user_select2" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Permission</label>
                        <select class="form-select" name="permission">
                            <option value="view">Consultation</option>
                            <option value="upload">Upload</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="shareCategoryForm" class="btn btn-info px-4 fw-bold text-white">Partager</button>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/footer.php'; ?>

<script>
$(document).ready(function() {
    loadShareData();
    loadUsersAndDocs();

    $('#shareDocumentForm').on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());
        data.action = 'share_document';
        console.log('[Share] DOCUMENT payload:', data);
        $.ajax({
            url: 'api/share/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log('[Share] DOCUMENT response:', response);
                if (response.status === 'success') {
                    $('#shareDocumentModal').modal('hide');
                    loadShareData();
                    Toast.fire({ icon: 'success', title: 'Document partagé' });
                } else {
                    console.error('[Share] DOCUMENT error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Share] DOCUMENT ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });

    $('#shareCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());
        data.action = 'share_category';
        console.log('[Share] CATEGORY payload:', data);
        $.ajax({
            url: 'api/share/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log('[Share] CATEGORY response:', response);
                if (response.status === 'success') {
                    $('#shareCategoryModal').modal('hide');
                    loadShareData();
                    Toast.fire({ icon: 'success', title: 'Catégorie partagée' });
                } else {
                    console.error('[Share] CATEGORY error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Share] CATEGORY ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });
});

function loadShareData() {
    console.log('[Share] LIST loading...');
    $.ajax({
        url: 'api/share/?action=list',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[Share] LIST response:', response);

            // Shared with me
            const wm = $('#sharedWithMeContent');
            wm.empty();
            if (response.shared_with_me.length === 0) {
                wm.html('<div class="text-center py-5 text-muted"><i class="ti ti-share-off fs-60 d-block mb-3"></i><h5>Aucun document partagé avec vous</h5></div>');
            } else {
                let html = '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="bg-light"><tr><th class="ps-3">Document</th><th>Partagé par</th><th>Permission</th><th>Date</th><th class="text-center">Action</th></tr></thead><tbody>';
                response.shared_with_me.forEach(function(s) {
                    html += `<tr>
                        <td class="ps-3"><i class="ti ti-file-text me-2 text-primary"></i>${s.nom_fichier_original}</td>
                        <td>${s.prenom} ${s.nom}</td>
                        <td><span class="badge bg-info">${s.permission}</span></td>
                        <td>${s.created_at ? s.created_at.split(' ')[0] : '-'}</td>
                        <td class="text-center">
                            ${s.permission !== 'view' ? `<a href="${s.chemin_stockage}" class="btn btn-sm btn-icon btn-white border" download><i class="ti ti-download"></i></a>` : ''}
                            <a href="document.php?id=${s.document_id}" class="btn btn-sm btn-icon btn-white border"><i class="ti ti-eye"></i></a>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                wm.html(html);
            }

            // Shared by me
            const bm = $('#sharedByMeContent');
            bm.empty();
            if (response.shared_by_me.length === 0) {
                bm.html('<div class="text-center py-5 text-muted"><i class="ti ti-share fs-60 d-block mb-3"></i><h5>Vous n\'avez partagé aucun document</h5></div>');
            } else {
                let html = '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="bg-light"><tr><th class="ps-3">Document</th><th>Partagé avec</th><th>Permission</th><th>Date</th><th class="text-center">Action</th></tr></thead><tbody>';
                response.shared_by_me.forEach(function(s) {
                    html += `<tr>
                        <td class="ps-3"><i class="ti ti-file-text me-2 text-primary"></i>${s.nom_fichier_original}</td>
                        <td>${s.prenom} ${s.nom} (${s.shared_with_email})</td>
                        <td><span class="badge bg-info">${s.permission}</span></td>
                        <td>${s.created_at ? s.created_at.split(' ')[0] : '-'}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-icon btn-white border text-danger" onclick="deleteShare(${s.partage_id})"><i class="ti ti-trash"></i></button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                bm.html(html);
            }
        }
    });
}

function loadUsersAndDocs() {
    console.log('[Share] Loading users, documents, categories...');
    $.ajax({ url: 'api/share/?action=users', type: 'GET', dataType: 'json',
        success: function(response) {
            console.log('[Share] USERS response:', response);
            if (response.status === 'success') {
                let opts = '<option value="">Sélectionner un utilisateur</option>';
                response.data.forEach(function(u) { opts += `<option value="${u.utilisateur_id}">${u.nom} ${u.prenom} (${u.email})</option>`; });
                $('#share_user_select, #share_user_select2').html(opts);
            }
        },
        error: function(xhr, status, err) { console.error('[Share] USERS ajax error:', status, err); }
    });
    $.ajax({ url: 'api/share/?action=documents', type: 'GET', dataType: 'json',
        success: function(response) {
            console.log('[Share] DOCUMENTS response:', response);
            if (response.status === 'success') {
                let opts = '<option value="">Sélectionner un document</option>';
                response.data.forEach(function(d) { opts += `<option value="${d.document_id}">${d.nom_fichier_original}</option>`; });
                $('#share_doc_select').html(opts);
            }
        },
        error: function(xhr, status, err) { console.error('[Share] DOCUMENTS ajax error:', status, err); }
    });
    $.ajax({ url: 'api/share/?action=categories', type: 'GET', dataType: 'json',
        success: function(response) {
            console.log('[Share] CATEGORIES response:', response);
            if (response.status === 'success') {
                let opts = '<option value="">Sélectionner une catégorie</option>';
                response.data.forEach(function(c) { opts += `<option value="${c.category_id}">${c.name}</option>`; });
                $('#share_cat_select').html(opts);
            }
        },
        error: function(xhr, status, err) { console.error('[Share] CATEGORIES ajax error:', status, err); }
    });
}

function deleteShare(id) {
    Swal.fire({ title: 'Supprimer ce partage ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Oui' }).then((r) => {
        if (r.isConfirmed) {
            console.log('[Share] DELETE id:', id);
            $.ajax({
                url: 'api/share/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'delete', id: id }),
                success: function(response) {
                    console.log('[Share] DELETE response:', response);
                    if (response.status === 'success') { loadShareData(); Toast.fire({ icon: 'success', title: 'Partage supprimé' }); }
                    else { console.error('[Share] DELETE error:', response.message); Swal.fire('Erreur', response.message, 'error'); }
                },
                error: function(xhr, status, err) {
                    console.error('[Share] DELETE ajax error:', status, err, xhr.responseText);
                    Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
                }
            });
        }
    });
}
</script>
