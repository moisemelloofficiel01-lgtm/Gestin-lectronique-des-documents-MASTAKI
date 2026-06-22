<?php 
require_once 'config/db.php';
include('pages/header.php'); 
?>

<div class="page-wrapper">
    <div class="content pb-0">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Tableau de Bord GED</h4>
                <p class="text-muted mb-0">Vue d'ensemble de la gestion électronique des documents</p>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <a href="documents.php" class="btn btn-primary shadow">
                    <i class="ti ti-plus me-1"></i> Nouveau Classement
                </a>
            </div>
        </div>

        <!-- ═══════ STAT CARDS ROW 1 ═══════ -->
        <div class="row">
            <!-- Total Documents -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-primary-soft text-primary">
                                <i class="ti ti-file-description fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_total_docs">0</h3>
                        <span class="text-muted fs-13">Total Documents</span>
                    </div>
                </div>
            </div>
            <!-- Documents Archivés -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-secondary-soft text-secondary">
                                <i class="ti ti-archive fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_archived_docs">0</h3>
                        <span class="text-muted fs-13">Archivés</span>
                    </div>
                </div>
            </div>
            <!-- En Attente -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-info-soft text-info">
                                <i class="ti ti-clock fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_pending_docs">0</h3>
                        <span class="text-muted fs-13">En Attente</span>
                    </div>
                </div>
            </div>
            <!-- Catégories -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-warning-soft text-warning">
                                <i class="ti ti-category fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_total_categories">0</h3>
                        <span class="text-muted fs-13">Catégories</span>
                    </div>
                </div>
            </div>
            <!-- Fournisseurs -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-success-soft text-success">
                                <i class="ti ti-users fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_total_suppliers">0</h3>
                        <span class="text-muted fs-13">Fournisseurs</span>
                    </div>
                </div>
            </div>
            <!-- Valeur Totale -->
            <div class="col-xl-2 col-md-4 col-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm overflow-hidden">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar avatar-md rounded bg-danger-soft text-danger">
                                <i class="ti ti-currency-dollar fs-20"></i>
                            </div>
                        </div>
                        <h3 class="mb-0 fw-bold" id="stat_total_value">0 $</h3>
                        <span class="text-muted fs-13">Valeur (USD)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ CHARTS ROW 1: Monthly Trend + Status Donut ═══════ -->
        <div class="row">
            <div class="col-xl-8 d-flex">
                <div class="card flex-fill border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-chart-area-line me-2 text-primary"></i>Évolution Mensuelle</h6>
                        <span class="badge bg-light text-dark">12 derniers mois</span>
                    </div>
                    <div class="card-body">
                        <div id="monthly_trend_chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 d-flex">
                <div class="card flex-fill border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-chart-donut-3 me-2 text-info"></i>Répartition par Statut</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="status_donut_chart" style="width:100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ CHARTS ROW 2: Categories Bar + Top Suppliers ═══════ -->
        <div class="row">
            <div class="col-xl-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-category me-2 text-warning"></i>Documents par Catégorie</h6>
                        <a href="categories.php" class="btn btn-xs btn-outline-primary">Voir</a>
                    </div>
                    <div class="card-body">
                        <div id="categories_chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 d-flex">
                <div class="card flex-fill border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-building me-2 text-success"></i>Top Fournisseurs</h6>
                        <a href="fournisseurs.php" class="btn btn-xs btn-outline-primary">Voir</a>
                    </div>
                    <div class="card-body">
                        <div id="suppliers_chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ RECENT DOCS + RECENT ARCHIVED ═══════ -->
        <div class="row">
            <!-- Recent Documents -->
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-file-text me-2 text-primary"></i>Derniers Classements</h6>
                        <a href="documents.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Document</th>
                                        <th>Fournisseur</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="recent_docs_table">
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Chargement...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Archived -->
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-archive me-2 text-secondary"></i>Archives Récentes</h6>
                        <a href="documents_archives.php" class="btn btn-sm btn-outline-secondary">Voir</a>
                    </div>
                    <div class="card-body p-0" id="recent_archived_list">
                        <div class="text-center py-4 text-muted">Chargement...</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="ti ti-bolt me-2 text-warning"></i>Actions Rapides</h6>
                        <div class="d-grid gap-2">
                            <a href="documents.php" class="btn btn-white shadow-sm text-start p-3 d-flex align-items-center">
                                <div class="avatar bg-primary-soft text-primary rounded me-3"><i class="ti ti-upload fs-20"></i></div>
                                <div><span class="d-block fw-bold">Uploader Document</span><small class="text-muted">Facture, Devis…</small></div>
                            </a>
                            <a href="fournisseurs.php" class="btn btn-white shadow-sm text-start p-3 d-flex align-items-center">
                                <div class="avatar bg-success-soft text-success rounded me-3"><i class="ti ti-user-plus fs-20"></i></div>
                                <div><span class="d-block fw-bold">Nouveau Fournisseur</span><small class="text-muted">Ajouter un partenaire</small></div>
                            </a>
                            <a href="categories.php" class="btn btn-white shadow-sm text-start p-3 d-flex align-items-center">
                                <div class="avatar bg-warning-soft text-warning rounded me-3"><i class="ti ti-category fs-20"></i></div>
                                <div><span class="d-block fw-bold">Gérer Catégories</span><small class="text-muted">Types de documents</small></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include('pages/footer.php'); ?>

