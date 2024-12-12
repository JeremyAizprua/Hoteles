<?php

class Like {
    private $conn;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Verifica si el usuario ya dio "like" a un hotel específico
     * 
     * @param int $hotel_id ID del hotel
     * @param int $user_id ID del usuario
     * @return bool Retorna true si el usuario ya dio "like", false en caso contrario
     */
    public function hasUserLiked($hotel_id, $user_id) {
        try {
            $query = "SELECT COUNT(*) FROM likes WHERE id_hotel = :hotel_id AND usuario = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hotel_id', $hotel_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchColumn() > 0; // Retorna true si hay al menos un registro
        } catch (PDOException $e) {
            // Manejo de errores: registra el error en un log o muestra un mensaje
            error_log("Error en hasUserLiked: " . $e->getMessage());
            return false; // En caso de error, retorna false
        }
    }

    /**
     * Agrega un "like" para un hotel
     * 
     * @param int $hotel_id ID del hotel
     * @param int $user_id ID del usuario
     * @return bool Retorna true si la operación fue exitosa, false en caso contrario
     */
    public function addLike($hotel_id, $user_id) {
        try {
            $query = "INSERT INTO likes (id_hotel, usuario) VALUES (:hotel_id, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hotel_id', $hotel_id);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute(); // Retorna true si se ejecuta correctamente
        } catch (PDOException $e) {
            // Manejo de errores: registra el error en un log o muestra un mensaje
            error_log("Error en addLike: " . $e->getMessage());
            return false; // En caso de error, retorna false
        }
    }

    /**
     * Obtiene el número total de "likes" para un hotel
     * 
     * @param int $hotel_id ID del hotel
     * @return int Número total de "likes"
     */
    public function getTotalLikes($hotel_id) {
        try {
            $query = "SELECT COUNT(*) FROM likes WHERE id_hotel = :hotel_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hotel_id', $hotel_id);
            $stmt->execute();
            return (int) $stmt->fetchColumn(); // Retorna el total de "likes" como entero
        } catch (PDOException $e) {
            // Manejo de errores: registra el error en un log o muestra un mensaje
            error_log("Error en getTotalLikes: " . $e->getMessage());
            return 0; // En caso de error, retorna 0
        }
    }
}
?>
