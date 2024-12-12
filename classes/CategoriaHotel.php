<?php
class CategoriaHotel {
    private $conn;
    private $table_name = "categorias_hoteles";

    // Propiedades de la clase
    public $id;
    public $nombre_categoria;
    public $estado;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo registro
    public function crear() {
        try {
            // Consulta para insertar una nueva categoría
            $query = "INSERT INTO " . $this->table_name . " (nombre_categoria, estado) VALUES (:nombre_categoria, :estado)";
            $stmt = $this->conn->prepare($query);

            // Sanitizar los datos antes de usarlos
            $this->nombre_categoria = htmlspecialchars(strip_tags($this->nombre_categoria));
            $this->estado = htmlspecialchars(strip_tags($this->estado));

            // Vincular parámetros
            $stmt->bindParam(":nombre_categoria", $this->nombre_categoria);
            $stmt->bindParam(":estado", $this->estado);

            return $stmt->execute();
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al crear categoría: " . $e->getMessage();
            return false;
        }
    }

    // Método para actualizar un registro existente
    public function actualizar() {
        try {
            // Consulta para actualizar una categoría existente
            $query = "UPDATE " . $this->table_name . " SET nombre_categoria = :nombre_categoria, estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            // Sanitizar los datos antes de usarlos
            $this->nombre_categoria = htmlspecialchars(strip_tags($this->nombre_categoria));
            $this->estado = htmlspecialchars(strip_tags($this->estado));
            $this->id = htmlspecialchars(strip_tags($this->id));

            // Vincular parámetros
            $stmt->bindParam(":nombre_categoria", $this->nombre_categoria);
            $stmt->bindParam(":estado", $this->estado);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al actualizar categoría: " . $e->getMessage();
            return false;
        }
    }

    // Método para leer todos los registros
    public function leer() {
        try {
            // Consulta para obtener todas las categorías
            $query = "SELECT * FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al leer categorías: " . $e->getMessage();
            return [];
        }
    }

    // Método para leer solo los registros activos
    public function leerActivas() {
        try {
            // Consulta para obtener categorías activas
            $query = "SELECT * FROM " . $this->table_name . " WHERE estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al leer categorías activas: " . $e->getMessage();
            return [];
        }
    }

    // Método para obtener un registro por ID
    public function obtenerPorId($id) {
        try {
            // Consulta para obtener una categoría por su ID
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al obtener categoría por ID: " . $e->getMessage();
            return null;
        }
    }

    // Método para eliminar un registro
    public function eliminar() {
        try {
            // Consulta para eliminar una categoría
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            // Sanitizar el ID antes de usarlo
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            // Manejo de errores
            echo "Error al eliminar categoría: " . $e->getMessage();
            return false;
        }
    }
}
