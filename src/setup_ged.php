<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/fournisseurs/schema_helper.php';
require_once __DIR__ . '/api/documents/schema_helper.php';

echo "Setting up GED database...\n";

// DROP TABLES TO RECREATE CORRECTLY
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "DROP TABLE IF EXISTS documents");
mysqli_query($conn, "DROP TABLE IF EXISTS fournisseurs");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

if (ensureFournisseursTableSchema($conn)) {
    echo "Fournisseurs table created.\n";
} else {
    echo "Error creating fournisseurs table.\n";
}

if (ensureDocumentsTableSchema($conn)) {
    echo "Documents table created.\n";
} else {
    echo "Error creating documents table.\n";
}

// Add some sample data for demonstration if needed
echo "Adding sample suppliers...\n";
$sql = "INSERT INTO fournisseurs (nom_fournisseur, email_general, ville, pays, categorie_fournisseur, statut) VALUES 
        ('Ets Global Services', 'contact@globalservices.cd', 'Goma', 'RDC', 'SERVICES', 'ACTIF'),
        ('Sodeim Ltd', 'sales@sodeim.com', 'Kinshasa', 'RDC', 'MATIERES_PREMIERES', 'ACTIF'),
        ('Tshopo Tech', 'info@tshopotech.net', 'Kisangani', 'RDC', 'SOUS_TRAITANCE', 'ACTIF')";
mysqli_query($conn, $sql);

mysqli_close($conn);
echo "Setup complete.\n";
?>