<script>
$(document).ready(function() {
    loadDashboardStats();
});

function animateValue(el, end) {
    const $el = $(el);
    $({ val: 0 }).animate({ val: end }, {
        duration: 800,
        easing: 'swing',
        step: function(now) {
            $el.text(Math.floor(now));
        },
        complete: function() {
            $el.text(end);
        }
    });
}

function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard/stats',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') return;
            const stats  = response.stats;
            const charts = response.charts;

            // ── Update Stat Cards ──
            animateValue('#stat_total_docs', stats.total_documents);
            animateValue('#stat_archived_docs', stats.archived_documents);
            animateValue('#stat_pending_docs', stats.pending_validation);
            animateValue('#stat_total_categories', stats.total_categories);
            animateValue('#stat_total_suppliers', stats.total_suppliers);
            $('#stat_total_value').text(parseFloat(stats.total_value_usd).toLocaleString('fr-FR') + ' $');

            // ── 1. Monthly Trend Area Chart ──
            if (window.ApexCharts && document.querySelector("#monthly_trend_chart")) {
                new ApexCharts(document.querySelector("#monthly_trend_chart"), {
                    series: [
                        { name: 'Nouveaux Documents', data: charts.monthly_trend.new_docs },
                        { name: 'Archivés', data: charts.monthly_trend.archived }
                    ],
                    chart: { height: 320, type: 'area', fontFamily: 'inherit', toolbar: { show: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: ['#0d6efd', '#6c757d'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 95, 100] }},
                    stroke: { curve: 'smooth', width: 2 },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: charts.monthly_trend.months,
                        axisBorder: { show: false }, axisTicks: { show: false },
                        labels: { style: { fontSize: '11px' } }
                    },
                    yaxis: { labels: { style: { fontSize: '11px' } } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    legend: { position: 'top', horizontalAlign: 'right' },
                    tooltip: { shared: true }
                }).render();
            }

            // ── 2. Status Donut Chart ──
            if (window.ApexCharts && document.querySelector("#status_donut_chart")) {
                new ApexCharts(document.querySelector("#status_donut_chart"), {
                    series: charts.status_distribution.counts,
                    labels: charts.status_distribution.labels,
                    chart: { type: 'donut', height: 300, fontFamily: 'inherit',
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: charts.status_distribution.colors,
                    plotOptions: {
                        pie: { donut: { size: '65%', labels: { 
                            show: true, 
                            total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 600 }
                        }}}
                    },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', fontSize: '12px' },
                    stroke: { width: 0 }
                }).render();
            }

            // ── 3. Categories Bar Chart ──
            if (window.ApexCharts && document.querySelector("#categories_chart")) {
                var catColors = ['#0d6efd','#198754','#0dcaf0','#ffc107','#dc3545','#6f42c1','#fd7e14','#20c997','#adb5bd'];
                new ApexCharts(document.querySelector("#categories_chart"), {
                    series: [{ name: 'Documents', data: charts.categories.counts }],
                    chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: catColors,
                    plotOptions: {
                        bar: { borderRadius: 6, horizontal: false, columnWidth: '55%', distributed: true }
                    },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: charts.categories.labels,
                        axisBorder: { show: false }, axisTicks: { show: false },
                        labels: { style: { fontSize: '11px' }, rotate: -35, rotateAlways: charts.categories.labels.length > 5 }
                    },
                    yaxis: { labels: { style: { fontSize: '11px' } } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    legend: { show: false },
                    tooltip: { y: { formatter: (val) => val + ' doc(s)' }}
                }).render();
            }

            // ── 4. Top Suppliers Horizontal Bar ──
            if (window.ApexCharts && document.querySelector("#suppliers_chart")) {
                new ApexCharts(document.querySelector("#suppliers_chart"), {
                    series: [{ name: 'Documents', data: charts.top_suppliers.counts }],
                    chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: ['#198754'],
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '55%' }},
                    dataLabels: { enabled: true, offsetX: 5, style: { fontSize: '12px', colors: ['#333'] }},
                    xaxis: {
                        categories: charts.top_suppliers.labels,
                        axisBorder: { show: false }, axisTicks: { show: false }
                    },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => val + ' document(s)' }}
                }).render();
            }

            // ── 5. Recent Documents Table ──
            const tbody = $('#recent_docs_table');
            tbody.empty();
            if (response.recent_documents.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted">Aucun document</td></tr>');
            } else {
                response.recent_documents.forEach(doc => {
                    let badge = 'bg-primary';
                    if (doc.statut === 'VALIDE') badge = 'bg-success';
                    if (doc.statut === 'ARCHIVE' || doc.statut === 'ARCHIVAL') badge = 'bg-secondary';
                    if (doc.statut === 'EN_COURS') badge = 'bg-info';
                    if (doc.statut === 'PAYE') badge = 'bg-teal';
                    if (doc.statut === 'REJETE') badge = 'bg-danger';

                    tbody.append(`
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm rounded bg-light text-primary me-2"><i class="ti ti-file-text"></i></div>
                                    <div>
                                        <span class="fw-semibold text-truncate d-inline-block" style="max-width:180px">${doc.nom_fichier_original}</span><br>
                                        <small class="text-muted">${doc.date_facture || ''}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="fs-13">${doc.nom_fournisseur || '<em class="text-muted">DIVERS</em>'}</span></td>
                            <td><span class="fw-bold">${doc.montant_ttc ? parseFloat(doc.montant_ttc).toLocaleString('fr-FR') + ' ' + (doc.devise||'') : '-'}</span></td>
                            <td><span class="badge ${badge} badge-xs">${doc.statut}</span></td>
                            <td class="text-center"><a href="document.php?id=${doc.document_id}" class="btn btn-xs btn-outline-primary"><i class="ti ti-eye"></i></a></td>
                        </tr>
                    `);
                });
            }

            // ── 6. Recent Archived List ──
            const archiveContainer = $('#recent_archived_list');
            archiveContainer.empty();
            if (response.recent_archived.length === 0) {
                archiveContainer.html('<div class="text-center py-4 text-muted"><i class="ti ti-archive-off fs-28 d-block mb-2"></i>Aucun document archivé</div>');
            } else {
                response.recent_archived.forEach(doc => {
                    archiveContainer.append(`
                        <div class="d-flex align-items-center p-3 border-bottom hover-bg-light">
                            <div class="avatar avatar-sm rounded bg-secondary-soft text-secondary me-3">
                                <i class="ti ti-archive fs-16"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <span class="fw-semibold text-truncate d-block fs-13">${doc.nom_fichier_original}</span>
                                <small class="text-muted">${doc.nom_fournisseur || 'DIVERS'} · ${doc.date_archivage || ''}</small>
                            </div>
                            <a href="document.php?id=${doc.document_id}" class="btn btn-xs btn-outline-secondary ms-2"><i class="ti ti-eye"></i></a>
                        </div>
                    `);
                });
            }
        },
        error: function(err) {
            console.error('Dashboard stats error:', err);
        }
    });
}
</script>

<style>
.bg-primary-soft { background-color: rgba(13,110,253,.1) !important; }
.bg-success-soft { background-color: rgba(25,135,84,.1) !important; }
.bg-info-soft    { background-color: rgba(13,202,240,.1) !important; }
.bg-warning-soft { background-color: rgba(255,193,7,.1) !important; }
.bg-danger-soft  { background-color: rgba(220,53,69,.1) !important; }
.bg-secondary-soft { background-color: rgba(108,117,125,.1) !important; }
.bg-teal { background-color: #20c997 !important; }
.bg-white-10 { background-color: rgba(255,255,255,.15); }
.hover-bg-light:hover { background-color: #f8f9fa; transition: background .2s ease; }
.badge-xs { font-size: 10px; padding: 3px 8px; }
</style>