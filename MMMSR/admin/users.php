<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../pages/header.php';

$roles = json_decode($_SESSION['roles'] ?? '[]', true);
if (!in_array('SUPERADMIN', $roles)) {
    header("Location: " . getBasePath() . "index.php");
    exit;
}
?>

<div class="page-wrapper">
    <div class="content pb-0">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Gestion des Utilisateurs</h4>
                <p class="text-muted mb-0">Administration des comptes utilisateurs</p>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <button class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="ti ti-user-plus me-1"></i> Nouvel Utilisateur
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 ms-auto">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="searchUser" class="form-control border-start-0" placeholder="Rechercher (nom, email, matricule...)">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Matricule</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Fonction</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr><td colspan="8" class="text-center py-4 text-muted">Chargement...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="usersPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouvel Utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Mot de passe</label>
                            <input type="text" class="form-control" name="password" value="Admin123!">
                            <small class="text-muted">Par défaut: Admin123!</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Rôle</label>
                            <select class="form-select" name="role">
                                <option value="USER">Utilisateur</option>
                                <option value="ADMIN">Administrateur</option>
                                <option value="SUPERADMIN">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fonction</label>
                            <input type="text" class="form-control" name="fonction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="text" class="form-control" name="telephone">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="addUserForm" class="btn btn-primary px-4 fw-bold">Créer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Modifier Utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editUserForm">
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" class="form-control" name="nom" id="edit_nom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" class="form-control" name="prenom" id="edit_prenom" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Rôle</label>
                            <select class="form-select" name="role" id="edit_role">
                                <option value="USER">Utilisateur</option>
                                <option value="ADMIN">Administrateur</option>
                                <option value="SUPERADMIN">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fonction</label>
                            <input type="text" class="form-control" name="fonction" id="edit_fonction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" id="edit_telephone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <select class="form-select" name="actif" id="edit_actif">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="submitEditUser" class="btn btn-info px-4 fw-bold text-white">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../pages/footer.php'; ?>

<script>
let currentPage = 1;

$(document).ready(function() {
    loadUsers();
    $('#searchUser').on('keyup', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => { currentPage = 1; loadUsers(); }, 500);
    });
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());
        console.log('[Users] CREATE payload:', data);
        $.ajax({
            url: 'api/users/create',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log('[Users] CREATE response:', response);
                if (response.status === 'success') {
                    $('#addUserModal').modal('hide');
                    $('#addUserForm')[0].reset();
                    loadUsers();
                    Toast.fire({ icon: 'success', title: 'Utilisateur créé' });
                } else {
                    console.error('[Users] CREATE error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Users] CREATE ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });
    $('#submitEditUser').on('click', function() {
        const data = Object.fromEntries(new FormData($('#editUserForm')[0]).entries());
        console.log('[Users] UPDATE payload:', data);
        $.ajax({
            url: 'api/users/update',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log('[Users] UPDATE response:', response);
                if (response.status === 'success') {
                    $('#editUserModal').modal('hide');
                    loadUsers();
                    Toast.fire({ icon: 'success', title: 'Utilisateur mis à jour' });
                } else {
                    console.error('[Users] UPDATE error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[Users] UPDATE ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });
});

function loadUsers() {
    const search = $('#searchUser').val();
    console.log('[Users] READ params:', { page: currentPage, limit: 20, search: search, url: 'api/users/read' });
    $.ajax({
        url: 'api/users/read',
        type: 'GET',
        data: { page: currentPage, limit: 20, search: search },
        dataType: 'json',
        success: function(response) {
            console.log('[Users] READ response:', response);
            if (response.status !== 'success') return;
            const tbody = $('#usersTableBody');
            tbody.empty();
            if (response.data.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center py-4 text-muted">Aucun utilisateur</td></tr>');
            } else {
                response.data.forEach(function(u) {
                    const roleBadge = u.roles.includes('SUPERADMIN') ? 'bg-danger' : u.roles.includes('ADMIN') ? 'bg-warning' : 'bg-primary';
                    const statusBadge = u.actif == 1 ? 'bg-success' : 'bg-secondary';
                    tbody.append(`
                        <tr>
                            <td class="ps-3"><span class="fw-semibold">${u.matricule}</span></td>
                            <td>${u.nom} ${u.prenom}</td>
                            <td>${u.email}</td>
                            <td>${u.fonction || '-'}</td>
                            <td><span class="badge ${roleBadge}">${u.roles.join(', ')}</span></td>
                            <td><span class="badge ${statusBadge}">${u.actif == 1 ? 'Actif' : 'Inactif'}</span></td>
                            <td>${u.date_creation ? u.date_creation.split(' ')[0] : '-'}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-white border shadow-none" onclick="editUser(${u.utilisateur_id}, '${u.nom}', '${u.prenom}', '${u.email}', '${u.fonction || ''}', '${u.telephone || ''}', '${u.roles[0]}', ${u.actif})">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-white border shadow-none text-danger" onclick="deleteUser(${u.utilisateur_id})">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
            const pagination = $('#usersPagination');
            pagination.empty();
            if (response.total_pages > 1) {
                if (currentPage > 1) pagination.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage - 1})">Précédent</a></li>`);
                for (let i = 1; i <= response.total_pages; i++) {
                    pagination.append(`<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${i})">${i}</a></li>`);
                }
                if (currentPage < response.total_pages) pagination.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage + 1})">Suivant</a></li>`);
            }
        }
    });
}

function changePage(page) { currentPage = page; loadUsers(); }

function editUser(id, nom, prenom, email, fonction, telephone, role, actif) {
    $('#edit_user_id').val(id);
    $('#edit_nom').val(nom);
    $('#edit_prenom').val(prenom);
    $('#edit_email').val(email);
    $('#edit_fonction').val(fonction);
    $('#edit_telephone').val(telephone);
    $('#edit_role').val(role);
    $('#edit_actif').val(actif);
    $('#editUserModal').modal('show');
}

function deleteUser(id) {
    Swal.fire({
        title: 'Supprimer ?',
        text: "Cette action est irréversible",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('[Users] DELETE id:', id);
            $.ajax({
                url: 'api/users/delete',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function(response) {
                    console.log('[Users] DELETE response:', response);
                    if (response.status === 'success') {
                        loadUsers();
                        Toast.fire({ icon: 'success', title: 'Utilisateur supprimé' });
                    } else {
                        console.error('[Users] DELETE error:', response.message);
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: function(xhr, status, err) {
                    console.error('[Users] DELETE ajax error:', status, err, xhr.responseText);
                    Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
                }
            });
        }
    });
}
</script>
