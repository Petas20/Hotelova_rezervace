<?php
class Database {

    private $db_host = ""; 
    private $db_name = ""; 
    private $db_user = ""; 
    private $db_password = ""; 

    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->db_host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->db_user,
                $this->db_password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Chyba při připojení k databázi: " . $e->getMessage();
            $this->connection = null;
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeDb() {
        $this->connection = null;
    }
}
?>