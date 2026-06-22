<!-- Add Modal -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouveau Classement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addDocumentForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="upload-zone p-4 border-dashed rounded text-center bg-light">
                                <i class="ti ti-cloud-upload fs-40 text-primary mb-2"></i>
                                <h6 class="fw-bold">Glisser-déposer ou cliquer pour uploader</h6>
                                <p class="text-muted fs-12">Formats supportés: PDF, JPG, PNG, XLS (Max 10MB)</p>
                                <input type="file" class="form-control" name="document" required id="fileInput">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Type de Document <span class="text-danger">*</span></label>
                            <select class="form-select border-primary-soft" name="type_document" required>
                                <option value="FACTURE">Facture</option>
                                <option value="BON_COMMANDE">Bon de Commande</option>
                                <option value="CONTRAT">Contrat</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fournisseur</label>
                            <select class="form-select" name="fournisseur_id" id="add_fournisseur_select">
                                <option value="">Chargement...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">N° Facture / Réf</label>
                            <input type="text" class="form-control" name="numero_facture" placeholder="Ex: FAC-2023-001" id="add_numero_facture">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date du Document <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_facture" id="add_date_facture" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 mb-3 montant-group" data-categories="FACTURE,DEVIS,BON_COMMANDE,BON_LIVRAISON">
                            <label class="form-label fw-bold">Montant HT</label>
                            <input type="number" step="0.01" class="form-control" name="montant_ht" id="add_montant_ht">
                        </div>
                        <div class="col-md-4 mb-3 montant-group" data-categories="FACTURE,DEVIS,BON_COMMANDE,BON_LIVRAISON">
                            <label class="form-label fw-bold">Montant TTC</label>
                            <input type="number" step="0.01" class="form-control" name="montant_ttc" id="add_montant_ttc">
                        </div>
                        <div class="col-md-4 mb-3 montant-group" data-categories="FACTURE,DEVIS,BON_COMMANDE,BON_LIVRAISON">
                            <label class="form-label fw-bold">Devise</label>
                            <select class="form-select" name="devise" id="add_devise">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="CDF">CDF</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 montant-group" data-categories="FACTURE,DEVIS,BON_COMMANDE,BON_LIVRAISON">
                            <label class="form-label fw-bold">Date d'émission</label>
                            <input type="date" class="form-control" name="date_echeance" id="add_date_echeance" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="addDocumentForm" class="btn btn-primary px-4 fw-bold">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Modifier le Classement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editDocumentForm">
                    <input type="hidden" name="document_id" id="edit_document_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Type de Document</label>
                            <select class="form-select" name="type_document" id="edit_type">
                                <option value="FACTURE">Facture</option>
                                <option value="BON_COMMANDE">Bon de Commande</option>
                                <option value="CONTRAT">Contrat</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fournisseur</label>
                            <select class="form-select" name="fournisseur_id" id="edit_fournisseur_select">
                                <option value="">Chargement...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">N° Facture / Réf</label>
                            <input type="text" class="form-control" name="numero_facture" id="edit_facture">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date du Document</label>
                            <input type="date" class="form-control" name="date_facture" id="edit_date" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">HT</label>
                            <input type="number" step="0.01" class="form-control" name="montant_ht" id="edit_ht">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">TVA</label>
                            <input type="number" step="0.01" class="form-control" name="montant_tva" id="edit_tva">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">TTC</label>
                            <input type="number" step="0.01" class="form-control" name="montant_ttc" id="edit_ttc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <select class="form-select" name="statut" id="edit_statut">
                                <option value="NOUVEAU">Nouveau</option>
                                <option value="EN_COURS">En cours</option>
                                <option value="VALIDE">Validé</option>
                                <option value="PAYE">Payé</option>
                                <option value="ARCHIVE">Archivé</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="submitEditBtn" class="btn btn-info px-4 fw-bold text-white">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Archiver le Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="ti ti-archive fs-60 text-warning mb-3"></i>
                <p>Souhaitez-vous déplacer ce document vers les archives ?</p>
                <input type="hidden" id="archive_document_id">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="confirmArchiveBtn" class="btn btn-warning flex-fill text-white fw-bold">Archiver</button>
            </div>
        </div>
    </div>
</div>

