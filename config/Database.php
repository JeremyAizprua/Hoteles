<?php
// config/Database.php

class Database {
    private $host = "localhost";
    private $db_name = "hoteles_panama";
    private $username = "admin";
    private $password = "root2514";
    private $port = "3307"; // Se agrega el puerto
    public $conn;

    // Conexión para login
    public function getLoginConnection() {
        $this->conn = null;
        try {
            // Añadimos el puerto a la cadena de conexión
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }

    // Conexión para usuarios logueados (con más permisos)
    public function getAdminConnection() {
        return $this->getLoginConnection(); // Usamos la misma conexión por ahora
    }

    // Conexión para la página pública (solo permisos de SELECT)
    public function getPublicConnection() {
        $this->conn = null;
        try {
            // Añadimos el puerto a la cadena de conexión
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
            $this->conn->exec("SET SESSION TRANSACTION READ ONLY");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
