<?php
require_once __DIR__ . '/../config/session_helper.php';
checkSession();
?>
<!DOCTYPE html>
<html lang="en">

<head>

	<!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>GED DSTN - Gestion Électronique des Documents</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Streamline your business with our advanced CRM template.">
	<meta name="keywords" content="Advanced CRM template, customer relationship management">
	<meta name="author" content="Dreams Technologies">
	<meta name="robots" content="index, follow">
	<base href="<?php echo getBasePath(); ?>">
	
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/img/favicon.png">

    <!-- Apple Icon -->
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">

    <!-- Theme Config Js -->
    <script src="assets/js/theme-script.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="assets/plugins/tabler-icons/tabler-icons.min.css">

    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="assets/plugins/simplebar/simplebar.min.css">

    <!-- Datatable CSS -->
    <link rel="stylesheet" href="assets/plugins/datatables/css/dataTables.bootstrap5.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">

	<!-- Daterangepicker CSS -->
	<link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css" id="app-style">

	    <script src="assets/js/jquery-3.7.1.min.js"></script>

    <style>
    .multi-select-dropdown { position: relative; }
    .multi-select-dropdown .ms-options { max-height: 250px; overflow-y: auto; }
    .multi-select-dropdown .ms-options .form-check { padding: 2px 8px; border-radius: 4px; }
    .multi-select-dropdown .ms-options .form-check:hover { background-color: #f1f5f9; }
    .multi-select-dropdown .dropdown-toggle::after { display: none; }
    .multi-select-dropdown .ms-options::-webkit-scrollbar { width: 6px; }
    .multi-select-dropdown .ms-options::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    </style>

    <script>
    window.initMultiSelect = function(id, apiUrl) {
        const $el = $('#' + id);
        if (!$el.length) return;
        const $values = $el.find('.ms-values');
        const $count = $el.find('.ms-count');
        const $label = $el.find('.ms-label');
        $.get(apiUrl, function(resp) {
            if (resp.status !== 'success') return;
            let html = '';
            resp.data.forEach(function(u) {
                html += '<div class="form-check ms-option"><input class="form-check-input ms-check" type="checkbox" value="'+u.utilisateur_id+'" id="'+id+'_'+u.utilisateur_id+'" data-name="'+u.nom+' '+u.prenom+'"><label class="form-check-label" for="'+id+'_'+u.utilisateur_id+'">'+u.nom+' '+u.prenom+' ('+u.email+')</label></div>';
            });
            $el.find('.ms-options').html(html);
            $el.on('change', '.ms-check', function() {
                const vals = $el.find('.ms-check:checked').map(function(){ return this.value; }).get();
                const names = $el.find('.ms-check:checked').map(function(){ return $(this).data('name'); }).get();
                $values.val(JSON.stringify(vals));
                $count.text(vals.length).toggle(vals.length > 0);
                if (names.length > 0) {
                    $label.text(names.join(', ')).removeClass('text-muted');
                } else {
                    $label.text('Sélectionner des utilisateurs').addClass('text-muted');
                }
            });
        });
    };

    function getMultiSelectValues(id) {
        const raw = $('#' + id).find('.ms-values').val();
        try { return JSON.parse(raw || '[]'); } catch(e) { return []; }
    }
    </script>

</head>

<body><a href="https://crms.dreamstechnologies.com/cdn-cgi/content?id=3AMag7KDV0fFjUI1mfsN8wXMtOHWOrxhkAFKIlt0gpw-1769007295-1.1.1.1-liZ_FKOX7SSk7glg5JVSndU9zqtd0uVfOyvVXgvqOSI" aria-hidden="true" rel="nofollow noopener" style="display: none !important; visibility: hidden !important"></a>

    <!-- Begin Wrapper -->
    <div class="main-wrapper">

        <!-- Topbar Start -->
        <header class="navbar-header">
            <div class="page-container topbar-menu">
                <div class="d-flex align-items-center gap-2">

                    <!-- Logo -->
                    <a href="index.php" class="logo">

                        <!-- Logo Normal -->
                        <span class="logo-light">
                            <span class="logo-lg"><img src="assets/img/logo.svg" alt="logo"></span>
                            <span class="logo-sm"><img src="assets/img/logo-small.svg" alt="small logo"></span>
                        </span>

                        <!-- Logo Dark -->
                        <span class="logo-dark">
                            <span class="logo-lg"><img src="assets/img/logo-white.svg" alt="dark logo"></span>
                        </span>
                    </a>

                    <!-- Sidebar Mobile Button -->
                    <a id="mobile_btn" class="mobile-btn" href="#sidebar">
                        <i class="ti ti-menu-deep fs-24"></i>
                    </a>

                    <button class="sidenav-toggle-btn btn border-0 p-0" id="toggle_btn2"> 
                        <i class="ti ti-arrow-bar-to-right"></i>
                    </button> 
					
                    <!-- Search -->
                    <div class="me-auto d-flex align-items-center header-search d-lg-flex d-none">
                        <form class="w-100" method="GET" action="documents.php" id="globalSearchForm">
                            <div class="input-icon position-relative me-2">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher un document..." id="globalSearchInput">
                                <span class="input-icon-addon d-inline-flex p-0 header-search-icon"><i class="ti ti-search"></i></span>
                            </div>
                        </form>
                    </div>
					
                </div>

                <div class="d-flex align-items-center">
				
                    <!-- Search for Mobile -->
                    <div class="header-item d-flex d-lg-none me-2">
                        <button class="topbar-link btn" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                            <i class="ti ti-search fs-16"></i>
                        </button>
                    </div>


                    <!-- Minimize -->
                    <div class="header-item">
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="btn topbar-link btnFullscreen"><i class="ti ti-maximize"></i></a>
                        </div> 
                    </div> 
                    <!-- Minimize --> 

                    <!-- Light/Dark Mode Button -->
                    <div class="header-item d-none d-sm-flex me-2">
                        <button class="topbar-link btn topbar-link" id="light-dark-mode" type="button">
                            <i class="ti ti-moon fs-16"></i>
                        </button>
                    </div>

					<div class="header-line"></div>
                    
					<!-- Notification Dropdown -->
                    <div class="header-item">
						<div class="dropdown me-2">
						
							<button class="topbar-link btn topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,24" type="button" aria-haspopup="false" aria-expanded="false">
								<i class="ti ti-bell-check fs-16 animate-ring"></i>
								<span class="badge rounded-pill" style="display:none">0</span>
							</button>
							
							<div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-height: 300px;">
							
								<div class="p-2 border-bottom">
									<div class="row align-items-center">
										<div class="col">
											<h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
										</div>
									</div>
								</div>
								
								<!-- Notification Body -->
								<div class="notification-body position-relative z-2 rounded-0" data-simplebar="" id="notification-list">
									<!-- Notifications loaded via AJAX -->
								</div>
								
								<!-- View All-->
								<div class="p-2 rounded-bottom border-top text-center">
									<a href="#" class="text-center text-decoration-underline fs-14 mb-0">
										Voir toutes les notifications
									</a>
								</div>
								
							</div>
						</div>
					</div> 
					
					<!-- User Dropdown -->
					<div class="dropdown profile-dropdown d-flex align-items-center justify-content-center">
                        <a href="javascript:void(0);" class="topbar-link dropdown-toggle drop-arrow-none position-relative" data-bs-toggle="dropdown" data-bs-offset="0,22" aria-haspopup="false" aria-expanded="false">
                            <div class="avatar avatar-sm bg-primary-soft text-primary rounded-circle">
                                <span class="fw-bold"><?php echo strtoupper(substr(getCurrentUserName(), 0, 1)); ?></span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-2">
                        
                            <div class="d-flex align-items-center bg-light rounded-3 p-2 mb-2">
                                <div class="avatar avatar-md bg-primary text-white rounded-circle">
                                    <span class="fw-bold fs-20"><?php echo strtoupper(substr(getCurrentUserName(), 0, 1)); ?></span>
                                </div>
                                <div class="ms-2">
                                    <p class="fw-medium text-dark mb-0"><?php echo getCurrentUserName(); ?></p>
                                    <span class="d-block fs-13"><?php echo getCurrentUserRole(); ?></span>
                                </div>
                            </div>

                            <!-- Item-->
                            <a href="profile.php" class="dropdown-item">
                                <i class="ti ti-user-circle me-1 align-middle"></i>
                                <span class="align-middle">Mon Profil</span>
                            </a>
          
                            
                            <!-- Item-->
                            <div class="pt-2 mt-2 border-top">
                                <a href="api/auth/logout.php" class="dropdown-item text-danger">
                                    <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                    <span class="align-middle">Déconnexion</span>
                                </a>
                            </div>
                        </div>
                    </div>
						
                </div>
            </div>
        </header>
        <!-- Topbar End -->

        <!-- Search Modal -->
        <div class="modal fade" id="searchModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-transparent">
                    <div class="card shadow-none mb-0">
                        <form action="documents.php" method="GET" id="mobileSearchForm">
                            <div class="px-3 py-2 d-flex flex-row align-items-center" id="search-top">
                                <i class="ti ti-search fs-22"></i>
                                <input type="search" name="search" class="form-control border-0" placeholder="Rechercher un document...">
                                <button type="submit" class="btn p-0" data-bs-dismiss="modal"><i class="ti ti-search fs-22"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidenav Menu Start -->
        <div class="sidebar" id="sidebar">
            
            <!-- Start Logo -->
            <div class="sidebar-logo">
                <div>
                    <!-- Logo Normal -->
                    <a href="index.html" class="logo logo-normal">
                        <img src="assets/img/logo.svg" alt="Logo">
                    </a>

                    <!-- Logo Small -->
                    <a href="index.html" class="logo-small">
                        <img src="assets/img/logo-small.svg" alt="Logo">
                    </a>

                    <!-- Logo Dark -->
                    <a href="index.html" class="dark-logo">
                        <img src="assets/img/logo-white.svg" alt="Logo">
                    </a>
                </div>
                <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn"> 
                    <i class="ti ti-arrow-bar-to-left"></i>
                </button>

                <!-- Sidebar Menu Close -->
                <button class="sidebar-close">
                    <i class="ti ti-x align-middle"></i>
                </button>				               
            </div>
            <!-- End Logo -->

            <!-- Sidenav Menu -->
            <div class="sidebar-inner" data-simplebar="">                
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li class="menu-title"><span>Menu Principal</span></li>
						<li>
							<ul>
                                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
								<li>
									<a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
										<i class="ti ti-dashboard"></i><span>Tableau de Bord</span>
									</a>
								</li>
                                <li>
                                    <a href="fournisseurs.php" class="<?php echo $current_page == 'fournisseurs.php' ? 'active' : ''; ?>">
                                        <i class="ti ti-users"></i><span>Fournisseurs</span>
                                    </a>
                                </li>
                                <li class="submenu">
									<a href="javascript:void(0);" class="<?php echo in_array($current_page, ['documents.php', 'classement.php', 'detail_classement.php']) ? 'active subdrop' : ''; ?>">
										<i class="ti ti-file"></i><span>Gestion Documents</span><span class="menu-arrow"></span>
									</a>
									<ul>
										<li><a href="documents.php" class="<?php echo $current_page == 'documents.php' ? 'active' : ''; ?>">Tous les Documents</a></li>
										<li><a href="classement.php" class="<?php echo $current_page == 'classement.php' ? 'active' : ''; ?>">Par Catégorie</a></li>
                                        <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">Config. Catégories</a></li>
									</ul>
								</li>
                                <li>
                                    <a href="documents_archives.php" class="<?php echo $current_page == 'documents_archives.php' ? 'active' : ''; ?>">
                                        <i class="ti ti-archive"></i><span>Archives</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="drive.php" class="<?php echo $current_page == 'drive.php' ? 'active' : ''; ?>">
                                        <i class="ti ti-folder"></i><span>Drive</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="share.php" class="<?php echo $current_page == 'share.php' ? 'active' : ''; ?>">
                                        <i class="ti ti-share"></i><span>Documents Partagés</span>
                                    </a>
                                </li>
							</ul>
						</li>
                        <li class="menu-title"><span>Administration</span></li>
                        <li>
                            <ul>
                                <li><a href="admin/users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>"><i class="ti ti-users-cog"></i><span>Gestion Utilisateurs</span></a></li>
                                <li><a href="api/auth/logout.php" class="text-danger"><i class="ti ti-logout"></i><span>Déconnexion</span></a></li>
                            </ul>
                        </li>
					</ul>
                </div>
            </div>
        </div>
        <!-- Sidenav Menu End -->