<!-- Share Document Modal -->
<div class="modal fade" id="shareDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Partager le Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="shareDocumentForm">
                    <input type="hidden" name="document_id" id="share_doc_id">
                    <input type="hidden" name="action" value="share_document">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Partager avec</label>
                        <div class="multi-select-dropdown" id="msDocShareModal">
                            <input type="hidden" name="shared_with" class="ms-values" value="[]">
                            <button type="button" class="btn btn-white border w-100 text-start d-flex justify-content-between align-items-center dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="text-muted ms-label">Sélectionner des utilisateurs</span>
                                <span class="badge bg-primary ms-count rounded-pill d-none">0</span>
                            </button>
                            <div class="dropdown-menu p-2 ms-options w-100" style="max-height:250px;overflow-y:auto">
                                <div class="text-center text-muted py-3"><i class="ti ti-loader spinner"></i> Chargement...</div>
                            </div>
                        </div>
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
                <button type="submit" form="shareDocumentForm" class="btn btn-success px-4 fw-bold">Partager</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="ti ti-alert-circle text-danger fs-60"></i>
                </div>
                <h5 class="fw-bold">Supprimer le document ?</h5>
                <p class="text-muted">Le fichier sera définitivement supprimé du serveur.</p>
                <input type="hidden" id="delete_document_id">
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger flex-fill fw-bold" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }
.upload-zone:hover { border-color: #1a73e8 !important; background-color: #f8f9fa !important; }
</style>

<script>
// Document Form Submits
$(document).ready(function() {
    $('#addDocumentForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'api/documents/create',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#addDocumentModal').modal('hide');
                    $('#addDocumentForm')[0].reset();
                    loadDocuments();
                    Swal.fire({ icon: 'success', title: 'Succès', text: 'Document classé avec succès' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: response.message });
                }
            }
        });
    });

    $('#submitEditBtn').on('click', function() {
        const formData = $('#editDocumentForm').serialize();
        $.ajax({
            url: 'api/documents/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editDocumentModal').modal('hide');
                    loadDocuments();
                    Swal.fire({ icon: 'success', title: 'Succès', text: 'Mise à jour réussie' });
                }
            }
        });
    });

    $('#confirmArchiveBtn').on('click', function() {
        const id = $('#archive_document_id').val();
        $.ajax({
            url: 'api/documents/archive',
            type: 'POST',
            data: { document_id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#archiveDocumentModal').modal('hide');
                    loadDocuments();
                    Swal.fire({ icon: 'success', title: 'Archivé', text: 'Document déplacé dans les archives' });
                }
            }
        });
    });

    $('#confirmDeleteBtn').on('click', function() {
        const id = $('#delete_document_id').val();
        $.ajax({
            url: 'api/documents/delete',
            type: 'POST',
            data: { document_id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteDocumentModal').modal('hide');
                    loadDocuments();
                    Swal.fire({ icon: 'success', title: 'Supprimé', text: 'Le document a été retiré' });
                }
            }
        });
    });

    // Share Document
    $('#shareDocumentForm').on('submit', function(e) {
        e.preventDefault();
        const data = {
            action: 'share_document',
            document_id: $('#share_doc_id').val(),
            shared_with: getMultiSelectValues('msDocShareModal'),
            permission: $(this).find('select[name="permission"]').val()
        };
        console.log('[DocModal] SHARE payload:', data);
        $.ajax({
            url: 'api/share/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                console.log('[DocModal] SHARE response:', response);
                if (response.status === 'success') {
                    $('#shareDocumentModal').modal('hide');
                    Toast.fire({ icon: 'success', title: 'Document partagé' });
                } else {
                    console.error('[DocModal] SHARE error:', response.message);
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('[DocModal] SHARE ajax error:', status, err, xhr.responseText);
                Swal.fire('Erreur', 'Requête échouée: ' + xhr.status, 'error');
            }
        });
    });
});

function shareDocument(id) {
    console.log('[DocModal] opening share modal for doc id:', id);
    $('#share_doc_id').val(id);
    initMultiSelect('msDocShareModal', 'api/share/?action=users');
    $('#shareDocumentModal').modal('show');
}

// Load Categories for selects
function loadCategoriesForSelects() {
    $.ajax({
        url: 'api/categories/read',
        type: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Choisir un type...</option>';
                response.data.forEach(function(cat) {
                    options += `<option value="${cat.code}">${cat.name}</option>`;
                });
                $('select[name="type_document"]').html(options);
            }
        }
    });
}

loadCategoriesForSelects();

$(document).on('change', 'select[name="type_document"]', function() {
    const val = $(this).val();
    $('.montant-group').each(function() {
        const cats = $(this).data('categories');
        if (cats && cats.split(',').includes(val)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});
</script>
