<!-- Add Modal -->
<div class="modal fade" id="addFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ajouter un Fournisseur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addFournisseurForm">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nom du Fournisseur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_fournisseur" required placeholder="Ex: Ets Global Services">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Logo</label>
                            <input type="file" class="form-control" name="logo" id="add_logo_input" accept="image/*">
                            <div class="mt-2 text-center">
                                <img id="add_logo_preview" src="" style="max-width: 100px; max-height: 100px; display: none; border: 1px solid #ddd; padding: 2px;" class="rounded shadow-sm">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <select class="form-select" name="categorie_fournisseur">
                                <option value="MATIERES_PREMIERES">Matières Premières</option>
                                <option value="SERVICES" selected>Services</option>
                                <option value="SOUS_TRAITANCE">Sous-traitance</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <input type="text" class="form-control" name="adresse">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ville</label>
                            <input type="text" class="form-control" name="ville">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Pays</label>
                            <input type="text" class="form-control" name="pays" value="RDC">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="text" class="form-control" name="telephone_principal">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" name="email_general">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border shadow-none" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="addFournisseurForm" class="btn btn-primary px-4 fw-bold">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xxl rounded-circle border p-1 bg-white shadow-sm mb-3 mx-auto" style="width: 120px; height: 120px;">
                        <img id="view_logo" src="" class="rounded-circle img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h4 id="view_nom_fournisseur" class="fw-bold mb-1"></h4>
                    <span id="view_statut" class="badge"></span>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-category text-primary me-2"></i>
                            <span class="text-muted fs-12 uppercase tracking-wide fw-bold">CATÉGORIE</span>
                        </div>
                        <p id="view_categorie_fournisseur" class="fw-bold mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-map-pin text-primary me-2"></i>
                            <span class="text-muted fs-12 uppercase tracking-wide fw-bold">LOCALISATION</span>
                        </div>
                        <p id="view_localisation" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-phone text-primary me-2"></i>
                            <span class="text-muted fs-12 uppercase tracking-wide fw-bold">TÉLÉPHONE</span>
                        </div>
                        <p id="view_telephone_principal" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-mail text-primary me-2"></i>
                            <span class="text-muted fs-12 uppercase tracking-wide fw-bold">EMAIL</span>
                        </div>
                        <p id="view_email_general" class="mb-0"></p>
                    </div>
                    <div class="col-md-12">
                        <div class="bg-light rounded p-3">
                            <span class="text-muted fs-12 fw-bold d-block mb-1">ADRESSE COMPLÈTE</span>
                            <p id="view_adresse" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light w-100 py-2" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Modifier le Fournisseur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editFournisseurForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="fournisseur_id" id="edit_fournisseur_id">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nom du Fournisseur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_fournisseur" id="edit_nom_fournisseur" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Logo</label>
                            <input type="file" class="form-control" name="logo" id="edit_logo_input" accept="image/*">
                            <div class="mt-2 d-flex align-items-center gap-3">
                                <div>
                                    <small class="d-block text-muted text-center">Actuel</small>
                                    <img id="edit_logo_preview_old" src="" style="max-width: 60px; max-height: 60px;" class="rounded border">
                                </div>
                                <div>
                                    <small class="d-block text-muted text-center">Nouveau</small>
                                    <img id="edit_logo_preview_new" src="" style="max-width: 60px; max-height: 60px; display: none;" class="rounded border border-primary">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <select class="form-select" name="categorie_fournisseur" id="edit_categorie_fournisseur">
                                <option value="MATIERES_PREMIERES">Matières Premières</option>
                                <option value="SERVICES">Services</option>
                                <option value="SOUS_TRAITANCE">Sous-traitance</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <input type="text" class="form-control" name="adresse" id="edit_adresse">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ville</label>
                            <input type="text" class="form-control" name="ville" id="edit_ville">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fournisseur Pays</label>
                            <input type="text" class="form-control" name="pays" id="edit_pays">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <select class="form-select" name="statut" id="edit_statut">
                                <option value="ACTIF">Actif</option>
                                <option value="INACTIF">Inactif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-white border shadow-none" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="editFournisseurForm" class="btn btn-primary px-4 fw-bold">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="ti ti-alert-triangle text-danger fs-60"></i>
                </div>
                <h5 class="fw-bold">Supprimer le Fournisseur ?</h5>
                <p class="text-muted">Cette action est irréversible et supprimera toutes les données associées.</p>
                <input type="hidden" id="delete_fournisseur_id">
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger flex-fill" id="confirmDeleteBtn">Oui, Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>
