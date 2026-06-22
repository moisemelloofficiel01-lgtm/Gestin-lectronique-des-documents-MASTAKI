<?php
/**
 * Database Initialization Script
 * Ensures all necessary tables exist and are properly configured.
 */

function initializeDatabaseSchema($conn) {
    // 1. UTILISATEURS Table
    $checkUtilisateurs = mysqli_query($conn, "SHOW TABLES LIKE 'utilisateurs'");
    if (mysqli_num_rows($checkUtilisateurs) == 0) {
        $sql = "CREATE TABLE utilisateurs (
            utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
            matricule VARCHAR(50) UNIQUE NOT NULL,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            mot_de_passe VARCHAR(255) NOT NULL,
            photo VARCHAR(255) NULL,
            fonction VARCHAR(100),
            telephone VARCHAR(20),
            adresse TEXT,
            code_postal VARCHAR(10),
            ville VARCHAR(100),
            roles JSON NOT NULL, 
            actif BOOLEAN DEFAULT TRUE,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            cree_par INT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $sql)) {
            // Create Default Superadmin
            $admin_pass = password_hash('Admin123!', PASSWORD_DEFAULT);
            $admin_roles = json_encode(['SUPERADMIN']);
            $insertSql = "INSERT INTO utilisateurs (matricule, nom, prenom, email, mot_de_passe, roles) 
                          VALUES ('SA-001', 'Admin', 'Super', 'admin@ged.com', '$admin_pass', '$admin_roles')";
            mysqli_query($conn, $insertSql);
        }
    }

    // 2. FOURNISSEURS Table
    $checkFournisseurs = mysqli_query($conn, "SHOW TABLES LIKE 'fournisseurs'");
    if (mysqli_num_rows($checkFournisseurs) == 0) {
        $sql = "CREATE TABLE fournisseurs (
            fournisseur_id INT AUTO_INCREMENT PRIMARY KEY,
            nom_fournisseur VARCHAR(255) NOT NULL,
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
        mysqli_query($conn, $sql);
    }

    // 3. DOCUMENTS Table
    $checkDocuments = mysqli_query($conn, "SHOW TABLES LIKE 'documents'");
    if (mysqli_num_rows($checkDocuments) == 0) {
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
        mysqli_query($conn, $sql);
    }

    // 4. DOCUMENT_CATEGORIES Table
    $checkCat = mysqli_query($conn, "SHOW TABLES LIKE 'document_categories'");
    if (mysqli_num_rows($checkCat) == 0) {
        $sql = "CREATE TABLE document_categories (
            category_id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50) DEFAULT 'ti-file',
            color VARCHAR(20) DEFAULT 'primary',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $sql)) {
            // Seed defaults
            $seedSql = "INSERT INTO document_categories (code, name, icon, color) VALUES 
                ('FACTURE', 'Factures', 'ti-file-invoice', 'primary'),
                ('BON_COMMANDE', 'Bons de Commande', 'ti-shopping-cart', 'success'),
                ('BON_LIVRAISON', 'Bons de Livraison', 'ti-truck-delivery', 'info'),
                ('DEVIS', 'Devis', 'ti-file-percent', 'warning'),
                ('CONTRAT', 'Contrats', 'ti-file-check', 'danger'),
                ('AUTRE', 'Autres', 'ti-file-text', 'secondary')";
            mysqli_query($conn, $seedSql);
        }
    }

    // 5. DOSSIERS Table (Drive-like folders)
    $checkDossiers = mysqli_query($conn, "SHOW TABLES LIKE 'dossiers'");
    if (mysqli_num_rows($checkDossiers) == 0) {
        $sql = "CREATE TABLE dossiers (
            dossier_id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL,
            parent_id INT DEFAULT NULL,
            created_by INT NOT NULL,
            type VARCHAR(20) DEFAULT 'personnel',
            partage BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES dossiers(dossier_id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $sql);
    }

    // 6. DOCUMENTS_PERSONNELS Table (Personal files in Drive)
    $checkDocsPerso = mysqli_query($conn, "SHOW TABLES LIKE 'documents_personnels'");
    if (mysqli_num_rows($checkDocsPerso) == 0) {
        $sql = "CREATE TABLE documents_personnels (
            document_id INT AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(36) UNIQUE NOT NULL,
            dossier_id INT DEFAULT NULL,
            nom_fichier VARCHAR(500) NOT NULL,
            fichier_original VARCHAR(500) NOT NULL,
            extension VARCHAR(10),
            taille BIGINT,
            chemin_stockage VARCHAR(1000),
            type_document VARCHAR(50) DEFAULT 'AUTRE',
            created_by INT NOT NULL,
            prive BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (dossier_id) REFERENCES dossiers(dossier_id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $sql);
    }

    // 7. DOCUMENTS_PARTAGES Table (Shared documents between users)
    $checkPartages = mysqli_query($conn, "SHOW TABLES LIKE 'documents_partages'");
    if (mysqli_num_rows($checkPartages) == 0) {
        $sql = "CREATE TABLE documents_partages (
            partage_id INT AUTO_INCREMENT PRIMARY KEY,
            document_id INT NOT NULL,
            shared_by INT NOT NULL,
            shared_with INT NOT NULL,
            permission VARCHAR(20) DEFAULT 'view',
            type_partage VARCHAR(20) DEFAULT 'document',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (shared_by) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
            FOREIGN KEY (shared_with) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
            FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
            UNIQUE KEY unique_share (document_id, shared_with)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $sql);
    }

    // 8. PARTAGE_CATEGORIES Table (Share categories with users)
    $checkPartCats = mysqli_query($conn, "SHOW TABLES LIKE 'partage_categories'");
    if (mysqli_num_rows($checkPartCats) == 0) {
        $sql = "CREATE TABLE partage_categories (
            partage_id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            shared_by INT NOT NULL,
            shared_with INT NOT NULL,
            permission VARCHAR(20) DEFAULT 'view',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES document_categories(category_id) ON DELETE CASCADE,
            FOREIGN KEY (shared_by) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
            FOREIGN KEY (shared_with) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
            UNIQUE KEY unique_cat_share (category_id, shared_with)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $sql);
    }
}
?>
