<?php
class Usuario {
    private $conn; // Conexión a la base de datos
    private $table_name = "usuarios"; // Nombre de la tabla

    public $id;
    public $nombre_usuario;
    public $password;
    public $email;
    public $rol;
    public $estado;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear un nuevo usuario
    public function crear($nombre_usuario, $password, $email, $rol) {
        try {
            // Verificar si el nombre de usuario o el email ya existen
            if ($this->existe($nombre_usuario, $email)) {
                return false; // El usuario o email ya existe
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nombre_usuario=:nombre_usuario, password=:password, email=:email, rol=:rol, estado='activo'";
            $stmt = $this->conn->prepare($query);

            // Limpiar y procesar los datos
            $this->nombre_usuario = htmlspecialchars(strip_tags($nombre_usuario));
            $this->password = password_hash($password, PASSWORD_DEFAULT); // Hash de la contraseña
            $this->email = htmlspecialchars(strip_tags($email));
            $this->rol = htmlspecialchars(strip_tags($rol));

            $stmt->bindParam(":nombre_usuario", $this->nombre_usuario);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":rol", $this->rol);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en crear: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si un usuario o email ya existe
    public function existe($nombre_usuario, $email) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " 
                      WHERE nombre_usuario = :nombre_usuario OR email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":nombre_usuario", $nombre_usuario);
            $stmt->bindParam(":email", $email);

            $stmt->execute();

            // Retorna true si se encuentra al menos un registro
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en existe: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar los datos de un usuario
    public function actualizar($id, $nombre_usuario, $email, $rol, $estado) {
        try {
            if (empty($nombre_usuario) || empty($email) || empty($rol)) {
                throw new Exception("Los campos requeridos no pueden estar vacíos.");
            }

            $sql = "UPDATE usuarios 
                    SET nombre_usuario = :nombre_usuario, email = :email, rol = :rol, estado = :estado 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':estado', $estado);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    // Eliminar un usuario
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM usuarios WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    // Leer todos los usuarios
    public function leer() {
        try {
            $query = "SELECT * FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("Error en leer: " . $e->getMessage());
            return null;
        }
    }
    // Método para iniciar sesión
    public function login($nombre_usuario, $password) {
        try {
            $query = "SELECT id, nombre_usuario, password, rol, estado FROM " . $this->table_name . " WHERE nombre_usuario = :nombre_usuario LIMIT 1";
            $stmt = $this->conn->prepare($query);
            
            // Vincula el nombre de usuario
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                // Recuperar los datos del usuario
                $this->id = $row['id'];
                $this->nombre_usuario = $row['nombre_usuario'];
                $this->rol = $row['rol'];
                $estado = $row['estado'];
                
                // Verificar si el usuario está activo
                if ($estado !== 'activo') {
                    throw new Exception('El usuario está inactivo. No se puede iniciar sesión.');
                }
    
                // Verificar la contraseña
                if (password_verify($password, $row['password'])) {
                    return true;
                } else {
                    throw new Exception('Contraseña incorrecta.');
                }
            } else {
                throw new Exception('Usuario no encontrado.');
            }
        } catch (Exception $e) {
            // Manejo de excepciones, podemos loguear el error y devolver false
            error_log("Error de login: " . $e->getMessage());
            return false;
        }
    }
    // Obtener un usuario por su ID
    public function obtenerPorId($id) {
        try {
            // Preparar la consulta
            $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = :id");
            // Vincular el parámetro
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            // Ejecutar la consulta
            $stmt->execute();
            // Obtener el resultado
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            return $usuario ? $usuario : null; // Retornar los datos del usuario o null si no existe
        } catch (Exception $e) {
            // Manejo de errores
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

}