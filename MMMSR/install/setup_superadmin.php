<?php
// Include database configuration
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Create/Recreate Table `utilisateurs`
    // Note: We use INT AUTO_INCREMENT for MySQL compatibility instead of SERIAL
    
    // Disable foreign key checks
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Debug: Check if it's disabled
    $res = $db->query("SELECT @@FOREIGN_KEY_CHECKS")->fetchColumn();
    echo "Foreign Key Checks status: " . $res . "\n";

    // Dynamically drop all Foreign Keys referencing `utilisateurs`
    $db_name = "my_database_ged"; // Assuming this is the DB name from config, or use SELECT DATABASE()
    
    $fks_stmt = $db->prepare("
        SELECT TABLE_NAME, CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'utilisateurs' 
        AND TABLE_SCHEMA = DATABASE()
    ");
    $fks_stmt->execute();
    $fks = $fks_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fks as $fk) {
        $table = $fk['TABLE_NAME'];
        $constraint = $fk['CONSTRAINT_NAME'];
        echo "Dropping FK $constraint from table $table...\n";
        try {
            $db->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$constraint`");
        } catch (PDOException $e) {
            echo "Failed to drop FK $constraint: " . $e->getMessage() . "\n";
        }
    }

    // DROP TABLE IF EXISTS to ensure schema matches the new definition
    try {
        $db->exec("DROP TABLE IF EXISTS utilisateurs");
    } catch (PDOException $e) {
        echo "Drop failed: " . $e->getMessage() . "\n";
        // If drop failed, we can't proceed with CREATE usually, but let's see.
    }
    
    $sql = "
    CREATE TABLE utilisateurs (
        utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Informations personnelles
        matricule VARCHAR(50) UNIQUE NOT NULL,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        photo VARCHAR(255) NULL,
        
        fonction VARCHAR(100),
    
        -- Informations de contact
        telephone VARCHAR(20),
        adresse TEXT,
        code_postal VARCHAR(10),
        ville VARCHAR(100),
        
        -- Rôles et permissions (JSON pour plusieurs rôles)
        roles JSON NOT NULL, 
        
        -- Statut
        actif BOOLEAN DEFAULT TRUE,
        
        -- Audit
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        cree_par INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $db->exec($sql);
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Table 'utilisateurs' created or checked successfully.\n";

    // 2. Create Superadmin User
    $email = "admin@ged.com";
    $password = "Admin123!"; // Default password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $matricule = "SA-001";
    $nom = "Admin";
    $prenom = "Super";
    $roles = json_encode(["SUPERADMIN", "ADMIN"]);
    
    // Check if user exists
    $check_stmt = $db->prepare("SELECT utilisateur_id FROM utilisateurs WHERE email = :email");
    $check_stmt->bindParam(":email", $email);
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 0) {
        $insert_sql = "INSERT INTO utilisateurs (matricule, nom, prenom, email, mot_de_passe, roles, actif) VALUES (:matricule, :nom, :prenom, :email, :mot_de_passe, :roles, 1)";
        $stmt = $db->prepare($insert_sql);
        
        $stmt->bindParam(":matricule", $matricule);
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":prenom", $prenom);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":mot_de_passe", $hashed_password);
        $stmt->bindParam(":roles", $roles);
        
        if ($stmt->execute()) {
            echo "Superadmin user created successfully.\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
        } else {
            echo "Error creating superadmin user.\n";
            print_r($stmt->errorInfo());
        }
    } else {
        echo "Superadmin user already exists.\n";
        
        // Optional: Update password if needed
        // $update_sql = "UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE email = :email";
        // $stmt = $db->prepare($update_sql);
        // $stmt->bindParam(":mot_de_passe", $hashed_password);
        // $stmt->bindParam(":email", $email);
        // $stmt->execute();
        // echo "Superadmin password reset to default.\n";
    }

} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
?>
