<?php
function ensureDocumentsTableSchema($conn) {
    // Check if table exists
    $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'documents'");
    if (mysqli_num_rows($checkTable) == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE documents (
            document_id INT AUTO_INCREMENT PRIMARY KEY,
            uuid_document VARCHAR(36) UNIQUE NOT NULL,
            type_document VARCHAR(50) NOT NULL,
            sous_type VARCHAR(50),
            nom_fichier_original VARCHAR(500) NOT NULL,
            extension_fichier VARCHAR(10),
            chemin_stockage VARCHAR(1000),
            taille_fichier BIGINT,
            checksum VARCHAR(64),
            numero_facture VARCHAR(100),
            numero_commande VARCHAR(100),
            numero_bon_livraison VARCHAR(100),
            date_facture DATE,
            date_echeance DATE,
            montant_ht DECIMAL(15,2),
            montant_tva DECIMAL(15,2),
            montant_ttc DECIMAL(15,2),
            devise VARCHAR(3) DEFAULT 'USD',
            fournisseur_id INT,
            service_demandeur VARCHAR(100),
            centre_cout VARCHAR(50),
            statut VARCHAR(50) DEFAULT 'NOUVEAU',
            date_reception TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_archivage TIMESTAMP NULL,
            duree_conservation INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(fournisseur_id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $sql)) {
            error_log("Error creating table documents: " . mysqli_error($conn));
            return false;
        }
        return true;
    }
    return true;
}
?>