<?php
require_once 'config/db.php';
include 'pages/header.php';
$userId = $_SESSION['user_id'];
?>

<div class="page-wrapper">
    <div class="content pb-0">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" id="driveBreadcrumb">
                        <li class="breadcrumb-item active">Mon Drive</li>
                    </ol>
                </nav>
                <p class="text-muted mb-0">Gérez vos fichiers et dossiers personnels</p>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <button class="btn btn-white border shadow-sm" onclick="$('#newFolderModal').modal('show')">
                    <i class="ti ti-folder-plus me-1"></i> Nouveau Dossier
                </button>
                <button class="btn btn-primary shadow-sm" onclick="$('#uploadFileModal').modal('show')">
                    <i class="ti ti-upload me-1"></i> Upload
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4" id="driveContent">
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-loader fs-40 mb-2 d-block"></i> Chargement...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Folder Modal -->
<div class="modal fade" id="newFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Nouveau Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="newFolderForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom du dossier</label>
                        <input type="text" class="form-control" name="nom" placeholder="Ex: Documents comptables" required autofocus>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="newFolderForm" class="btn btn-primary flex-fill">Créer</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Uploader un fichier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="uploadFileForm" enctype="multipart/form-data">
                    <input type="hidden" name="dossier_id" id="upload_folder_id" value="">
                    <div class="mb-3">
                        <div class="upload-zone p-4 border-dashed rounded text-center bg-light">
                            <i class="ti ti-cloud-upload fs-40 text-primary mb-2"></i>
                            <h6 class="fw-bold">Glisser-déposer ou cliquer</h6>
                            <input type="file" class="form-control" name="file" required>
                        </div>
                    </div>
                    <p class="text-muted fs-12 mb-0" id="uploadCurrentFolder">Dossier: Racine</p>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="uploadFileForm" class="btn btn-primary flex-fill">Uploader</button>
            </div>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Renommer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="renameFolderForm">
                    <input type="hidden" name="id" id="rename_id">
                    <input type="hidden" name="action" value="rename">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nouveau nom</label>
                        <input type="text" class="form-control" name="nom" id="rename_nom" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="renameFolderForm" class="btn btn-primary flex-fill">Renommer</button>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/footer.php'; ?>

