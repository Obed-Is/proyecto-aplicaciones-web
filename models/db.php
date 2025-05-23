<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

class Database {
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['HOST_DB'];
        $this->user = $_ENV['USER_DB'];
        $this->password = $_ENV['PASSWORD_DB'];
        $this->dbname = $_ENV['NAME_DB'];
    }

    public function getConnection() {
        if ($this->conn == null) {
            $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);
            if ($this->conn->connect_error) {
                die("Error de conexion: " . $this->conn->connect_error);
            }
        }
        return $this->conn;
    }
}
?>
