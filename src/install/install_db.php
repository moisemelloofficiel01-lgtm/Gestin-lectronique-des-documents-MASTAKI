<?php
header("Content-Type: text/plain; charset=utf-8");

$host = getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD');
if ($password === false) {
    $password = '';
}
$dbname = getenv('MYSQL_DATABASE') ?: 'my_database_gedo';

$conn = mysqli_connect($host, $user, $password);
if (!$conn) {
    http_response_code(500);
    exit("Connexion MySQL échouée: " . mysqli_connect_error() . "\n");
}

mysqli_set_charset($conn, "utf8mb4");

$safeDbName = str_replace('`', '``', $dbname);
if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $safeDbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    http_response_code(500);
    exit("Création de la base échouée: " . mysqli_error($conn) . "\n");
}

if (!mysqli_select_db($conn, $dbname)) {
    http_response_code(500);
    exit("Sélection de la base échouée: " . mysqli_error($conn) . "\n");
}

require_once __DIR__ . '/../config/init_db.php';

echo "---------------------------------------------------\n";
echo "REINITIALISATION DE LA BASE\n";
echo "---------------------------------------------------\n";
echo "Base: $dbname\n\n";

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

$result = mysqli_query($conn, "SHOW TABLES");
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $table = $row[0];
        if (mysqli_query($conn, "DROP TABLE IF EXISTS `$table`")) {
            echo "Table supprimée: $table\n";
        } else {
            echo "Erreur suppression $table: " . mysqli_error($conn) . "\n";
        }
    }
}

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "\n---------------------------------------------------\n";
echo "CREATION DES TABLES ET INSERTIONS PAR DEFAUT\n";
echo "---------------------------------------------------\n";

initializeDatabaseSchema($conn);

$expectedTables = [
    'utilisateurs',
    'fournisseurs',
    'documents',
    'document_categories',
    'dossiers',
    'documents_personnels',
    'documents_partages',
    'partage_categories',
];

foreach ($expectedTables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    echo mysqli_num_rows($check) > 0
        ? "Table créée: $table\n"
        : "Table manquante: $table\n";
}

$usersCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM utilisateurs");
$usersRow = $usersCount ? mysqli_fetch_assoc($usersCount) : ['total' => 0];

$categoriesCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM document_categories");
$categoriesRow = $categoriesCount ? mysqli_fetch_assoc($categoriesCount) : ['total' => 0];

echo "\n---------------------------------------------------\n";
echo "DONNEES INSEREES\n";
echo "---------------------------------------------------\n";
echo "Utilisateurs: " . $usersRow['total'] . "\n";
echo "Categories: " . $categoriesRow['total'] . "\n";
echo "Admin par défaut: admin@ged.com\n";
echo "Mot de passe par défaut: Admin123!\n";

mysqli_close($conn);
?>
