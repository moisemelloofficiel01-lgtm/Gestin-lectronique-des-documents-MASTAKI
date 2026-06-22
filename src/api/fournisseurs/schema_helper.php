<?php
function ensureFournisseursTableSchema($conn) {
    // Check if table exists
    $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'fournisseurs'");
    if (!$checkTable) {
        error_log("Query failed: " . mysqli_error($conn));
        return false;
    }
    if (mysqli_num_rows($checkTable) == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE fournisseurs (
            fournisseur_id INT AUTO_INCREMENT PRIMARY KEY,
            nom_fournisseur VARCHAR(255) NOT NULL DEFAULT 'Fournisseur Inconnu',
            logo VARCHAR(255),
            adresse VARCHAR(255),
            complement_adresse VARCHAR(255),
            code_postal VARCHAR(20),
            ville VARCHAR(100),
            pays VARCHAR(100) DEFAULT 'RDC',
            contacts TEXT,
            telephone_principal VARCHAR(50),
            email_general VARCHAR(100),
            categorie_fournisseur VARCHAR(50),
            secteur_activite VARCHAR(100),
            commentaires_evaluation TEXT,
            statut VARCHAR(20) DEFAULT 'ACTIF',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $sql)) {
            error_log("Error creating table fournisseurs: " . mysqli_error($conn));
            return false;
        }
        return true;
    }

    // Define required columns and their types
    $requiredColumns = [
        'nom_fournisseur' => "VARCHAR(255) NOT NULL DEFAULT 'Fournisseur Inconnu'",
        'logo' => 'VARCHAR(255)',
        'adresse' => 'VARCHAR(255)',
        'complement_adresse' => 'VARCHAR(255)',
        'code_postal' => 'VARCHAR(20)',
        'ville' => 'VARCHAR(100)',
        'pays' => "VARCHAR(100) DEFAULT 'RDC'",
        'contacts' => 'TEXT',
        'telephone_principal' => 'VARCHAR(50)',
        'email_general' => 'VARCHAR(100)',
        'categorie_fournisseur' => 'VARCHAR(50)',
        'secteur_activite' => 'VARCHAR(100)',
        'commentaires_evaluation' => 'TEXT',
        'statut' => "VARCHAR(20) DEFAULT 'ACTIF'",
        'date_creation' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'date_modification' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];

    // Get existing columns
    $existingColumns = [];
    $result = mysqli_query($conn, "SHOW COLUMNS FROM fournisseurs");
    if (!$result) {
        error_log("Show columns query failed: " . mysqli_error($conn));
        return false;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $existingColumns[] = $row['Field'];
    }

    // Fix for legacy 'nom' column causing "Field 'nom' doesn't have a default value" error
    if (in_array('nom', $existingColumns)) {
        if (!in_array('nom_fournisseur', $existingColumns)) {
            // Rename nom to nom_fournisseur
            $alterSql = "ALTER TABLE fournisseurs CHANGE nom nom_fournisseur VARCHAR(255) NOT NULL DEFAULT 'Fournisseur Inconnu'";
            if (mysqli_query($conn, $alterSql)) {
                // Update the array to reflect change so we don't try to add it again
                $existingColumns[] = 'nom_fournisseur';
                // We keep 'nom' in the array implicitly or remove it, but it doesn't matter for the next loop as 'nom_fournisseur' is now present
            } else {
                 error_log("Error renaming column nom: " . mysqli_error($conn));
            }
        } else {
            // Both exist, drop nom since it's likely unused and causing errors
             $alterSql = "ALTER TABLE fournisseurs DROP COLUMN nom";
             if (!mysqli_query($conn, $alterSql)) {
                 error_log("Error dropping column nom: " . mysqli_error($conn));
             }
        }
    }

    // Add missing columns
    foreach ($requiredColumns as $col => $def) {
        if (!in_array($col, $existingColumns)) {
            $alterSql = "ALTER TABLE fournisseurs ADD COLUMN $col $def";
            if (!mysqli_query($conn, $alterSql)) {
                error_log("Error adding column $col: " . mysqli_error($conn));
            }
        }
    }
    
    return true;
}
?>
