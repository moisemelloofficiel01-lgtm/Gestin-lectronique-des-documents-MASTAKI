<?php
function ensureArchiveTableSchema($conn) {
    // Check if table exists
    $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'archive_documents'");
    if (mysqli_num_rows($checkTable) == 0) {
        $sql = "CREATE TABLE archive_documents (
            archive_id INT AUTO_INCREMENT PRIMARY KEY,
            uuid_archive VARCHAR(36) NOT NULL UNIQUE,
            document_id INT NOT NULL,
            type_archivage VARCHAR(20) NOT NULL,
            categorie_archivage VARCHAR(50),
            sous_categorie VARCHAR(50),
            date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_fin_vie_utile DATE,
            date_fin_conservation DATE,
            duree_conservation INT NOT NULL,
            emplacement_physique VARCHAR(500),
            bucket_s3 VARCHAR(255),
            chemin_objet VARCHAR(1000),
            format_fichier VARCHAR(10) NOT NULL,
            version_format VARCHAR(20),
            hash_sha256 VARCHAR(64) NOT NULL,
            hash_sha512 VARCHAR(128),
            taille_fichier BIGINT NOT NULL,
            signature_electronique TEXT,
            certificat_signature TEXT,
            horodatage_certifie TEXT,
            titre VARCHAR(500),
            description TEXT,
            sujet TEXT,
            auteur VARCHAR(255),
            createur VARCHAR(255),
            editeur VARCHAR(255),
            contributeurs JSON,
            date_creation DATE,
            date_publication DATE,
            type_mime VARCHAR(100),
            format_mime VARCHAR(100),
            identifiants JSON,
            langue VARCHAR(10) DEFAULT 'fr',
            relation TEXT,
            couverture TEXT,
            droits TEXT,
            niveau_confidentialite VARCHAR(20) DEFAULT 'INTERNE',
            politiques_acces JSON,
            date_declassification DATE,
            statut_archivage VARCHAR(20) DEFAULT 'ACTIF',
            date_dernier_acces DATETIME,
            compteur_acces INT DEFAULT 0,
            dernier_acces_par INT,
            archive_par INT,
            autorise_par INT,
            date_autorisation DATE,
            motif_archivage TEXT,
            FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!mysqli_query($conn, $sql)) {
            error_log("Error creating table archive_documents: " . mysqli_error($conn));
            return false;
        }
    }
    return true;
}
?>