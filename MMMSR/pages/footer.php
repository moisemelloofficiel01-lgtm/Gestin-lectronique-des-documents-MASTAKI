
    <!-- Global Password Verification Modal -->
    <div class="modal fade" id="passwordVerifyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Sécurité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2 text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-lg bg-danger-soft rounded-circle mx-auto mb-2 text-danger">
                            <i class="ti ti-lock fs-24"></i>
                        </div>
                        <p class="text-muted fs-13">Cette action nécessite une vérification de votre mot de passe.</p>
                    </div>
                    <form id="globalPasswordForm">
                        <div class="mb-3">
                            <input type="password" class="form-control text-center" id="global_verify_password" placeholder="Votre mot de passe" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- End Wrapper -->


    <!-- jQuery -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Core JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>    

	<!-- Simplebar JS -->
	<script src="assets/plugins/simplebar/simplebar.min.js"></script>

    <!-- Datatable JS -->
    <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="assets/plugins/sweetalert2/sweetalert2.min.js"></script>

    <script>
    let _passwordCallback = null;

    function requestPassword(callback) {
        _passwordCallback = callback;
        $('#global_verify_password').val('');
        $('#passwordVerifyModal').modal('show');
        setTimeout(() => $('#global_verify_password').focus(), 500);
    }

    $(document).ready(function() {
        $('#globalPasswordForm').on('submit', function(e) {
            e.preventDefault();
            const pwd = $('#global_verify_password').val();
            
            $.ajax({
                url: 'api/auth/verify_password.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ password: pwd }),
                success: function(response) {
                    if (response.status === 'success') {
                        $('#passwordVerifyModal').modal('hide');
                        if (_passwordCallback) _passwordCallback(true);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Mot de passe incorrect',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire('Erreur', 'Erreur serveur lors de la vérification', 'error');
                }
            });
        });
    });

    // Global Toast Mixin
    if (typeof Toast === 'undefined') {
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }

    // Notification System
    function loadNotifications() {
        $.ajax({
            url: 'api/notifications/get_all',
            type: 'GET',
            data: { limit: 5 },
            dataType: 'json',
            success: function(response) {
                // console.log('Load Notifications Response:', response); // Uncomment if needed, but might be too noisy for polling
                if (response.status === 'success') {
                    const list = $('#notification-list');
                    list.empty();
                    
                    // Update badge
                    const badge = $('.ti-bell-check').next('.badge');
                    if (response.unread_count > 0) {
                        badge.text(response.unread_count).show();
                    } else {
                        badge.hide();
                    }

                    if (response.data.length === 0) {
                        list.html('<div class="p-3 text-center text-muted">Aucune notification</div>');
                        return;
                    }

                    response.data.forEach(notif => {
                        const bgClass = notif.is_read == 0 ? 'bg-light' : '';
                        const icon = notif.type === 'success' ? 'ti-check text-success' : 
                                     (notif.type === 'error' ? 'ti-alert-circle text-danger' : 'ti-info-circle text-info');
                        
                        list.append(`
                            <div class="dropdown-item notification-item py-3 text-wrap border-bottom ${bgClass}" id="notif-${notif.notification_id}">
                                <div class="d-flex">
                                    <div class="me-2 position-relative flex-shrink-0">
                                        <i class="ti ${icon} fs-24"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-medium text-dark">${notif.title}</p>
                                        <p class="mb-1 text-wrap">${notif.message}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fs-12"><i class="ti ti-clock me-1"></i>${moment(notif.created_at).fromNow()}</span>
                                            ${notif.is_read == 0 ? `
                                            <div class="notification-action d-flex align-items-center float-end gap-2">
                                                <a href="javascript:void(0);" onclick="markAsRead(${notif.notification_id})" class="notification-read rounded-circle bg-danger" data-bs-toggle="tooltip" title="Marquer comme lu"></a>
                                            </div>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Load Notifications Error:', error);
            }
        });
    }

    function markAsRead(id) {
        $.ajax({
            url: 'api/notifications/mark_read',
            type: 'POST',
            data: JSON.stringify({ id: id }),
            contentType: 'application/json',
            success: function(response) {
                console.log('Mark Read Response:', response);
                if (response.status === 'success') {
                    loadNotifications();
                }
            },
            error: function(xhr, status, error) {
                console.error('Mark Read Error:', error);
            }
        });
    }

    $(document).ready(function() {
        if ($('#notification-list').length) {
            loadNotifications();
            // Poll every 60 seconds
            setInterval(loadNotifications, 60000);
        }
    });
    </script>

	<!-- Daterangepicker JS -->
	<script src="assets/js/moment.min.js"></script>
	<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>

	<!-- Apexchart JS -->
	<script src="assets/plugins/apexchart/apexcharts.min.js"></script>
	<script src="assets/plugins/apexchart/chart-data.js"></script>
	
	<!-- Custom Json Js -->	
	<script src="assets/json/deals-project.js"></script>

    <!-- Main JS -->
    <script src="assets/js/script.js"></script>

</body>

</html>
