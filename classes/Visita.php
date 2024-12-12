<?php
class Visita {
    private $conn; // Conexión a la base de datos
    private $table_name = "visitas"; // Nombre de la tabla

    public $id; // ID de la visita
    public $fecha; // Fecha de la visita
    public $contador; // Contador de visitas

    public function __construct($db) {
        $this->conn = $db; // Inicializar la conexión
    }

    // Método para registrar una visita (incrementar o agregar una nueva)
    public function registrarVisita() {
        try {
            // Obtener la fecha actual
            $fecha_actual = date('Y-m-d');
            
            // Consulta para insertar o actualizar el contador si la fecha ya existe
            $query = "INSERT INTO " . $this->table_name . " (fecha, contador) VALUES (:fecha, 1) 
                      ON DUPLICATE KEY UPDATE contador = contador + 1";
            
            $stmt = $this->conn->prepare($query); // Preparar la consulta
            $stmt->bindValue(":fecha", $fecha_actual); // Asignar el valor de la fecha
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true; // Operación exitosa
            }
            return false; // Operación fallida
        } catch (Exception $e) {
            // Capturar errores y loguearlos
            error_log("Error en registrarVisita: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener las visitas totales por día
    public function obtenerVisitasPorDia() {
        try {
            // Obtener la fecha actual
            $fecha_actual = date('Y-m-d');
            
            // Consulta para sumar el total de visitas del día
            $query = "SELECT SUM(contador) as total FROM " . $this->table_name . " WHERE fecha = :fecha";
            $stmt = $this->conn->prepare($query); // Preparar la consulta
            $stmt->bindValue(":fecha", $fecha_actual); // Asignar el valor de la fecha
            
            $stmt->execute(); // Ejecutar la consulta
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener el resultado
            return $row['total'] ? $row['total'] : 0; // Retornar el total o 0 si no hay visitas
        } catch (Exception $e) {
            // Capturar errores y loguearlos
            error_log("Error en obtenerVisitasPorDia: " . $e->getMessage());
            return 0; // Retornar 0 en caso de error
        }
    }

    // Método para obtener las visitas totales por mes
    public function obtenerVisitasPorMes() {
        try {
            // Obtener el mes actual en formato YYYY-MM
            $mes_actual = date('Y-m');
            
            // Consulta para sumar las visitas del mes
            $query = "SELECT SUM(contador) as total FROM " . $this->table_name . " WHERE fecha LIKE :mes";
            $stmt = $this->conn->prepare($query); // Preparar la consulta
            $stmt->bindValue(":mes", $mes_actual . '%'); // Asignar el valor del mes
            
            $stmt->execute(); // Ejecutar la consulta
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener el resultado
            return $row['total'] ? $row['total'] : 0; // Retornar el total o 0 si no hay visitas
        } catch (Exception $e) {
            // Capturar errores y loguearlos
            error_log("Error en obtenerVisitasPorMes: " . $e->getMessage());
            return 0; // Retornar 0 en caso de error
        }
    }

    // Método para obtener las visitas totales por año
    public function obtenerVisitasPorAno() {
        try {
            // Obtener el año actual en formato YYYY
            $ano_actual = date('Y');
            
            // Consulta para sumar las visitas del año
            $query = "SELECT SUM(contador) as total FROM " . $this->table_name . " WHERE fecha LIKE :ano";
            $stmt = $this->conn->prepare($query); // Preparar la consulta
            $stmt->bindValue(":ano", $ano_actual . '%'); // Asignar el valor del año
            
            $stmt->execute(); // Ejecutar la consulta
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener el resultado
            return $row['total'] ? $row['total'] : 0; // Retornar el total o 0 si no hay visitas
        } catch (Exception $e) {
            // Capturar errores y loguearlos
            error_log("Error en obtenerVisitasPorAno: " . $e->getMessage());
            return 0; // Retornar 0 en caso de error
        }
    }
}