<style>
.border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }
.upload-zone:hover { border-color: #1a73e8 !important; background-color: #f8f9fa !important; }
.drive-item { transition: background .15s ease; border-radius: 8px; }
.drive-item:hover { background: #f8f9fa; }
.drive-item .dropdown { opacity: 0; transition: opacity .15s; }
.drive-item:hover .dropdown { opacity: 1; }
</style>

<script>
let currentFolderId = null;

$(document).ready(function() {
    loadDrive();

    $('#newFolderForm').on('submit', function(e) {
        e.preventDefault();
        const nom = $(this).find('input[name="nom"]').val();
        console.log('[Drive] CREATE_FOLDER payload:', { nom, parent_id: currentFolderId });
        $.ajax({
            url: 'api/drive/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'create_folder', nom: nom, parent_id: currentFolderId }),
            success: function(response) {
                console.log('[Drive] CREATE_FOLDER response:', response);
                if (response.status === 'success') {
                    $('#newFolderModal').modal('hide');
                    $('#newFolderForm')[0].reset();
                    loadDrive();
                    Toast.fire({ icon: 'success', title: 'Dossier créé' });
                } else {
                    console.error('[Drive] CREATE_FOLDER error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Drive] CREATE_FOLDER ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });

    $('#uploadFileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('dossier_id', currentFolderId || '');
        console.log('[Drive] UPLOAD file:', formData.get('file').name, 'dossier:', currentFolderId);
        $.ajax({
            url: 'api/drive/upload',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('[Drive] UPLOAD response:', response);
                if (response.status === 'success') {
                    $('#uploadFileModal').modal('hide');
                    $('#uploadFileForm')[0].reset();
                    loadDrive();
                    Toast.fire({ icon: 'success', title: 'Fichier uploadé' });
                } else {
                    console.error('[Drive] UPLOAD error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Drive] UPLOAD ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });

    $('#renameFolderForm').on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());
        console.log('[Drive] RENAME payload:', data);
            $.ajax({
                url: 'api/drive/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    console.log('[Drive] RENAME response:', response);
                if (response.status === 'success') {
                    $('#renameFolderModal').modal('hide');
                    loadDrive();
                    Toast.fire({ icon: 'success', title: 'Renommé' });
                } else {
                    console.error('[Drive] RENAME error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Drive] RENAME ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });
});

function loadDrive() {
    const params = { action: 'list' };
    if (currentFolderId) params.parent_id = currentFolderId;
    console.log('[Drive] LIST params:', params);
    $.ajax({
        url: 'api/drive/',
        type: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            console.log('[Drive] LIST response:', response);
            if (response.status !== 'success') return;
            renderBreadcrumb(response.breadcrumb);
            renderDrive(response.folders, response.files);
        },
        error: function(xhr, status, err) {
            console.error('[Drive] LIST ajax error:', status, err, xhr.responseText);
            $('#driveContent').html('<div class="text-center py-5 text-danger"><i class="ti ti-alert-circle fs-40 d-block mb-2"></i><h5>Erreur de chargement</h5><p>Vérifie la console (F12) pour les détails</p></div>');
        }
    });
}

function renderBreadcrumb(breadcrumb) {
    const ol = $('#driveBreadcrumb');
    ol.empty();
    ol.append('<li class="breadcrumb-item"><a href="#" onclick="event.preventDefault(); event.stopPropagation(); navigateTo(null)">Mon Drive</a></li>');
    breadcrumb.forEach(function(item) {
        ol.append(`<li class="breadcrumb-item"><a href="#" onclick="event.preventDefault(); event.stopPropagation(); navigateTo(${item.dossier_id})">${item.nom}</a></li>`);
    });
}

function renderDrive(folders, files) {
    const container = $('#driveContent');
    let html = '';

    if (folders.length === 0 && files.length === 0) {
        html = `<div class="text-center py-5 text-muted">
            <i class="ti ti-folder-open fs-60 mb-3 d-block"></i>
            <h5>Ce dossier est vide</h5>
            <p class="mb-3">Créez un dossier ou uploadez un fichier</p>
            <button class="btn btn-primary" onclick="$('#uploadFileModal').modal('show')"><i class="ti ti-upload me-1"></i> Uploader</button>
        </div>`;
        container.html(html);
        return;
    }

    // Folders
    html += '<div class="row">';
    folders.forEach(function(f) {
        const isShared = f.partage == 1 ? '<span class="badge bg-info-soft ms-1"><i class="ti ti-users"></i></span>' : '';
        html += `
            <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-3">
                <div class="drive-item p-3 border" ondblclick="navigateTo(${f.dossier_id})">
                    <div class="text-center mb-2 position-relative">
                        <i class="ti ti-folder text-warning fs-40"></i>
                        <div class="dropdown position-absolute top-0 end-0">
                            <button class="btn btn-sm btn-icon btn-white border shadow-none" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); event.stopPropagation(); renameFolder(${f.dossier_id}, '${f.nom}')"><i class="ti ti-pencil me-2"></i>Renommer</a>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); event.stopPropagation(); deleteFolder(${f.dossier_id})"><i class="ti ti-trash me-2"></i>Supprimer</a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 text-truncate fw-semibold fs-13" title="${f.nom}">${f.nom} ${isShared}</p>
                        <small class="text-muted">${parseInt(f.nb_sous_dossiers) + parseInt(f.nb_fichiers)} élément(s)</small>
                    </div>
                </div>
            </div>`;
    });

    // Files
    files.forEach(function(f) {
        const icon = getFileIcon(f.extension);
        const size = f.taille ? formatFileSize(f.taille) : '';
        html += `
            <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-3">
                <div class="drive-item p-3 border">
                    <div class="text-center mb-2 position-relative">
                        <i class="ti ${icon} fs-40"></i>
                        <div class="dropdown position-absolute top-0 end-0">
                            <button class="btn btn-sm btn-icon btn-white border shadow-none" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="${f.chemin_stockage}" target="_blank"><i class="ti ti-eye me-2"></i>Voir</a>
                                <a class="dropdown-item" href="${f.chemin_stockage}" download="${f.nom_fichier}"><i class="ti ti-download me-2"></i>Télécharger</a>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteFile(${f.document_id})"><i class="ti ti-trash me-2"></i>Supprimer</a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 text-truncate fw-semibold fs-13" title="${f.fichier_original}">${f.fichier_original}</p>
                        <small class="text-muted">${size}</small>
                    </div>
                </div>
            </div>`;
    });
    html += '</div>';
    container.html(html);
}

function navigateTo(folderId) {
    currentFolderId = folderId;
    $('#upload_folder_id').val(folderId || '');
    $('#uploadCurrentFolder').text('Dossier: ' + (folderId ? 'Sous-dossier' : 'Racine'));
    loadDrive();
}

function getFileIcon(ext) {
    const icons = { pdf: 'ti-file-type-pdf text-danger', doc: 'ti-file-type-doc text-primary', docx: 'ti-file-type-doc text-primary', xls: 'ti-file-type-xls text-success', xlsx: 'ti-file-type-xls text-success', jpg: 'ti-file-type-jpg text-info', jpeg: 'ti-file-type-jpg text-info', png: 'ti-file-type-jpg text-info', gif: 'ti-file-type-jpg text-info', webp: 'ti-file-type-jpg text-info', txt: 'ti-file-text text-secondary', zip: 'ti-file-zip text-warning', rar: 'ti-file-zip text-warning' };
    return icons[ext] || 'ti-file text-muted';
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    const units = ['o', 'Ko', 'Mo', 'Go'];
    let i = 0;
    let size = bytes;
    while (size >= 1024 && i < units.length - 1) { size /= 1024; i++; }
    return size.toFixed(1) + ' ' + units[i];
}

function renameFolder(id, nom) {
    $('#rename_id').val(id);
    $('#rename_nom').val(nom);
    $('#renameFolderModal').modal('show');
}

function deleteFolder(id) {
    Swal.fire({ title: 'Supprimer ce dossier ?', text: 'Tous les sous-dossiers et fichiers seront supprimés', icon: 'warning', showCancelButton: true, confirmButtonText: 'Supprimer' }).then((r) => {
        if (r.isConfirmed) {
            console.log('[Drive] DELETE_FOLDER id:', id);
            $.ajax({
                url: 'api/drive/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'delete_folder', id: id }),
                success: function(response) {
                    console.log('[Drive] DELETE_FOLDER response:', response);
                    if (response.status === 'success') { loadDrive(); Toast.fire({ icon: 'success', title: 'Supprimé' }); }
                    else { console.error('[Drive] DELETE_FOLDER error:', response.message); Swal.fire('Erreur', response.message, 'error'); }
                },
                error: function(xhr, status, err) {
                    console.error('[Drive] DELETE_FOLDER ajax error:', status, err, xhr.responseText);
                    Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
                }
            });
        }
    });
}

function deleteFile(id) {
    Swal.fire({ title: 'Supprimer ce fichier ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Supprimer' }).then((r) => {
        if (r.isConfirmed) {
            console.log('[Drive] DELETE_FILE id:', id);
            $.ajax({
                url: 'api/drive/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'delete_file', id: id }),
                success: function(response) {
                    console.log('[Drive] DELETE_FILE response:', response);
                    if (response.status === 'success') { loadDrive(); Toast.fire({ icon: 'success', title: 'Supprimé' }); }
                    else { console.error('[Drive] DELETE_FILE error:', response.message); Swal.fire('Erreur', response.message, 'error'); }
                },
                error: function(xhr, status, err) {
                    console.error('[Drive] DELETE_FILE ajax error:', status, err, xhr.responseText);
                    Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
                }
            });
        }
    });
}
</script>
