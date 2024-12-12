<?php
class Opinion {
    private $conn;
    private $table_name = "opiniones"; // Nombre de la tabla en la base de datos

    public $id;
    public $hotel_id;
    public $usuario_id;
    public $comentario;
    public $fecha;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener todas las opiniones con datos relacionados (usuario y hotel)
    public function obtenerTodasOpiniones() {
        try {
            $query = "SELECT o.*, u.nombre_usuario, h.titulo as hotel_titulo
                      FROM " . $this->table_name . " o
                      LEFT JOIN usuarios u ON o.usuario_id = u.id
                      LEFT JOIN hoteles h ON o.hotel_id = h.id
                      ORDER BY o.fecha DESC";

            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->execute(); // Ejecuta la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todas las filas como un array asociativo
        } catch (PDOException $e) {
            echo "Error al obtener opiniones: " . $e->getMessage();
            return [];
        }
    }

    // Método para crear una nueva opinión
    public function crear($hotel_id, $usuario_id, $comentario) {
        try {
            $query = "INSERT INTO opiniones (hotel_id, usuario_id, comentario, fecha) 
                      VALUES (:hotel_id, :usuario_id, :comentario, NOW())";

            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->bindParam(':hotel_id', $hotel_id); // Vincula el ID del hotel
            $stmt->bindParam(':usuario_id', $usuario_id); // Vincula el ID del usuario
            $stmt->bindParam(':comentario', $comentario); // Vincula el comentario

            return $stmt->execute(); // Ejecuta la consulta
        } catch (PDOException $e) {
            echo "Error al crear una opinión: " . $e->getMessage();
            return false;
        }
    }

    // Método para leer opiniones de un hotel específico
    public function leerPorHotel($hotel_id) {
        try {
            $query = "SELECT o.*, u.nombre_usuario 
                      FROM " . $this->table_name . " o
                      LEFT JOIN usuarios u ON o.usuario_id = u.id
                      WHERE o.hotel_id = ? ORDER BY o.fecha DESC";

            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->bindParam(1, $hotel_id); // Vincula el ID del hotel
            $stmt->execute(); // Ejecuta la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna las filas como array asociativo
        } catch (PDOException $e) {
            echo "Error al obtener opiniones por hotel: " . $e->getMessage();
            return [];
        }
    }

    // Método para eliminar una opinión
    public function eliminar() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->bindParam(":id", $this->id); // Vincula el ID de la opinión

            return $stmt->execute(); // Ejecuta la consulta
        } catch (PDOException $e) {
            echo "Error al eliminar la opinión: " . $e->getMessage();
            return false;
        }
    }

    // Método para obtener opiniones de un usuario específico
    public function obtenerOpinionesPorUsuario($usuario_id) {
        try {
            $query = "SELECT o.*, h.titulo AS hotel_titulo, u.nombre_usuario
                      FROM opiniones o
                      JOIN hoteles h ON o.hotel_id = h.id
                      JOIN usuarios u ON o.usuario_id = u.id
                      WHERE o.usuario_id = :usuario_id";

            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->bindParam(':usuario_id', $usuario_id); // Vincula el ID del usuario
            $stmt->execute(); // Ejecuta la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todas las filas como un array asociativo
        } catch (PDOException $e) {
            echo "Error al obtener opiniones por usuario: " . $e->getMessage();
            return [];
        }
    }

    // Método para editar una opinión existente
    public function editar($id, $hotel_id, $comentario) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET hotel_id = :hotel_id, comentario = :comentario, fecha = NOW()
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query); // Prepara la consulta
            $stmt->bindParam(':id', $id); // Vincula el ID de la opinión
            $stmt->bindParam(':hotel_id', $hotel_id); // Vincula el ID del hotel
            $stmt->bindParam(':comentario', $comentario); // Vincula el comentario

            return $stmt->execute(); // Ejecuta la consulta
        } catch (PDOException $e) {
            echo "Error al editar la opinión: " . $e->getMessage();
            return false;
        }
    }
}
?>
