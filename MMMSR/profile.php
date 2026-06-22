<?php
require_once 'config/db.php';
include 'pages/header.php';

// Get current user data
$userId = $_SESSION['user_id'];
$sql = "SELECT nom, prenom, email, roles FROM utilisateurs WHERE utilisateur_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header d-flex align-items-center justify-content-between mb-4">
                    <h4 class="page-title">Mon Profil</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 col-lg-5">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="avatar avatar-xl rounded-circle bg-light mx-auto mb-3">
                            <span class="fs-24 fw-bold text-primary"><?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?></span>
                        </div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-primary-soft"><?php echo implode(', ', json_decode($user['roles'], true)); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Informations Personnelles</h6>
                    </div>
                    <div class="card-body">
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" class="form-control" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Sécurité</h6>
                    </div>
                    <div class="card-body">
                        <form id="passwordForm">
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="ti ti-eye"></i></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="ti ti-eye"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)"><i class="ti ti-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-warning text-white">Changer le mot de passe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'pages/footer.php'; ?>

<script>
function togglePassword(btn) {
    const input = $(btn).prev('input');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        $(btn).find('i').removeClass('ti-eye').addClass('ti-eye-off');
    } else {
        input.attr('type', 'password');
        $(btn).find('i').removeClass('ti-eye-off').addClass('ti-eye');
    }
}

$(document).ready(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        requestPassword(function(verified) {
            if(verified) {
                $.ajax({
                    url: 'api/users/update_profile.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.status === 'success') {
                            Swal.fire('Succès', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: 'api/users/update_password.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire('Succès', response.message, 'success');
                    $('#passwordForm')[0].reset();
                } else {
                    Swal.fire('Erreur', response.message, 'error');
                }
            }
        });
    });
});
</script>
