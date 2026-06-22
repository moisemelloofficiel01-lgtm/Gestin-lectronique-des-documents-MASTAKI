<?php
require_once 'config/db.php';
include 'pages/header.php';

$document_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($document_id == 0) {
    echo "<div class='page-wrapper'><div class='content'><div class='alert alert-danger'>Document non spécifié.</div></div></div>";
    include 'pages/footer.php';
    exit();
}

// Fetch document details
$sql = "SELECT d.*, f.nom_fournisseur, f.ville as f_ville, f.pays as f_pays, f.email_general as f_email
        FROM documents d 
        LEFT JOIN fournisseurs f ON d.fournisseur_id = f.fournisseur_id 
        WHERE d.document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $document_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    echo "<div class='page-wrapper'><div class='content'><div class='alert alert-danger'>Document non trouvé.</div></div></div>";
    include 'pages/footer.php';
    exit();
}

$fileExt = strtolower($doc['extension_fichier']);
$filePath = "uploads/" . $doc['chemin_stockage'];
$isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$isPdf = ($fileExt === 'pdf');
?>

<div class="page-wrapper">
    <div class="content">
        <!-- Breadcrumb & Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="documents.php">Documents</a></li>
                        <li class="breadcrumb-item active">Détails du classement</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0">Classement : <?php echo htmlspecialchars($doc['numero_facture'] ?: $doc['nom_fichier_original']); ?></h4>
            </div>
            <div class="d-flex gap-2">
                <a href="#" onclick="openSecurePreview('<?php echo $filePath; ?>'); return false;" class="btn btn-white border shadow-sm">
                    <i class="ti ti-eye me-1"></i> Voir
                </a>
                <button class="btn btn-success shadow-sm" onclick="downloadDocument(<?php echo $document_id; ?>, '<?php echo $doc['statut']; ?>')">
                    <i class="ti ti-download me-1"></i> Télécharger
                </button>
                <button class="btn btn-primary shadow-sm" onclick="openEditModal(<?php echo $document_id; ?>)">
                    <i class="ti ti-pencil me-1"></i> Modifier
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Document Preview -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm overflow-hidden h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Aperçu du document</h6>
                    </div>
                    <div class="card-body p-0 bg-light d-flex align-items-center justify-content-center" style="min-height: 600px;">
                        <?php if ($isPdf): ?>
                            <iframe src="<?php echo $filePath; ?>#toolbar=0" width="100%" height="700px" style="border: none;"></iframe>
                        <?php elseif ($isImage): ?>
                            <img src="<?php echo $filePath; ?>" class="img-fluid shadow" style="max-height: 700px;">
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="ti ti-file-unknown fs-80 text-muted mb-3 d-block"></i>
                                <p class="text-muted">L'aperçu n'est pas disponible pour les fichiers <strong><?php echo strtoupper($fileExt); ?></strong>.</p>
                                <button class="btn btn-primary" onclick="downloadDocument(<?php echo $document_id; ?>, '<?php echo $doc['statut']; ?>')"><i class="ti ti-download me-1"></i>Télécharger pour consulter</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Classification Details -->
            <div class="col-lg-5">
                <!-- Status Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="text-muted fw-bold fs-12 uppercase tracking-wide">STATUT ACTUEL</span>
                            <?php
                                $badgeClass = 'bg-primary';
                                if ($doc['statut'] === 'VALIDE') $badgeClass = 'bg-success';
                                if ($doc['statut'] === 'EN_COURS') $badgeClass = 'bg-info';
                                if ($doc['statut'] === 'ARCHIVE') $badgeClass = 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?> px-3 py-2"><?php echo $doc['statut']; ?></span>
                        </div>
                        <div class="row g-4">
                            <div class="col-6">
                                <label class="text-muted fs-11 d-block mb-1">MONTANT TTC</label>
                                <h4 class="fw-bold mb-0"><?php echo number_format($doc['montant_ttc'], 2); ?> <?php echo $doc['devise']; ?></h4>
                            </div>
                            <div class="col-6">
                                <label class="text-muted fs-11 d-block mb-1">DATE FACTURE</label>
                                <h4 class="fw-bold mb-0"><?php echo $doc['date_facture'] ?: 'Non définie'; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- classification Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Classification Détaillée</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 py-3 border-0">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Fournisseur</div>
                                    <div class="col-7 fw-bold"><?php echo htmlspecialchars($doc['nom_fournisseur'] ?: 'DIVERS'); ?></div>
                                </div>
                            </div>
                            <div class="list-group-item px-0 py-3 border-top">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Type de document</div>
                                    <div class="col-7 fw-bold"><?php echo $doc['type_document']; ?></div>
                                </div>
                            </div>
                            <div class="list-group-item px-0 py-3 border-top">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Sous-type</div>
                                    <div class="col-7"><?php echo $doc['sous_type'] ?: '-'; ?></div>
                                </div>
                            </div>
                            <div class="list-group-item px-0 py-3 border-top">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Référence interne</div>
                                    <div class="col-7 fw-bold text-primary"><?php echo $doc['uuid_document']; ?></div>
                                </div>
                            </div>
                            <div class="list-group-item px-0 py-3 border-top">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Service demandeur</div>
                                    <div class="col-7"><?php echo $doc['service_demandeur'] ?: '-'; ?></div>
                                </div>
                            </div>
                            <div class="list-group-item px-0 py-3 border-top">
                                <div class="row">
                                    <div class="col-5 text-muted fs-13">Centre de coût</div>
                                    <div class="col-7"><?php echo $doc['centre_cout'] ?: '-'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="card border-0 shadow-sm mb-4 bg-light-soft">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Détails Financiers</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Montant Hors-Taxe</span>
                            <span class="fw-bold"><?php echo number_format($doc['montant_ht'], 2); ?> <?php echo $doc['devise']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">TVA (Auto)</span>
                            <span class="fw-bold"><?php echo number_format($doc['montant_tva'], 2); ?> <?php echo $doc['devise']; ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">NET À PAYER</span>
                            <span class="fw-bold fs-18 text-primary"><?php echo number_format($doc['montant_ttc'], 2); ?> <?php echo $doc['devise']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Supplier Quick Info -->
                <?php if ($doc['fournisseur_id']): ?>
                <div class="card border-0 shadow-sm border-start border-primary border-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Info Fournisseur</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md rounded bg-primary-soft me-3">
                                <i class="ti ti-building fs-20 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($doc['nom_fournisseur']); ?></h6>
                                <small class="text-muted"><?php echo $doc['f_ville']; ?>, <?php echo $doc['f_pays']; ?></small>
                            </div>
                        </div>
                        <div class="fs-13">
                            <div class="mb-1"><i class="ti ti-mail me-2"></i> <?php echo $doc['f_email']; ?></div>
                        </div>
                        <a href="fournisseurs.php" class="btn btn-xs btn-outline-primary mt-3 w-100">Voir la fiche fournisseur</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/document_modals.php'; ?>
<?php include 'pages/footer.php'; ?>

<script>
// Use the same loadDocuments/edit logic if needed
function loadDocuments() { /* Not needed here but prevents errors if modals call it */ }

function openSecurePreview(url) {
    var statut = '<?php echo $doc["statut"]; ?>';
    if (statut === 'ARCHIVE' || statut === 'ARCHIVAL') {
        requestPassword(function(verified) {
            if(verified) { window.open(url, '_blank'); }
        });
    } else {
        window.open(url, '_blank');
    }
}

function downloadDocument(docId, statut) {
    var downloadUrl = 'api/documents/download.php?id=' + docId;
    if (statut === 'ARCHIVE' || statut === 'ARCHIVAL') {
        requestPassword(function(verified) {
            if(verified) {
                window.location.href = downloadUrl;
            }
        });
    } else {
        window.location.href = downloadUrl;
    }
}
</script>

<style>
.bg-light-soft { background-color: #f8f9fa; }
.avatar-xxl { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
.uppercase { text-transform: uppercase; }
.tracking-wide { letter-spacing: 0.05em; }
</style>