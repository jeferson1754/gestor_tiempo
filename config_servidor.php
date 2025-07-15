<?php
// Configuración de la base de datos
define('DB_HOST', 'sql208.epizy.com');
define('DB_NAME', 'epiz_32740026_r_user');
define('DB_USER', 'epiz_32740026');
define('DB_PASS', 'eJWcVk2au5gqD');

date_default_timezone_set('America/Santiago');

// Clase para manejar la conexión a la base de datos
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>

