<?php
class Database {
    private $host = "localhost";
    private $db_name = "my_database_gedo";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        $host = getenv('MYSQL_HOST') ?: $this->host;
        $dbName = getenv('MYSQL_DATABASE') ?: $this->db_name;
        $username = getenv('MYSQL_USER') ?: $this->username;
        $password = getenv('MYSQL_PASSWORD');
        if ($password === false) {
            $password = $this->password;
        }
        $safeDbName = str_replace('`', '``', $dbName);

        try {
            $bootstrapConn = new PDO("mysql:host=" . $host, $username, $password);
            $bootstrapConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $bootstrapConn->exec("CREATE DATABASE IF NOT EXISTS `" . $safeDbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $dbName, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
            
            // Auto-initialize tables
            require_once __DIR__ . '/init_db.php';
            // We use the mysqli connection for initialization since scripts are already written that way
            $m_conn = mysqli_connect($host, $username, $password, $dbName);
            if ($m_conn) {
                mysqli_set_charset($m_conn, "utf8mb4");
                initializeDatabaseSchema($m_conn);
                mysqli_close($m_conn);
            }

        } catch(PDOException $exception) {
            // Try to connect without db name if it doesn't exist yet (for setup)
            try {
                $this->conn = new PDO("mysql:host=" . $host, $username, $password);
                $this->conn->exec("set names utf8mb4");
            } catch(PDOException $e) {
                echo "Connection error: " . $exception->getMessage();
            }
        }

        return $this->conn;
    }
}
?>
