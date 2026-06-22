<?php
header("Content-Type: text/plain; charset=utf-8");
require_once __DIR__ . '/config/db.php';

echo "=== Vérification et création des tables ===\n\n";

$tables = ['utilisateurs', 'fournisseurs', 'documents', 'document_categories', 'dossiers', 'documents_personnels', 'documents_partages', 'partage_categories'];

foreach ($tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    echo mysqli_num_rows($check) > 0 ? "✓ $table : OK\n" : "✗ $table : MANQUANTE\n";
}

echo "\nTerminé.\n";
